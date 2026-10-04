# LIKABLOG.RU — Production Contract

This document is the invariant set used while completing the project. It is intentionally stricter than the minimum Laravel conventions because the application is a paid content system.

## Boundaries

### UI components

`resources/web/components/ui` is immutable. Existing Form, ChangeTracker, FormSender, Sortable, Typeahead, RichTextarea, media dropzone and uploader components are consumed as an API.

### Views

Every Blade view is below `resources/web`. `resources/views` is not a runtime view root.

### Data access

Admin/public application code talks to repositories. Repositories own Eloquent queries and persistence. Services orchestrate transactions, publication, media placement, nested structures and payments when required.

### HTTP

Controllers select a repository/service, authorize through middleware/policy and return a response. Mutation validation is done with Form Requests.

### Frontend

Native JavaScript + SCSS. Vite entry points are declared in `vite.config.js`. No Vue or Tailwind plugin participates in the build.

## Media invariants

1. Temporary uploads are owned by the authenticated uploader.
2. A temporary upload is placed only when `ready()` returns true.
3. Business media validates detected MIME, extension and size before placement.
4. Replaced media is removed only after a successful DB commit.
5. Soft deletion never removes media.
6. Force deletion removes the owner media folder.
7. RichTextarea images are persisted as files, never as data URIs.
8. Orphan cleanup is opt-in and protected by a safety age threshold.

## Structure invariants

Sortable state is serialized into ordinary hidden/input fields. The server restores hierarchy from submitted state and validates ownership of every submitted entity ID. Cross-container moves are limited to the configured Sortable group.

## Scheduling invariants

The schedule scanner runs every minute. Jobs are idempotent. Publication state changes use precise due/status conditions. Re-running the scanner must not duplicate publication or access.

## Commerce invariants

A payment intent has a local idempotence key. The YooKassa request uses that key. Webhooks are not trusted as payment truth by themselves: the current provider payment object is fetched before fulfillment. Transaction creation and purchase/enrollment granting are idempotent.

## Course invariants

Course editor fields follow the current course schema only. RichText fields are `pitch`, `objective` and `skills`. The structure is Module → Lesson. The existing quiz domain remains the single source of truth. No parallel LMS schema is introduced.

## QA gate

A release requires:

- PHP syntax clean across application/config/routes/tests.
- Native JS syntax clean.
- Vite configuration syntax clean.
- repository boundary clean.
- all Blade views under `resources/web`.
- custom UI hashes unchanged.
- route reference audit with zero unresolved names.
- RichTextarea table feature absent because it was explicitly removed from scope.
- eight repeatable static/regression passes.

Real Composer/Bun builds and framework tests must be run on the deployment-equivalent environment before production release.
