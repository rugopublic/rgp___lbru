# LIKABLOG.RU — 8-pass review matrix

1. **Baseline preservation** — compare protected custom UI hashes with `qa/custom-ui.sha256`; confirm `.backups/.junk` remains outside runtime.
2. **Syntax** — `php -l` for all application PHP; `node --check` for native JS and Vite config.
3. **Architecture** — admin controllers contain no manual validation or direct Eloquent writes; data access goes through repositories/services.
4. **Media lifecycle** — verify upload placement, replacement-after-commit semantics, soft-delete retention, force-delete cleanup, orphan scanner safety window, and RichTextarea pending-upload resolution.
5. **Domain verticals** — users/groups → products → articles/reference → recipes → courses; verify required routes/views/requests/repositories and relationship boundaries.
6. **Production operations** — scheduler, idempotent publication jobs, queue/failed jobs, calendar, dashboard, backup/deploy sequence.
7. **Commerce/security** — YooKassa intent reuse/idempotence, provider-side status re-fetch, fulfillment idempotence, paid access, private product download, secret handling, admin bootstrap.
8. **Release gate** — clean project-tree check, custom-component checksum check, static audit, dependency/build command checklist, final manual staging smoke test on the real PHP 8.5/Bun environment.

The local container cannot execute Composer/Bun because those runtimes/dependencies are not present and outbound package installation is unavailable. The release report must distinguish static verification from tests that require the real deployment environment.
