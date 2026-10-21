#!/usr/bin/env python3
"""Reconcile consolidated Git history; any failure exits nonzero, never force/prune."""
import argparse
import subprocess
import sys
import tempfile
from pathlib import Path

SOURCE = 'https://github.com/paycrypto-me/paycrypto-me-primitives-php.git'
DESTINATION = 'git@gitlab.com:paycrypto-me/paycrypto.me-primitives-php-mirror.git'
MAIN = 'refs/heads/main'


def git(repo, *args):
    return subprocess.check_output(
        ['git', '-C', str(repo), '-c', 'core.hooksPath=/dev/null', *args],
        text=True,
    ).strip()


def references(output):
    return {ref: oid for oid, ref in (line.split() for line in output.splitlines())}


def selected(ref):
    return ref == MAIN or ref.startswith('refs/tags/')


def remote_refs(repo, remote):
    return references(git(repo, 'ls-remote', '--refs', remote))


def check_destination(refs):
    unexpected = sorted(ref for ref in refs if not selected(ref))
    if unexpected:
        raise RuntimeError(f'Unexpected destination references; investigate: {unexpected}')


def reconcile(source, destination):
    with tempfile.TemporaryDirectory(prefix='gitlab-mirror-') as directory:
        repo = Path(directory)
        git(repo, 'init', '--bare', '--quiet', '--initial-branch=main')
        # No checkout, shallow boundary, filter, branch wildcard, or automatic tags.
        git(repo, 'fetch', '--no-tags', source,
            f'{MAIN}:{MAIN}', 'refs/tags/*:refs/tags/*')
        expected = references(git(repo, 'for-each-ref', '--format=%(objectname) %(refname)'))
        if MAIN not in expected or not all(map(selected, expected)):
            raise RuntimeError('Source snapshot is missing main or contains unexpected references')
        git(repo, 'fsck', '--full', '--no-reflogs')
        before = remote_refs(repo, destination)
        check_destination(before)
        for ref, oid in expected.items():
            if ref.startswith('refs/tags/') and ref in before and before[ref] != oid:
                raise RuntimeError(f'Tag conflict: {ref}; human review required')
        if expected != {ref: oid for ref, oid in remote_refs(repo, source).items() if selected(ref)}:
            raise RuntimeError('Source changed during fetch; rerun reconciliation')
        # Atomic receive rejects the entire update on tag conflicts, non-fast-forward
        # main, protection failures or races. Never fall back to a non-atomic push.
        # Explicit wildcard expands only locally fetched tags (including zero tags).
        git(repo, '-c', 'push.followTags=false', 'push', '--atomic', '--porcelain',
            '-o', 'ci.skip', destination, f'{MAIN}:{MAIN}', 'refs/tags/*:refs/tags/*')
        after = remote_refs(repo, destination)
        check_destination(after)
        if any(after.get(ref) != oid for ref, oid in expected.items()):
            raise RuntimeError('Destination does not match the fetched source snapshot')
        if any(after.get(ref) != oid for ref, oid in before.items() if ref.startswith('refs/tags/')):
            raise RuntimeError('An existing destination tag changed during reconciliation')
        if expected != {ref: oid for ref, oid in remote_refs(repo, source).items() if selected(ref)}:
            raise RuntimeError('Source changed during push; rerun reconciliation (no rollback)')
        retained = len(after.keys() - expected.keys())
        print(f'Verified main={expected[MAIN]}, {len(expected) - 1} source tags; '
              f'{retained} destination-only tags retained.')


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--source', default=SOURCE, help='Override only for isolated local tests')
    parser.add_argument('--destination', default=DESTINATION, help='Override only for isolated local tests')
    args = parser.parse_args()
    try:
        reconcile(args.source, args.destination)
    except (RuntimeError, subprocess.CalledProcessError, OSError) as error:
        print(f'Mirror failed: {error}', file=sys.stderr)
        sys.exit(1)
