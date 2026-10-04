# LIKABLOG.RU — локальная разработка

Этот документ — **единственная актуальная инструкция по запуску проекта локально**.
Он рассчитан на Windows + PowerShell и на текущую структуру проекта: Laravel 13, PHP 8.3+, MySQL, Bun, Vite 8.

Старую документацию по Nginx/HTTPS в `documentation/02. HTTPS + nginx with Laravel + Vite.md` считать исторической. Она оставлена ради контекста, но команды `npm` и старый `vite.config.js` оттуда использовать не надо.

## 1. Что должно быть установлено

Нужны:

- PHP 8.3 или новее;
- Composer;
- MySQL 8.x или совместимый MySQL-сервер;
- Bun;
- PowerShell 7 рекомендуется;
- Git.

Для обычной разработки **Nginx, mkcert и Mailpit не обязательны**. Базовый профиль работает через `php artisan serve` и HTTP-Vite.

Проверка:

```powershell
php -v
composer --version
bun --version
mysql --version
git --version
```

Bun является единственным frontend package manager этого проекта. Не заменяйте `bun.lock` на `package-lock.json`, `pnpm-lock.yaml` или `yarn.lock`.

## 2. Получить проект

Перейдите в каталог проекта и установите зависимости:

```powershell
cd D:\Dev\Work\likablog.ru
composer install
bun install --frozen-lockfile
```

`composer install` создаёт `vendor/`, а `bun install --frozen-lockfile` устанавливает ровно то, что зафиксировано в `bun.lock`.

## 3. Создать локальную БД

Проект по умолчанию использует MySQL.

Зайдите в MySQL пользователем с правами на создание БД:

```sql
CREATE DATABASE likablog
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

При необходимости создайте отдельного пользователя:

```sql
CREATE USER 'likablog'@'localhost' IDENTIFIED BY 'change-this-local-password';
GRANT ALL PRIVILEGES ON likablog.* TO 'likablog'@'localhost';
FLUSH PRIVILEGES;
```

Для существующего локального MySQL-пользователя этот шаг можно пропустить.

## 4. Создать `.env`

Скопируйте шаблон:

```powershell
Copy-Item .env.example .env
```

Для стандартного HTTP-профиля установите:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

VITE_DEV_SERVER_URL=http://127.0.0.1:5173
VITE_HOST=127.0.0.1
VITE_PORT=5173
VITE_HTTPS=false
VITE_HMR_HOST=127.0.0.1
VITE_HMR_PORT=5173
VITE_HMR_PROTOCOL=ws

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=likablog
DB_USERNAME=likablog
DB_PASSWORD=change-this-local-password

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

MAIL_MAILER=log
```

На локальной машине YooKassa можно оставить ненастроенной. Для обычного запуска сайта реальные production-секреты не нужны.

## 5. Сгенерировать APP_KEY

Выполните один раз после создания `.env`:

```powershell
php artisan key:generate
```

Проверить:

```powershell
php artisan about
```

В выводе должны быть корректные environment/application settings.

## 6. Создать чистую схему

Этот архив содержит **одну canonical migration**, которая описывает БД сразу в конечном состоянии.

Для новой локальной БД:

```powershell
php artisan migrate
```

Для полностью пересоздания локальной БД при разработке:

```powershell
php artisan migrate:fresh
```

`migrate:fresh` допустим только для локальной/staging/test БД. На production его не применять.

## 7. Подключить storage

```powershell
php artisan storage:link
```

Если Windows не позволяет создать ссылку из обычного терминала, повторите команду в терминале с необходимыми правами.

Не храните production media внутри этого рабочего дерева при работе с чистым dev-архивом.

## 8. Администратор для локалки

Самый простой вариант — штатная команда проекта:

```powershell
php artisan likablog:admin `
  --email=admin@likablog.local `
  --name="Администратор" `
  --password="CHANGE_THIS_TO_A_LOCAL_PASSWORD"
