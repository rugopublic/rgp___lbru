# LIKAblog — карта кодовой базы и сопровождения

> Цель этого документа — дать сопровождающему человеку ответ на вопрос **«где находится ответственность за эту функцию и что можно безопасно менять»** без чтения проекта целиком.

## Главный поток данных

```text
HTTP route
  → Controller
  → Form Request / Policy
  → Repository и/или Service
  → Model / Database
  → Observer / Job при необходимости
  → JSON / Redirect / Blade
  → Vite entry point
  → reusable UI component
```

Для платных операций поток строже:

```text
Checkout
  → PaymentService
  → local PaymentIntent + unique idempotence key
  → YooKassa
  → webhook
  → provider status re-check
  → PaymentFulfillmentService
  → Transaction + Purchase/Enrollment
  → access
```

Повтор webhook, повтор запроса checkout или повторная доставка job не должны создавать второй платёж или второй доступ. База данных содержит соответствующие unique constraints; application-level checks не являются единственной защитой.

## Папки: ответственность

| Папка | Что там живёт | Что менять там безопасно |
|---|---|---|
| `app/Console/Commands` | Artisan-команды администратора/оператора | Только командную оболочку; бизнес-логику держать в service |
| `app/Http/Controllers` | HTTP orchestration: request → service/repository → response | Не добавлять ручную валидацию или прямые Eloquent writes |
| `app/Http/Requests` | Валидация и нормализация входных данных | Менять правила формы, не дублировать их в controller |
| `app/Http/Middleware` | HTTP-предпосылки и доступ | Не помещать сюда бизнес-операции |
| `app/Models` | Eloquent-модели, связи, casts, локальные scopes | Изменения схемы сначала отражать в canonical migration |
| `app/Repositories/Contracts` | Контракт доступа к данным | Менять только вместе с реализациями и потребителями |
| `app/Repositories/Eloquent` | Фактические Eloquent queries/persistence | Здесь живут запросы; не распылять их по controllers |
| `app/Services` | Бизнес-сценарии, транзакции, orchestration | Основное место для сложных действий |
| `app/Observers` | Побочные эффекты model lifecycle | Проверять soft delete / force delete / media lifecycle |
| `app/Jobs` | Очередь и idempotent background work | Любая job должна быть безопасна к повторной доставке |
| `app/View/Components` | PHP-часть Blade UI components | Менять контракт только осознанно |
| `app/Enums`, `app/Rules`, `app/Traits`, `app/Helpers` | Повторно используемые доменные примитивы | Изменения могут влиять сразу на несколько доменов |
| `database/migrations` | **Единая canonical schema** для чистой БД | Не восстанавливать исторические миграции; baseline меняется осознанно |
| `database/factories`, `database/seeders` | Dev/test/sample data | Не класть production secrets |
| `resources/scripts/admin` | Admin page entry points и admin shared code | Только browser orchestration; бизнес-правила остаются на сервере |
| `resources/scripts/components` | Shared browser UI primitives | Это API для страниц; менять аккуратно |
| `resources/scripts/http` | Axios/upload transport | Центральная точка HTTP поведения браузера |
| `resources/styles` | SCSS для public/admin | Не ломать immutable UI contract |
| `resources/web/components/ui` | Shared Blade UI API | **Защищено SHA-256 manifest; без архитектурного решения не менять** |
| `resources/web/admin` | Admin Blade pages | Страница должна собираться из shared UI и page entry point |
| `resources/web/frontend` | Public Blade pages | Тут живёт presentation public site |
| `routes` | URL → middleware → controller mapping | Не помещать бизнес-логику в routes |
| `tests` | Framework/feature/unit contracts | При изменении поведения сначала обновлять/расширять тест |
| `qa` | Release gates и structural audits | Новое production-critical правило должно иметь gate |
| `docs` | Актуальная операционная и архитектурная документация | Считается частью production delivery |
| `documentation` | Исторические рабочие записи | Не использовать как source of truth, если `docs/` говорит иначе |
| `local` | Local developer tasks/SSL helpers | Только dev tooling; не копировать secrets в repo |
| `public` | Browser-facing public assets | Не класть туда private product files |

