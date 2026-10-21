# GitLab recovery mirror

Coordination and deployment evidence: [Issue #42](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/42).

GitHub is the sole development authority. The [workflow](../../.github/workflows/gitlab-mirror.yml)
copies consolidated Git history from
`https://github.com/paycrypto-me/paycrypto-me-primitives-php.git` to
`git@gitlab.com:paycrypto-me/paycrypto.me-primitives-php-mirror.git`.
The [reconciliation script](../../scripts/mirror-gitlab.py) uses system Git and
Python's standard library. This is repository infrastructure, within existing
architectural boundaries; it does not require a new PIP under the
[Materiality Gate](../specifications/paycrypto-pip-specification-v1.0.md#2-the-materiality-gate).

## Policy and execution

- Only `refs/heads/main` and `refs/tags/*` are copied, with their complete reachable
  Git objects, including annotated tags and tags outside main's history.
- Main is created or advanced by fast-forward only. Rewinds and divergence fail.
- Tags are added or left identical. Comparison uses the tag object ID, not just
  the peeled commit. Conflicting tags fail; destination-only tags are retained.
- Unexpected advertised destination references fail for investigation; nothing
  is pruned. GitHub feature branches, pull-request refs and internal refs are not
  fetched or pushed. Server-hidden refs cannot be audited with `ls-remote`.
- Each execution fetches into a fresh bare repository with explicit refspecs,
  without shallow history or object filters. Checkout supplies the script from
  authoritative main; the checkout's refs and event SHA are not mirror inputs.
- One atomic push sends main and tags together, without force, mirror or prune.
  Rejection of any update fails the whole push. Lack of server support for atomic
  pushes fails closed. Server reference locking and non-fast-forward/tag checks
  also protect against racing writes; Actions ordering is not the safety proof.
- A single concurrency group covers all triggers, with no cancellation of the
  running job. Pending runs can be replaced. Every new run fetches current state.
- Source refs are checked before and after pushing; destination refs are checked
  after pushing. A detected source change or incomplete verification fails and
  requires reconciliation again, without rollback. There is no distributed
  transaction across providers: success describes the state observed by these
  checks, not a promise that GitHub will remain unchanged afterward.

Runs occur on pushes to main or tags, on manual dispatch from main, and daily at
**04:23 UTC** (`23 4 * * *`). The off-hour minute reduces peak-hour contention.
The daily run independently reconciles current state. GitHub can delay/drop
scheduled jobs, omit events for bulk tag pushes, and disable schedules in public
repositories after 60 days without repository activity. Check run freshness;
this is not a guaranteed 24-hour recovery-point objective.

## Security and destination settings

The only repository secret used is `PAYCRYPTO_ME_GITLAB_MIRROR`. Its value is
written to a temporary identity under the hosted runner's temporary directory,
with directory mode 700 and file mode 600. It is removed on exit and in an
`always()` cleanup step. Abrupt runner loss relies on hosted runner disposal.
No local owner SSH configuration is read or changed by this workflow.

SSH uses isolated configuration, no agent, only the dedicated identity, batch
mode and strict host verification. The pinned ED25519 host key comes directly
from [GitLab's official host-key documentation](https://docs.gitlab.com/user/gitlab_com/#ssh-known_hosts-entries),
checked on 2026-10-21. Its published fingerprint is
`SHA256:eUXGGm1YGsMAS7vkcx6JOJdOGHPem5gQp4taiCfCLB8`.
No runtime keyscan or trust-on-first-use is used. Host-key rotation intentionally
fails until a maintainer verifies the replacement against official GitLab
information and reviews a workflow update.

Actions permissions are `contents: read`. Official checkout is pinned to a full
commit SHA and does not persist credentials. The public source fetch needs no
credential. Fork runs are skipped; manual dispatch must select main. Repository
writers who can change workflows or tag workflow revisions remain in the trust
boundary of repository secrets. Main checkout does not make malicious workflow
changes safe. There are no PR-triggered mirror jobs.

Before activation, inspect the destination's Settings → Repository:

- Keep the existing dedicated deploy key enabled with write access. Authentication
  and read access do not prove push authorization.
- If main is protected, authorize this specific key under **Allowed to push and
  merge** and ensure its owner has the required project/branch access. Keep force
  pushes disabled; do not unprotect the branch as a workaround.
- For protected tag patterns that match source tags, authorize the key under
  **Allowed to create**, with its owner a project member with read access.
  Inspect overlapping rules. Do not broadly grant ordinary contributors access.

See GitLab's [deploy-key rules](https://docs.gitlab.com/user/project/deploy_keys/)
and [protected-tag rules](https://docs.gitlab.com/user/project/protected_tags/#allow-deploy-keys-to-create-protected-tags).
The push uses `-o ci.skip` to avoid ordinary destination push pipelines without
changing commits. It does not disable merge-request pipelines or external
integrations; see [GitLab push options](https://docs.gitlab.com/topics/git/commit/#push-options).
For a recovery-only project, the owner may disable destination CI/CD and Auto
DevOps if enabled, and avoid integrations and merge requests. Such settings
changes require owner authorization and are not performed by the workflow.

## First activation and verification

Local implementation is not evidence that the mirror is operational. No live
synchronization was performed during Stage A.

1. Obtain approval for GitHub publication **and the first GitLab synchronization**.
   Record ordinary work in a General Issue and link the implementation PR.
   Publishing/merging this workflow into main can immediately run the first push;
   approval must precede that merge, not just a later manual dispatch.
2. Review destination permissions above and the existing secret's availability.
   Do not recreate or expose the secret. Check that the destination still has no
   unexpected refs. If an authorization change is needed, obtain approval for
   that specific change.
3. Publish the reviewed changes through the normal PR process and merge to main.
   Watch the automatically triggered **GitLab mirror** run. If needed, dispatch:

   ```sh
   gh workflow run gitlab-mirror.yml --ref main --repo paycrypto-me/paycrypto-me-primitives-php
   gh run list --workflow gitlab-mirror.yml --repo paycrypto-me/paycrypto-me-primitives-php
   gh run watch RUN_ID --exit-status --repo paycrypto-me/paycrypto-me-primitives-php
   ```

4. Independently compare current refs (these public read-only commands need no key):

   ```sh
   git ls-remote --refs https://github.com/paycrypto-me/paycrypto-me-primitives-php.git refs/heads/main 'refs/tags/*'
   git ls-remote --refs https://gitlab.com/paycrypto-me/paycrypto.me-primitives-php-mirror.git
   ```

   Main must exist and match; every current source tag must match exactly;
   destination-only tags may remain; no other advertised refs may exist. If the
   source advanced while checking, run reconciliation again. Record the successful
   Actions run URL and verified main SHA in the implementation Issue/PR before
   calling the mirror operational. Do not duplicate transient status here.

## Monitoring and recovery

Use the [Actions workflow page](https://github.com/paycrypto-me/paycrypto-me-primitives-php/actions/workflows/gitlab-mirror.yml)
to inspect the latest run, failures and verification output. Nonzero Git exits,
network errors, protection rejections, tag conflicts, source changes and failed
verification fail the job; errors are not suppressed. Check GitHub Actions
notification preferences and the last successful run's age. No separate alert
service is installed. Manual dispatch reconciles and verifies; it is not read-only.

For divergence, preserve the destination in an offline clone or bundle first.
Compare the conflicting object IDs and ancestry with authoritative GitHub.
Determine whether this is an authorized GitHub rewrite, accidental destination
write, stale execution, or a protection/credential failure. For transient errors,
rerun after correction. For rewrites/conflicts, stop and obtain a reviewed,
explicit decision about preserving the old history and repairing the exact refs
(or selecting a new empty destination). The workflow never forces, deletes or
relaxes protection. Repeated schedules will continue to fail until resolved.
A rewrite that happened entirely between observations and never displaced an
already mirrored ref cannot be detected from current refs alone.

To rotate the deploy key, an authorized owner creates a new dedicated key,
registers its public half with write access and any required protected-ref
permissions, replaces the value of the **same** GitHub secret, runs and verifies
reconciliation, then revokes the old key and removes obsolete private copies.
Never print private material in logs or attach it to an Issue/PR.

To recover during a GitHub outage, fetch consolidated history into an empty
local repository, verify it, and inspect main:

```sh
git init recovered-primitives
cd recovered-primitives
git fetch --no-tags https://gitlab.com/paycrypto-me/paycrypto.me-primitives-php-mirror.git \
  refs/heads/main:refs/heads/main 'refs/tags/*:refs/tags/*'
git fsck --full
git switch main
```

Preserve this recovery copy before publishing to a replacement authoritative
remote. Choose the replacement through a separate owner decision; the mirror
workflow never pushes from GitLab to GitHub.

## Coverage limits

Unmerged development branches are not protected unless their objects are
reachable from main or mirrored tags. Losing such work during a GitHub disaster
is the accepted trade-off. Issues, Pull Requests, Actions execution history,
release assets, repository settings and other platform metadata are excluded.
Git LFS payloads and submodule repositories need separate backup arrangements;
Git only preserves their pointer/reference data. No LFS usage was found in the
local history inspected during implementation; introducing LFS requires a
separate coverage review. This mutable replica is neither immutable nor a
comprehensive backup. Retained deleted tags may preserve data intentionally
removed from GitHub; any removal requires a separate reviewed decision.

## Local validation

Run `python3 scripts/test-mirror-gitlab.py` for offline integration tests with
real temporary Git repositories and no credentials. CI runs this same suite.
It covers creation, idempotence, full history, off-main annotated/lightweight
and nested tags, excluded refs, fast-forward, deleted tags, conflicting tags,
rewinds, divergence, unexpected destination refs, atomic server rejection,
missing main, unavailable source, absent atomic capability, source races and
concurrent destination main/tag updates.
Run `python3 scripts/check-doc-links.py` and validate the workflow with actionlint.
These checks do not prove GitLab push permission or a live successful mirror.

Investigation on 2026-10-21 found the source default branch was main, with no
advertised tags, no GitHub rulesets and no main branch protection. Public GitLab
read access advertised no refs; the public protected-branches API returned an
empty list, while protected-tags access returned HTTP 403. Effective key
permissions, tag restrictions, CI/CD and Auto DevOps settings remain owner-side
activation checks. No security settings were changed.

Underlying behavior: [Git push atomicity and ref rules](https://git-scm.com/docs/git-push),
[Actions concurrency](https://docs.github.com/en/actions/how-tos/write-workflows/choose-when-workflows-run/control-workflow-concurrency),
and [workflow event limitations](https://docs.github.com/en/actions/reference/workflows-and-actions/events-that-trigger-workflows).
