param (
    [switch]$Stop,
    [switch]$Restart
)

# Определяем корень проекта
$projectRoot = Resolve-Path "$PSScriptRoot\..\.."

# --- Блок остановки и очистки процессов ---
if ($Stop -or $Restart) {
    Write-Host "🛑 Остановка всех служб веб-сервера..." -ForegroundColor Red
    Stop-Process -Name "nginx" -ErrorAction SilentlyContinue
    Stop-Process -Name "mailpit" -ErrorAction SilentlyContinue
    Stop-Process -Name "php" -Force -ErrorAction SilentlyContinue
    Stop-Process -Name "node" -Force -ErrorAction SilentlyContinue
    Write-Host "✔ Все процессы успешно остановлены." -ForegroundColor Green
    if ($Stop) { Exit 0 }
}

# --- 1. Проверка и запуск MySQL ---
Write-Host "[0/6] Проверка инструментов..." -ForegroundColor Cyan
foreach ($tool in @('php', 'bun')) {
    if (-not (Get-Command $tool -ErrorAction SilentlyContinue)) {
        throw "Не найден инструмент '$tool'. Установите его и повторите запуск."
    }
}
Write-Host "✔ PHP и Bun доступны." -ForegroundColor Green

Write-Host "[1/6] Проверка службы MySQL80..." -ForegroundColor Cyan
$mysql = Get-Service -Name "MySQL80" -ErrorAction SilentlyContinue
if ($mysql.Status -ne 'Running') {
    Start-Process pwsh -ArgumentList "-Command Start-Service MySQL80" -Verb RunAs -Wait
}
Write-Host "✔ MySQL успешно запущен." -ForegroundColor Green

# Переходим в корень проекта
Set-Location $projectRoot

# --- Запуск ОДНОГО окна Windows Terminal со ВКЛАДКАМИ ---
Write-Host "[2-5/6] Запуск Laravel, Vite, Очередей и Mailpit в одном окне..." -ForegroundColor Cyan

# Собираем железобетонную строку аргументов для wt.exe с правильным экранированием кавычек
$wtCommand = "new-tab -d `"$projectRoot`" --title `"Laravel Serve`" pwsh -NoExit -Command `"php artisan serve --port=8000`" ; " +
              "new-tab -d `"$projectRoot`" --title `"Vite Dev`" pwsh -NoExit -Command `"bun run dev -- --host`" ; " +
              "new-tab -d `"$projectRoot`" --title `"Laravel Queue`" pwsh -NoExit -Command `"php artisan queue:work`" ; " +
              "new-tab -d `"$projectRoot`" --title `"Mailpit`" pwsh -NoExit -Command `"mailpit`""

# Запускаем Терминал Windows с единой строкой параметров
Start-Process wt -ArgumentList $wtCommand

Start-Sleep -Seconds 1

# --- 6. Запуск Nginx ---
Write-Host "[6/6] Запуск Nginx..." -ForegroundColor Cyan
if (Test-Path "C:\Program Files\nginx-1.29.2\nginx.exe") {
    Set-Location "C:\Program Files\nginx-1.29.2\"
    Start-Process .\nginx.exe
    Set-Location $projectRoot
}

Write-Host "`n🚀 Проект запущен и доступен в локальной сети!" -ForegroundColor Green
Write-Host "🔗 URL Laravel: http://127.0.0.1:8000/" -ForegroundColor Magenta
Write-Host "🔗 URL Vite:    http://127.0.0.1:5173/" -ForegroundColor Magenta
Write-Host "ℹ️ HTTPS/Nginx — отдельный профиль; см. docs/local-development.md" -ForegroundColor DarkGray