## Где искать конкретную проблему

| Симптом | Сначала смотреть |
|---|---|
| Форма не принимает данные | `app/Http/Requests` → controller → service |
| Данные сохраняются не так | repository → model → canonical migration |
| Дублируется платёж | `PaymentService` → `PaymentIntent` constraints → `PaymentFulfillmentService` |
| Webhook дал доступ второй раз | `PaymentFulfillmentService` → purchase/enrollment unique constraints |
| Файл пропал после замены | media service/observer → transaction commit hooks |
| Soft delete удалил файл | observer/media service — это regression |
| Scheduled content не опубликовалось | scheduler → `PublishScheduledContentItem` → publication service → failed jobs |
| JS не запускается на странице | соответствующий `resources/scripts/admin/pages/**` entry → `resources/scripts/admin/app.js` / Vite entry map |
| HMR не работает | `.env` Vite URLs → `vite.config.js` → local dev docs |
| UI-компонент сломался на всех страницах | `resources/web/components/ui` + custom UI checksum |

## Production-critical invariants

1. **Bun — canonical package manager.** `bun.lock` — единственный frontend lockfile. `npm` не должен создавать `package-lock.json`.
2. **Canonical migration — единственный initial schema.** Старую историю миграций не восстанавливать.
3. **Private paid files — только private disk + authorized endpoint.**
4. **Media replacement — delete only after successful DB commit.**
5. **Soft delete — media remains. Force delete — owner media is removed.**
6. **Payment fulfillment — provider status is re-checked before access is granted.**
7. **Payment/access uniqueness — DB constraints are mandatory protection against double fulfillment.**
8. **Scheduled publication — queue job must be idempotent.**
9. **Shared UI — checksum protected.**
10. **A release is not production-ready until environment-dependent Composer/Bun/framework/payment smoke checks pass on staging-equivalent infrastructure.**

## Documentation coverage contract

The source baseline contains **1,249 named PHP functions/methods** and **75 JavaScript modules**. Every named PHP function is required to have a nearby `/** ... */` PHPDoc block; every tracked JavaScript module is required to start with a JSDoc module header. `qa/documentation-audit.sh` enforces both rules.

## Admin page inventory

- `articles/categories/create.js`
- `articles/categories/edit.js`
- `articles/categories/index.js`
- `articles/create.js`
- `articles/edit.js`
- `articles/index.js`
- `calendar/index.js`
- `calendar.js`
- `courses/categories/create.js`
- `courses/categories/edit.js`
- `courses/categories/index.js`
- `courses/create.js`
- `courses/edit.js`
- `courses/index.js`
- `courses/lessons/edit.js`
- `courses/structure.js`
- `courses/students/index.js`
- `courses/students/show.js`
- `create-modal.js`
- `dashboard.js`
- `mailings/create.js`
- `mailings/index.js`
- `mailings.js`
- `products/categories/create.js`
- `products/categories/edit.js`
- `products/categories/index.js`
- `products/create.js`
- `products/edit.js`
- `products/index.js`
- `products/show.js`
- `quizzes/create.js`
- `quizzes/edit.js`
- `quizzes/index.js`
- `recipes/categories/create.js`
- `recipes/categories/edit.js`
- `recipes/categories/index.js`
- `recipes/create.js`
- `recipes/edit.js`
- `recipes/index.js`
- `reference/categories/create.js`
- `reference/categories/edit.js`
- `reference/categories/index.js`
- `reference/edit.js`
- `reference/ingredients/create.js`
- `reference/ingredients/edit.js`
- `reference/ingredients/index.js`
- `smm/create.js`
- `smm/index.js`
- `transactions/index.js`
- `transactions/show.js`
- `users/create.js`
- `users/edit.js`
- `users/index.js`
