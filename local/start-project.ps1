param(
    [switch]$Stop,
    [switch]$Restart
)

$ErrorActionPreference = 'Stop'
$projectRoot = Resolve-Path "$PSScriptRoot\.."
$runner = Join-Path $projectRoot 'local/tasks/run-server.ps1'

if (-not (Test-Path $runner)) {
    throw "Development runner not found: $runner"
}

Write-Host "LIKABLOG.RU development launcher" -ForegroundColor Green
Write-Host "Project: $projectRoot" -ForegroundColor DarkGray
Write-Host ""

& $runner @PSBoundParameters
