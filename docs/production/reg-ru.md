# LIKABLOG.RU — production на REG.RU

Ниже приведена последовательность именно для существующего Laravel-приложения. Не переносите `.env` из разработки в production и не запускайте `migrate:fresh` на боевой базе.

## 0. Перед стартом: определить тип услуги REG.RU

REG.RU рекомендует сначала проверить требования приложения и способ размещения; для виртуального хостинга управление сайтами выполняется через ispmanager. Доступ по SSH есть на виртуальном хостинге, кроме тарифа «Хост-Лайт». Пароли для SSH/FTP/MySQL находятся в личном кабинете REG.RU. [Размещение готового сайта у REG.RU](https://help.reg.ru/support/hosting/razmeshcheniye-sayta-otobrazheniye-v-brauzere/kak-razmestit-gotovyy-sayt-v-internete)

Перед первым deploy сделайте две резервные копии:

- дамп MySQL;
- каталог `storage/app/public` и приватное хранилище файлов.

## 1. PHP и расширения

Для проекта нужен PHP 8.3+, а на вашем хостинге можно выбрать PHP 8.5. В cron/CLI на REG.RU версия PHP вызывается через путь вида `/opt/php/8.5/bin/php`; актуальную версию выбирайте в панели хостинга. [Планировщик CRON REG.RU](https://help.reg.ru/support/hosting/php-asp-net-i-skripty/planirovshchik-cron)

Проверьте минимум:

```bash
php -v
php -m
```

Нужны `mbstring`, `openssl`, `pdo_mysql`, `fileinfo`, `ctype`, `curl`, `json`, `tokenizer`, `xml`.

Путь для команд проекта удобно зафиксировать:

```bash
PHP=/opt/php/8.5/bin/php
```

Если панель использует другой фактический путь, используйте путь из настроек конкретного аккаунта.

## 2. Домен и document root

Для сайта `likablog.ru` добавьте именно домен `likablog.ru`, а не только `www.likablog.ru`; DNS для обычного хостинга REG.RU использует `ns1.hosting.reg.ru` и `ns2.hosting.reg.ru`. [DNS для хостинга REG.RU](https://help.reg.ru/support/dns-servery-i-nastroyka-zony/obshchaya-informatsiya-o-dns-serverakh/kakiye-dns-servery-propisat-dlya-domena)

Document root должен указывать на:

```text
/path/to/likablog.ru/public
```

Нельзя публиковать наружу `app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `.env`, `storage/app/`.

Если серверная конфигурация использует Apache, `.htaccess` является допустимым механизмом настройки web-root/redirects на виртуальном хостинге REG.RU. Перед изменениями сохраняйте резервную копию конфигурации. [REG.RU: .htaccess](https://help.reg.ru/support/hosting/fayly-web-config-i-htaccess/kak-sozdat-fayl-htaccess)

## 3. Загрузка файлов

Рекомендуемый вариант — SSH/SFTP. REG.RU документирует SFTP/SSH для виртуального хостинга; для Windows достаточно PowerShell + `ssh`. [Работа по SSH на виртуальном хостинге REG.RU](https://help.reg.ru/support/hosting/dostupy-i-podklyucheniye-panel-upravleniya-ftp-ssh/rabota-po-ssh-na-virtualnom-hostinge)

Загрузите весь проект, кроме локальных секретов и локальных артефактов разработки.

Не загружайте:

```text
.env
vendor/
node_modules/
storage/logs/*.log
local/ssl/
```

`vendor/` можно либо собрать непосредственно на сервере Composer'ом, либо загрузить с подготовленного build-окружения только при полном совпадении PHP/OS.

## 4. Bun/Vite

В репозитории зафиксирован `bun.lock`. Production-сборка frontend должна выполняться через Bun:

```bash
bun install --frozen-lockfile
bun run build
```

Но текущая публичная справка REG.RU, использованная для этой инструкции, не подтверждает наличие Bun как гарантированно доступного системного runtime на каждом тарифе. Поэтому сначала:

```bash
bun --version
```

Если Bun доступен по SSH — собирайте на хостинге.

Если Bun недоступен — соберите `public/build` локально или в CI на фиксированном Bun runtime и загрузите **только готовый `public/build`** вместе с исходниками. Не заменяйте проектный package manager на npm/PNPM ради production: `bun.lock` должен оставаться источником воспроизводимой frontend-установки.

## 5. Composer и Laravel bootstrap

В корне проекта:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
cp .env.example .env
# Только для новой установки, если APP_KEY ещё пуст.
$PHP artisan key:generate
# На существующем проекте с уже заданным APP_KEY эту команду НЕ выполнять.
```

Заполните `.env` реальными данными MySQL, mail, storage, legal и YooKassa. Никогда не помещайте эти значения в ZIP или git.

После заполнения `.env`:

```bash
$PHP artisan migrate:status
$PHP artisan migrate --force
$PHP artisan storage:link
$PHP artisan optimize
```

На существующей production БД используйте только `migrate --force`. `migrate:fresh` предназначен для чистой тестовой БД.

## 6. Создание администратора

Не используйте ID=1 и не переносите development password.

Создайте администратора через штатную команду проекта:

```bash
$PHP artisan likablog:admin \
  --email=admin@likablog.ru \
  --name="Администратор" \
  --password='СЛОЖНЫЙ_УНИКАЛЬНЫЙ_ПАРОЛЬ'
```

Пароль не попадает в исходники и не должен появляться в shell history надолго. После первого входа сохраните пароль в менеджере секретов.

Для полностью автоматизированного initial seed используйте только закрытое окружение и:

```dotenv
SEED_ADMIN=true
ADMIN_EMAIL=...
ADMIN_PASSWORD=...
ADMIN_NAME=...
```

После создания администратора уберите `SEED_ADMIN=true`.

## 7. MySQL

Создайте отдельную production БД/пользователя и минимальные права, необходимые приложению. Доступы MySQL отображаются в разделе доступов услуги REG.RU. [Доступы к MySQL/SSH/FTP у REG.RU](https://help.reg.ru/support/hosting/dostupy-i-podklyucheniye-panel-upravleniya-ftp-ssh/paroli-dlya-dostupa-k-hostingu-serveru-ftp-i-mysql)

После `migrate --force` проверьте:

```bash
$PHP artisan migrate:status
```

Перед миграцией на боевой БД обязательно сохраните backup.

## 8. Filesystem и media

Для public media:

```bash
$PHP artisan storage:link
```

Постоянные каталоги контента имеют предсказуемую структуру:

```text
teachers/{public_id}
products/{public_id}
articles/{public_id}
reference/{public_id}
recipes/{public_id}
courses/{public_id}
```

Inline media RichTextarea находится внутри каталога владельца. Замена файла удаляет старый файл только после commit. Soft delete не удаляет media. Force delete удаляет каталог. Ежедневный scanner по умолчанию только сообщает об orphan-каталогах; удаление требует явного `--delete`.

Для digital product-файлов используется отдельный private disk. Проверка скачивания должна проходить только через авторизованный endpoint, а не через публичный URL `storage`.

## 9. Queue

Основной production-контур использует Laravel database queue:

```dotenv
QUEUE_CONNECTION=database
```

Запустите постоянный worker в том механизме, который предоставляет ваш тариф/панель:

```bash
$PHP artisan queue:work --tries=3 --timeout=120
```

После deploy worker необходимо перезапустить, чтобы он получил новый код.

Если тариф не даёт постоянный worker, временный fallback — запуск `queue:work --stop-when-empty --tries=3` по cron с малым интервалом. Это менее производительный режим; для платежей и большой очереди предпочтителен настоящий долгоживущий worker.

Контроль:

```bash
$PHP artisan queue:failed
```

## 10. Scheduler

План публикации требует проверки каждую минуту. В планировщике REG.RU поддерживается запуск cron с минутным интервалом. [Планировщик CRON REG.RU](https://help.reg.ru/support/hosting/php-asp-net-i-skripty/planirovshchik-cron)

Задание:

```cron
* * * * * /opt/php/8.5/bin/php /PATH/TO/likablog.ru/artisan schedule:run >> /PATH/TO/likablog.ru/storage/logs/scheduler.log 2>&1
```

В приложении scheduler запускает:

- отложенную публикацию статей/рецептов/курсов;
- очистку временных upload-сессий;
- проверку пользовательской grace-period;
- ежедневный orphan-media scan.

## 11. YooKassa

В `.env`:

```dotenv
YOOKASSA_SHOP_ID=...
YOOKASSA_SECRET_KEY=...
YOOKASSA_RECEIPT_ENABLED=false
YOOKASSA_VAT_CODE=
YOOKASSA_PAYMENT_SUBJECT=service
YOOKASSA_PAYMENT_MODE=full_payment
YOOKASSA_WEBHOOK_IP_VALIDATION=true
```

Webhook:

```text
https://likablog.ru/payments/yookassa/webhook
```

После включения проверьте реальный sandbox/test payment:

```text
витрина → карточка → checkout → YooKassa → возврат → webhook → transaction → purchase/enrollment → доступ
```

Повтор webhook не должен создавать вторую транзакцию или второй доступ.

Клиент использует `Idempotence-Key`; приложение сохраняет idempotence key для payment intent и повторно использует незавершённый intent, что предотвращает появление нескольких платежей при повторной отправке одной операции.

Receipt включайте только после согласования фискальных параметров: `YOOKASSA_VAT_CODE`, `YOOKASSA_PAYMENT_SUBJECT`, `YOOKASSA_PAYMENT_MODE` и требований вашей кассы/ОФД.

## 12. Первый production smoke test

Выполняйте в таком порядке:

1. открыть главную и каталог;
2. войти администратором;
3. создать категорию → создать товар → заполнить цену → загрузить private file → сохранить;
4. открыть товар публично → увидеть понятное ценностное описание → перейти к покупке;
5. пройти test payment → проверить transaction + access;
6. вернуть доступный курс → проверить checkout → enrollment → `/courses/{course}/learn`;
7. в админке создать статью → RichTextarea + inline image → сохранить → перезагрузить → убедиться, что картинка не base64;
8. создать scheduled article/recipe/course → проверить календарь → дождаться scheduler;
9. изменить cover/file → проверить, что старый файл удаляется после commit;
10. soft delete → файл остаётся → restore → запись возвращается;
11. force delete в тестовой записи → каталог очищается;
12. открыть Dashboard → убедиться, что нет тяжёлого N+1 на обычной загрузке.

## 13. Rollback

Перед release:

```bash
$PHP artisan down --render="errors::503" --retry=60
```

Сохраните DB dump + media backup. После rollback кода не запускайте обратные миграции вслепую: откат схемы должен соответствовать версии базы и backup.

После deploy:

```bash
$PHP artisan up
$PHP artisan optimize:clear
$PHP artisan optimize
```

Затем повторите smoke test.

## 14. Что обязательно проверить на реальном REG.RU до объявления «production-ready»

Локальная среда этой поставки не содержит Composer/Bun/vendor/node_modules и не имеет внешнего сетевого доступа, поэтому финальный runtime build/миграции/YooKassa HTTP exchange в ней не исполнены. Статический QA и syntax checks выполнены; ниже остаются только environment-dependent проверки:

```bash
composer install --no-dev
bun install --frozen-lockfile
bun run build
$PHP artisan migrate:fresh --seed   # только отдельная staging/test DB
$PHP artisan test
$PHP artisan optimize
```

Production объявляйте готовым только после staging smoke на том же PHP 8.5 + MySQL + storage + queue + cron окружении.
