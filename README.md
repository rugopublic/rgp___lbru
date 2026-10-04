# LIKABLOG.RU

Production-oriented Laravel application for the Blog of Lika Vule: public content catalog, paid Products and Courses, administration panel, scheduled publication, media lifecycle and YooKassa checkout.

## Runtime

- PHP 8.5 recommended in production (project requires PHP 8.3+).
- Laravel 13.
- MySQL.
- Bun + Vite 8.
- Bootstrap 5.3, SCSS and native JavaScript.
- Existing custom UI components are treated as an immutable API. Do not edit `resources/web/components/ui` without an explicit architecture decision.

## Architecture

The production contract is deliberately strict:

`migration → model/relations/casts → observer → Form Request → service when orchestration is required → controller → route/policy → Blade → JS/SCSS → QA`

All data access for the application domains is routed through repositories. Admin controllers are HTTP-only and do not contain manual validation or direct Eloquent persistence. Services orchestrate business operations; they do not become a generic CRUD layer.

All Blade views live below `resources/web`. Frontend and admin behaviour is composed from Vite entry points in `vite.config.js`.

## Scope completed from the production plan

- Users and Groups.
- Products and categories, including private paid-file delivery.
- Articles and categories with Product attachments and scheduled publication.
- Reference items and categories with KBJU/metadata and Product attachments.
- Recipes with categories, nested ingredient groups, Typeahead, Sortable, video and Product attachments.
- Courses with categories, teachers, nested Module → Lesson structure, existing quiz/test domain, lesson editor and publication state.
- Shared Sortable, Typeahead, FormSender/ChangeTracker and existing FileService infrastructure preserved and extended rather than replaced.
- RichTextarea inline image lifecycle. RichTextarea tables are intentionally out of scope.
- Scheduler, idempotent publication job, queue/failed-job operational path, calendar, dashboard and orphan-media scan.
- YooKassa payment intents, idempotency, provider status re-check, fulfillment and access grants.
- Public sales surfaces for Courses and Products.

## Important production rules

### Custom UI is immutable

The files under `resources/web/components/ui` are protected by a SHA-256 manifest in `qa/custom-ui.sha256`. The release gate verifies that their contents have not changed.

### Media lifecycle

Content media uses predictable owner folders such as `courses/{public_id}` and `articles/{public_id}`. Replaced media is deleted after a successful database commit. Soft-deleted entities retain their media. Force-deleted entities remove their owned media directory. Inline RichTextarea images are moved from the existing temporary upload area through `FileService`; base64/data-URI storage is not used.

### Paid files

Product files are stored on a private filesystem disk and are served through an authenticated purchase check. Do not expose product files through a public `storage` URL.

### Publishing

Scheduled publication is handled by Laravel Scheduler every minute. The publication job is idempotent and is safe to receive the same schedule item more than once.

### Payments

YooKassa credentials live only in environment variables. The application persists a local payment intent/idempotence key, sends that key to YooKassa and verifies the provider payment object again when processing a webhook before granting access.

## Local setup

The canonical Windows/PowerShell development procedure is documented in [`docs/local-development.md`](docs/local-development.md). It covers MySQL bootstrap, `.env`, the clean canonical migration, storage linking, admin creation, Bun/Vite HMR, queue worker and the optional HTTPS/Nginx profile.

Minimal daily startup:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
bun run dev
php artisan queue:work --tries=3 --timeout=120
```

For a production asset build (the delivery intentionally does not ship a stale `public/build`):

```powershell
bun install --frozen-lockfile
bun run build
```

## QA

Static release gate:

```bash
bash qa/production-audit.sh
bash qa/run-8-passes.sh
python3 tools_route_audit.py
```

When Composer and project dependencies are available, run the real Laravel test suite as well:

```bash
php artisan test
```

The provided development container intentionally does not contain Composer/Bun/vendor/node_modules and has no external package-install network access, so those environment-dependent commands are not claimed as locally executed in this delivery.

## Production deployment

See [`docs/production/reg-ru.md`](docs/production/reg-ru.md) for a step-by-step REG.RU deployment procedure, queue/scheduler setup, YooKassa configuration, admin bootstrap and smoke tests.

See [`docs/local-development.md`](docs/local-development.md) for the local development procedure.

See [`docs/architecture/production-contract.md`](docs/architecture/production-contract.md) for the architectural invariants used by the release gate.

See [`docs/architecture/codebase-map.md`](docs/architecture/codebase-map.md) for the folder-by-folder responsibility map, maintenance hotspots and feature navigation.

See [`docs/architecture/feature-registry.md`](docs/architecture/feature-registry.md) for feature-by-feature entry points, business services, data ownership and critical invariants.

Run `bash qa/documentation-audit.sh` before release to verify PHPDoc/JSDoc coverage.

The current documented baseline covers **1,249 named PHP functions/methods** and **75 JavaScript modules**. The repository uses `bun.lock` as the canonical frontend lockfile; `npm run build` may be used only as a local validation probe when dependencies are already installed.

See [`docs/reports/documentation-pass-2026-10-04.md`](docs/reports/documentation-pass-2026-10-04.md) for the latest coverage and release evidence.

## First admin

Create an administrator from the server with:

```bash
php artisan likablog:admin --email=admin@likablog.ru --name="Администратор" --password='STRONG_PASSWORD'
```

Use a unique password and keep it outside the repository.
