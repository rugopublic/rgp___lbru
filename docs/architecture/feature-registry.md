# LIKAblog — реестр фич и точек сопровождения

Этот документ — operational map проекта. Его задача — позволить сопровождающему через год начать с **названия фичи**, а не с поиска по исходникам.

Для каждой фичи указаны четыре уровня:

`HTTP/UI entry → application/service → data → browser/UI`

Правило сопровождения: сначала меняется слой, которому принадлежит ответственность. Не переносить бизнес-логику в Blade, JS или route-файлы только потому, что там быстрее найти нужное место.

## 1. Пользователи и администраторы

**Что делает:** регистрация, вход, password reset, смена email/пароля, управление пользователями, группы, cookie/legal state, удаление пользователя и grace-period.

**HTTP:**
- `app/Http/Controllers/Auth/*`
- `app/Http/Controllers/Register/RegisterController.php`
- `app/Http/Controllers/User/*`
- `app/Http/Controllers/Admin/UserController.php`
- `app/Http/Controllers/Admin/GroupController.php`

**Application:**
- `app/Services/Auth/*`
- `app/Services/Register/RegisterService.php`
- `app/Services/User/*`
- `app/Services/Admin/User/AdminUserService.php`

**Data:** `User`, `Group`, password/email/token repositories и соответствующие migrations.

**Browser:** `resources/scripts/admin/pages/users/*`, auth/register frontend entries и shared form/http components.

**Осторожно:** удаление пользователя связано с grace-period. Проверять `User` scopes/observer/job и не превращать soft delete в физическое удаление.

## 2. Каталог и платные продукты

**Что делает:** категории продуктов, карточка товара, цена, media и продажа digital product.

**HTTP:** `app/Http/Controllers/Admin/Product*`, `app/Http/Controllers/Public/CatalogController.php`, `ProductFileController.php`, `PaymentController.php`.

**Application:** `app/Services/Admin/Content/ProductService.php`, `ContentMediaService.php`, `app/Services/Public/PaymentService.php`, `PaymentFulfillmentService.php`.

**Data:** `Product`, `ProductCategory`, `ProductPurchase`, `PaymentIntent`, `Transaction` + repositories.

**Browser:** `resources/scripts/admin/pages/products/*`, public catalog/checkout scripts и shared upload/http components.

**Осторожно:** private digital file нельзя превращать в публичный `storage` URL. Доступ выдаётся только после подтверждённого платежа. DB uniqueness — обязательная защита от повторной покупки/fulfillment.

## 3. Статьи и отложенная публикация

**Что делает:** CRUD статей, категории, cover, RichTextarea, inline media, связь article↔product и scheduled publication.

**HTTP:** `app/Http/Controllers/Admin/Article*`, `app/Http/Controllers/Admin/CalendarController.php`.

**Application:** `app/Services/Admin/Content/ArticleService.php`, `ContentMediaService.php`, `RichTextMediaService.php`, `app/Services/Public/PublicationService.php`.

**Data:** `Article`, `ArticleCategory`, media ownership/SEO repositories и canonical migration.

**Browser:** `resources/scripts/admin/pages/articles/*`, calendar entry points, RichTextarea/media components.

**Осторожно:** scheduled state меняется scheduler/job, а не JS. Inline image после сохранения должна быть ссылкой на media, а не base64 в статье.

## 4. Рецепты

**Что делает:** категории, основной recipe record, структура шагов, ingredients, cover/video и publication status.

**HTTP:** `app/Http/Controllers/Admin/Recipe*`.

**Application:** `RecipeService.php`, `RecipeStructureService.php`, `ContentMediaService.php`, `RichTextMediaService.php`.

**Data:** `Recipe`, `RecipeCategory`, `RecipeIngredient`, `RecipeIngredientGroup`, `RecipeStep`, `RecipeFile` и repositories.

**Browser:** `resources/scripts/admin/pages/recipes/*` + shared sortable/media/form components.

**Осторожно:** sortable DOM и hidden inputs — источник порядка структуры. Нельзя хранить фактический порядок только в JS state.

## 5. Справочник ингредиентов и KBJU

**Что делает:** категории reference data, ингредиенты, nutrition/KBJU values, поисковые поля и default grammage.

**HTTP:** `app/Http/Controllers/Admin/Reference*`.

**Application:** `app/Services/Admin/Content/ReferenceService.php`.

**Data:** `ReferenceItem`, `ReferenceCategory`, reference repository.

**Browser:** `resources/scripts/admin/pages/reference/*`, typeahead компоненты.

**Осторожно:** большие справочные поля должны идти через typeahead-контракт, а не превращаться в гигантский `<select>`.

## 6. Курсы: структура и обучение

**Что делает:** курс, категория, teachers, modules, lessons, steps, students/enrollments и learner flow.

**HTTP:** `app/Http/Controllers/Admin/Course*`, `CourseStructureController.php`, `CourseLessonController.php`, `app/Http/Controllers/Public/CourseLearnController.php`.

**Application:** `app/Services/Admin/Course/*`, `app/Services/Public/PublicationService.php`.