```

Используйте локальный пароль, не повторяйте production-пароль.

Альтернатива для автоматизированного local seed:

```dotenv
SEED_ADMIN=true
ADMIN_EMAIL=admin@likablog.local
ADMIN_PASSWORD=CHANGE_THIS_TO_A_LOCAL_PASSWORD
ADMIN_NAME=Администратор
```

После `php artisan db:seed` удалите эти значения из `.env`, если они больше не нужны.

## 9. Собрать frontend один раз

До первого запуска dev-сервера полезно проверить production-style сборку:

```powershell
bun run build
```

После этого можно удалить `public/build`, если нужен исключительно Vite HMR-режим; `bun run dev` будет обслуживать dev-assets отдельно.

## 10. Запустить Laravel

В первом терминале:

```powershell
cd D:\Dev\Work\likablog.ru
php artisan serve --host=127.0.0.1 --port=8000
```

Ожидаемый адрес:

```text
http://127.0.0.1:8000
```

## 11. Запустить Vite через Bun

Во втором терминале:

```powershell
cd D:\Dev\Work\likablog.ru
bun run dev
```

Ожидаемый адрес Vite:

```text
http://127.0.0.1:5173
```

Непосредственно открывать порт Vite в браузере обычно не требуется: Laravel использует его для dev-assets/HMR.

## 12. Запустить очередь

В третьем терминале:

```powershell
cd D:\Dev\Work\likablog.ru
php artisan queue:work --tries=3 --timeout=120
```

Оставляйте worker запущенным, если тестируете фоновые задачи, публикацию, письма или платёжный fulfillment.

## 13. Локальная почта

При `MAIL_MAILER=log` письма не отправляются наружу. Laravel пишет их в лог.

Проверка:

```powershell
Get-Content storage\logs\laravel.log -Wait
```

Mailpit является опциональным локальным инструментом. Если он установлен, его можно запустить отдельно и переключить mailer согласно локальной конфигурации.

## 14. Ежедневный dev-цикл

После первоначальной установки обычный запуск состоит из трёх терминалов:

**Терминал 1**

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

**Терминал 2**

```powershell
bun run dev
```

**Терминал 3**, если нужны очереди:

```powershell
php artisan queue:work --tries=3 --timeout=120
```

После изменения PHP обычно достаточно сохранить файл и обновить страницу. После изменения JS/SCSS Vite отдаёт HMR-обновление.

## 15. Если нужна текущая HTTPS/LAN-схема

В проекте сохранена поддержка HTTPS для Vite и Nginx. Для неё нужен локальный сертификат, созданный именно на вашей машине.

Не переносите `local/ssl/server.key` из чужого архива. Приватный ключ теперь намеренно не хранится в репозитории.

Установите mkcert и создайте локальный сертификат:

```powershell
mkcert -install
mkcert `
  -key-file local/ssl/server.key `
  -cert-file local/ssl/server.crt `
  localhost 127.0.0.1 ::1 192.168.0.159 likablog.ru.test
```

После этого в `.env` можно включить HTTPS-профиль:

```dotenv
APP_URL=https://192.168.0.159:8443
VITE_DEV_SERVER_URL=https://192.168.0.159:5173
VITE_HOST=192.168.0.159
VITE_PORT=5173
VITE_HTTPS=true
VITE_HMR_HOST=192.168.0.159
VITE_HMR_PORT=5173
VITE_HMR_PROTOCOL=wss
```

Для этого профиля Nginx должен проксировать HTTPS `:8443` на Laravel `127.0.0.1:8000`.

## 16. Удобный PowerShell launcher

В проекте есть:

```powershell
.\local\start-project.ps1
```

Это только convenience-обёртка над `local/tasks/run-server.ps1`. Она использует **Bun**, а не npm.

Если запускать всё автоматически не требуется, ручной запуск из раздела 14 остаётся самым прозрачным вариантом.

## 17. Перед началом работы — быстрая проверка проекта

```powershell
php artisan about
php artisan migrate:status
bun run build
bash qa/production-audit.sh
```

Для Windows без Git Bash последний скрипт можно запускать из Git Bash/WSL.

Полный статический release QA:

```bash
bash qa/run-8-passes.sh
```

Реальный Laravel test suite:

```powershell
php artisan test
```

## 18. Полный reset локального проекта

Когда нужна именно чистая БД:

```powershell
php artisan migrate:fresh
```

Когда нужно начать вообще с чистого дерева зависимостей:

```powershell
Remove-Item -Recurse -Force vendor -ErrorAction SilentlyContinue
Remove-Item -Recurse -Force node_modules -ErrorAction SilentlyContinue
Remove-Item .env -ErrorAction SilentlyContinue

composer install
bun install --frozen-lockfile
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
```

## 19. Чего не делать

Не выполняйте в локальной разработке `composer update` или замену `bun.lock` без осознанного изменения dependency graph.

Не используйте `npm install` для этого проекта.

Не переносите `.env`, YooKassa secret key, production media или production DB dump в dev-репозиторий.

Не меняйте файлы `resources/web/components/ui` ради локальной косметики: эти компоненты защищены release hash-контрактом.
