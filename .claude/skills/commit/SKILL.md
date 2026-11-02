---
name: commit
description: Commit the current changes to git in sh-selfhelp and/or its nested plugin repos when the user runs /commit. Only on explicit request; never pushes.
disable-model-invocation: true
---

# /commit

The user asked to commit. Committing is allowed only through this command (or an explicit request);
pushing is not — never run `git push` unless the user asks for it separately.

1. Find every repo with changes: the root (`git status --short`) **and** each plugin under
   `server/plugins/sh-shp-*` (they are separate repos the root ignores):
   `for d in server/plugins/sh-shp-*/; do (cd "$d" && [ -n "$(git status --porcelain)" ] && echo "$d"); done`
2. For each repo, review `git status` and `git diff`. Don't stage: `server/service/globals_untracked.php`,
   other `globals_untracked.php`, credentials/API keys, uploads, logs (`*.log`), local IDE config, or files
   that only differ in line endings. If unsure whether a file belongs in the commit, ask.
3. Stage the intended files explicitly by path (not `git add -A` blindly) and commit with a short message:
   imperative summary line (≤72 chars), blank line, bullets of what changed and why. Mention the new
   version if a migration bumped it. If the user passed text after `/commit`, use it as the message.
4. Report per repo: commit hash, summary line, files included, and anything deliberately left uncommitted.