**Data:** `Course`, `CourseCategory`, `CourseEnrollment`, `CourseModule`, `CourseLesson`, `CourseStep`, `Teacher` и связанные repositories.

**Browser:** `resources/scripts/admin/pages/courses/*`, `resources/scripts/admin/courses/*`, sortable lesson/step infrastructure.

**Осторожно:** course structure имеет несколько уровней сортировки. Изменение порядка должно проходить через DOM → hidden inputs → server-side validation/persistence.

## 7. Quiz

**Что делает:** quiz creation/editing, questions и привязка к материалам/урокам.

**HTTP:** `app/Http/Controllers/Admin/` соответствующие quiz endpoints.

**Application:** `QuizRepository` и связанные domain contracts.

**Data:** `Quiz` и вложенные quiz models/pivots.

**Browser:** `resources/scripts/admin/pages/quizzes/*`.

**Осторожно:** quiz может быть связан с разными типами контента; при изменении polymorphic relations проверять migration + model relation + repository одновременно.

## 8. Платежи YooKassa

**Что делает:** локальный payment intent, checkout, provider API, webhook, transaction и выдача доступа.

**HTTP:** `app/Http/Controllers/Public/PaymentController.php`.

**Application:** `PaymentService.php`, `PaymentFulfillmentService.php`, `YooKassaClient.php`.

**Data:** `PaymentIntent`, `Transaction`, `ProductPurchase`, `CourseEnrollment`.

**Browser:** checkout-related frontend modules и public product/course pages.

**Критический контракт:**

```text
checkout
  → local intent + unique idempotence key
  → YooKassa
  → webhook
  → provider status re-check
  → fulfillment
  → transaction
  → purchase/enrollment
  → access
```

Повтор webhook, double-click checkout и повторная доставка job должны быть безопасны. Защита должна существовать в БД, а не только в controller/service.

## 9. Медиа и файловый lifecycle

**Что делает:** upload session, placement, replacement, inline media, product private files, owner cleanup.

**HTTP:** `Generic/FileUpload/FileUploadController.php`, `Public/ProductFileController.php`.

**Application:** `Admin/Generic/FileService.php`, `ContentMediaService.php`, `RichTextMediaService.php` + observers.

**Data:** upload ownership/metadata + `public_id` owner folders.

**Storage layout:**

```text
teachers/{public_id}
products/{public_id}
articles/{public_id}
reference/{public_id}
recipes/{public_id}
courses/{public_id}
```

**Критический lifecycle:** replace → DB commit → old file removal; soft delete → media remains; force delete → owner media removed.

## 10. Scheduler, queue и календарь

**Что делает:** scheduled publication, background processing, failed jobs, operational calendar.

**HTTP:** `Admin/CalendarController.php` + scheduler-related admin UI.

**Application:** `PublicationService`, `PublishScheduledContent`, `PublishScheduledContentItem` и queue infrastructure.

**Operational source:** Laravel scheduler + database queue.

**Browser:** `resources/scripts/admin/pages/calendar.js`, `pages/calendar/index.js`.

**Осторожно:** scheduled job должен быть idempotent. После deploy long-running worker перезапускается на новом коде.

## 11. Админские transactional views

`DashboardService`, `DashboardController`, `TransactionController`, `UserController` и соответствующие JS entries формируют operational surface для администратора.

При добавлении новой бизнес-фичи dashboard должен показывать только operationally useful состояние: ошибки, ожидающие действия, failed jobs, ближайшие scheduled publications. Не превращать dashboard в дорогостоящую аналитику, если это не требуется продуктом.

## 12. SEO и presentation metadata

**Application:** `app/Services/Admin/Seo/SeoService.php` + `SeoRepository`.

**Data:** SEO-related model/table from canonical schema.

**UI:** page-specific Blade and forms.

Изменения SEO должны оставаться отдельным concern и не смешиваться с core persistence продукта/статьи.

## 13. Где начинать изменение фичи

| Задача | Первая точка | Затем проверять |
|---|---|---|
| Новое поле формы | соответствующий `Http/Requests/*` | model → repository/service → migration → JS |
| Новое бизнес-правило | `Services/*` | repository → tests → controller |
| Новый admin page | controller + Blade page | `resources/scripts/admin/pages/*` → route |
| Новый shared UI | `resources/web/components/ui` | JS component → all consumers → checksum |
| Новый upload/media type | `FileService` / content media service | disk rules → observer → tests |
| Цена/оплата | `PaymentService` | PaymentIntent → webhook → fulfillment → constraints |
| Публикация по расписанию | `PublicationService` | scheduler → job → failed jobs → calendar |
| Изменение схемы | `database/migrations/0001_01_01_000000_create_likablog_schema.php` | models → repositories → tests |
| Большое reference field | typeahead component | request validation → repository → JS |

## 14. Source of truth

При конфликте документов ориентироваться в таком порядке:

1. текущий production code + tests;
2. `docs/architecture/*`;
3. `docs/local-development.md` и `docs/production/reg-ru.md`;
4. `documentation/*` — только как исторический контекст.

`documentation/` не должен незаметно переопределять актуальный production contract.
