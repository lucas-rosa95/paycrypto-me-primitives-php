#!/usr/bin/env python3
"""Offline integration tests: real Git repositories, no production credentials."""
import contextlib
import importlib.util
import io
import os
from pathlib import Path
import subprocess
import sys
import tempfile
import unittest
from unittest.mock import patch

sys.dont_write_bytecode = True

spec = importlib.util.spec_from_file_location('mirror', Path(__file__).with_name('mirror-gitlab.py'))
mirror = importlib.util.module_from_spec(spec)
spec.loader.exec_module(mirror)


class MirrorTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.addCleanup(self.temp.cleanup)
        self.root = Path(self.temp.name)
        self.source = self.root / 'source'
        self.dest = self.root / 'destination.git'
        self.env = patch.dict(os.environ, {
            'GIT_CONFIG_NOSYSTEM': '1', 'GIT_CONFIG_GLOBAL': '/dev/null',
            'GIT_AUTHOR_NAME': 'Test', 'GIT_AUTHOR_EMAIL': 'test@example.invalid',
            'GIT_COMMITTER_NAME': 'Test', 'GIT_COMMITTER_EMAIL': 'test@example.invalid',
        })
        self.env.start()
        self.addCleanup(self.env.stop)
        self.run_git(self.root, 'init', '-q', '-b', 'main', str(self.source))
        self.run_git(self.root, 'init', '-q', '--bare', str(self.dest))
        self.run_git(self.dest, 'config', 'receive.advertisePushOptions', 'true')
        self.commit('initial')

    def run_git(self, repo, *args):
        return subprocess.check_output(['git', '-C', str(repo), *args], text=True,
                                       stderr=subprocess.DEVNULL).strip()

    def commit(self, message):
        self.run_git(self.source, 'commit', '-q', '--allow-empty', '-m', message)
        return self.run_git(self.source, 'rev-parse', 'HEAD')

    def sync(self):
        with contextlib.redirect_stdout(io.StringIO()):
            mirror.reconcile(str(self.source), str(self.dest))

    def refs(self):
        return mirror.remote_refs(self.root, str(self.dest))

    def test_initial_empty_tags_and_idempotence(self):
        self.sync()
        before = self.refs()
        self.sync()
        self.assertEqual(before, self.refs())
        self.assertEqual(set(before), {mirror.MAIN})

    def test_full_history_tags_and_excluded_refs(self):
        root = self.run_git(self.source, 'rev-parse', 'HEAD')
        self.run_git(self.source, 'checkout', '-q', '--orphan', 'feature')
        detached = self.commit('outside main')
        self.run_git(self.source, 'tag', '-a', 'release/annotated', '-m', 'annotation')
        self.run_git(self.source, 'tag', 'lightweight')
        self.run_git(self.source, 'update-ref', 'refs/pull/1/head', detached)
        self.run_git(self.source, 'checkout', '-q', 'main')
        tip = self.commit('next')
        self.sync()
        refs = self.refs()
        self.assertEqual(set(refs), {mirror.MAIN, 'refs/tags/release/annotated', 'refs/tags/lightweight'})
        self.assertEqual(refs[mirror.MAIN], tip)
        self.assertEqual(self.run_git(self.dest, 'rev-parse', 'main^'), root)
        self.assertEqual(self.run_git(self.dest, 'rev-parse', 'release/annotated^{}'), detached)
        self.assertEqual(self.run_git(self.dest, 'cat-file', '-t', 'release/annotated'), 'tag')
        self.run_git(self.dest, 'fsck', '--full')

    def test_fast_forward_and_new_tag(self):
        self.sync()
        tip = self.commit('advance')
        self.run_git(self.source, 'tag', 'new')
        self.sync()
        self.assertEqual(self.refs()[mirror.MAIN], tip)
        self.assertEqual(self.refs()['refs/tags/new'], tip)

    def test_deleted_tag_retained(self):
        self.run_git(self.source, 'tag', 'retained')
        self.sync()
        before = self.refs()
        self.run_git(self.source, 'tag', '-d', 'retained')
        self.sync()
        self.assertEqual(before, self.refs())

    def test_conflicting_tag_blocks_entire_update(self):
        self.run_git(self.source, 'tag', 'release')
        self.sync()
        before = self.refs()
        self.commit('advance')
        self.run_git(self.source, 'tag', '-f', 'release')
        with self.assertRaisesRegex(RuntimeError, 'Tag conflict'):
            self.sync()
        self.assertEqual(before, self.refs())

    def test_reannotated_same_commit_conflicts(self):
        self.run_git(self.source, 'tag', '-a', 'release', '-m', 'first')
        self.sync()
        self.run_git(self.source, 'tag', '-fa', 'release', '-m', 'second')
        with self.assertRaisesRegex(RuntimeError, 'Tag conflict'):
            self.sync()

    def test_rewind_rejects_new_tags_atomically(self):
        self.commit('advance')
        self.sync()
        before = self.refs()
        self.run_git(self.source, 'reset', '--hard', 'HEAD^')
        self.run_git(self.source, 'tag', 'must-not-appear')
        with self.assertRaises(subprocess.CalledProcessError):
            self.sync()
        self.assertEqual(before, self.refs())

    def test_divergent_main_rejected(self):
        self.commit('old future')
        self.sync()
        before = self.refs()
        self.run_git(self.source, 'reset', '--hard', 'HEAD^')
        self.commit('different future')
        with self.assertRaises(subprocess.CalledProcessError):
            self.sync()
        self.assertEqual(before, self.refs())

    def test_unexpected_destination_branch_preserved_and_fails(self):
        self.sync()
        self.run_git(self.dest, 'branch', 'unexpected', 'main')
        before = self.refs()
        with self.assertRaisesRegex(RuntimeError, 'Unexpected destination'):
            self.sync()
        self.assertEqual(before, self.refs())

    def test_server_rejection_is_atomic(self):
        self.sync()
        before = self.refs()
        self.commit('advance')
        self.run_git(self.source, 'tag', 'new')
        hook = self.dest / 'hooks' / 'update'
        hook.write_text('#!/bin/sh\n[ "$1" != refs/tags/new ]\n')
        hook.chmod(0o700)
        with self.assertRaises(subprocess.CalledProcessError):
            self.sync()
        self.assertEqual(before, self.refs())

    def test_missing_main_and_unreachable_source_fail(self):
        self.run_git(self.source, 'branch', '-m', 'other')
        with self.assertRaises(subprocess.CalledProcessError):
            self.sync()
        self.assertEqual({}, self.refs())
        with self.assertRaises(subprocess.CalledProcessError):
            mirror.reconcile(str(self.root / 'missing'), str(self.dest))

    def test_atomic_capability_required(self):
        self.run_git(self.dest, 'config', 'receive.advertiseAtomic', 'false')
        with self.assertRaises(subprocess.CalledProcessError):
            self.sync()
        self.assertEqual({}, self.refs())

    def test_concurrent_newer_destination_cannot_be_rewound(self):
        self.sync()
        original = mirror.git
        newer = None
        def race(repo, *args):
            nonlocal newer
            if 'push' in args:
                newer = self.commit('concurrent newer state')
                self.run_git(self.source, 'push', str(self.dest), 'main')
            return original(repo, *args)
        with patch.object(mirror, 'git', side_effect=race):
            with self.assertRaises(subprocess.CalledProcessError):
                self.sync()
        self.assertEqual(self.refs()[mirror.MAIN], newer)

    def test_concurrent_tag_conflict_rejects_main_atomically(self):
        self.sync()
        before = self.refs()[mirror.MAIN]
        self.commit('advance')
        self.run_git(self.source, 'tag', 'new')
        original = mirror.git
        def race(repo, *args):
            if 'push' in args:
                self.run_git(self.dest, 'update-ref', 'refs/tags/new', before)
            return original(repo, *args)
        with patch.object(mirror, 'git', side_effect=race):
            with self.assertRaises(subprocess.CalledProcessError):
                self.sync()
        self.assertEqual(self.refs()[mirror.MAIN], before)
        self.assertEqual(self.refs()['refs/tags/new'], before)

    def test_source_change_before_push_fails_without_write(self):
        original = mirror.remote_refs
        def changed(repo, remote):
            if remote == str(self.source):
                self.commit('changed during fetch')
            return original(repo, remote)
        with patch.object(mirror, 'remote_refs', side_effect=changed):
            with self.assertRaisesRegex(RuntimeError, 'Source changed during fetch'):
                self.sync()
        self.assertEqual({}, self.refs())

    def test_source_change_after_push_is_not_reported_successful(self):
        original = mirror.git
        def changed(repo, *args):
            result = original(repo, *args)
            if 'push' in args:
                self.commit('changed during push')
            return result
        with patch.object(mirror, 'git', side_effect=changed):
            with self.assertRaisesRegex(RuntimeError, 'Source changed during push'):
                self.sync()
        self.assertNotEqual(self.refs()[mirror.MAIN], self.run_git(self.source, 'rev-parse', 'HEAD'))


if __name__ == '__main__':
    unittest.main()
