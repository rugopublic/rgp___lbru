#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"
STAMP="$(date +%Y%m%d-%H%M%S)"
OUT="$ROOT/qa/runs/$STAMP"
mkdir -p "$OUT"

run_pass() {
  local n="$1"
  local focus="$2"
  local command="$3"
  local log="$OUT/pass-${n}.log"
  {
    echo "LIKABLOG.RU RELEASE QA — PASS ${n}/8"
    echo "Focus: ${focus}"
    bash -lc "$command"
    echo "PASS ${n}/8 complete"
  } | tee "$log"
}

run_pass 1 "baseline-and-final-static-gate" './qa/production-audit.sh'

run_pass 2 "PHP-syntax" '
  set -e
  count=$(find app config database routes tests -type f -name "*.php" | wc -l)
  if find app config database routes tests -type f -name "*.php" -print0 | xargs -0 -r -n1 php -l >/dev/null 2>&1; then
    echo "PASS PHP syntax (${count} files)"
  else
    echo "FAIL PHP syntax" >&2
    exit 1
  fi
'

run_pass 3 "native-JS-Vite-and-Bun-contract" '
  set -e
  find resources -type f \( -name "*.js" -o -name "*.mjs" \) -print0 | xargs -0 -r -n1 node --check >/dev/null
  node --check vite.config.js >/dev/null
  [[ -f bun.lock ]]
  [[ ! -e package-lock.json && ! -e pnpm-lock.yaml && ! -e yarn.lock ]]
  echo "PASS Native JS + Vite syntax + Bun lockfile contract"
'

run_pass 4 "repository-controller-service-boundaries" '
  set -e
  if rg -n --glob "*.php" --glob "!app/Services/Admin/Generic/FileService.php" --glob "!app/Services/User/UserDeletionService.php" --glob "!app/Services/User/UserEmailChangeService.php" --glob "!app/Services/Auth/*" --glob "!app/Services/Register/*" --glob "!app/Services/Cookie/*" "\$[A-Za-z_][A-Za-z0-9_]*->(save|create|update|delete|forceDelete|restore)\(" app/Services; then exit 1; fi
  if rg -n --glob "*.php" "\$this->validate\(|\bvalidator\(" app/Http/Controllers/Admin; then exit 1; fi
  echo "PASS repository/service/controller boundaries"
'

run_pass 5 "views-routes-and-component-contract" '
  set -e
  [[ $(find resources/views -type f 2>/dev/null | wc -l) -eq 0 ]]
  [[ $(find resources/web/admin -type f -name "*.blade.php" | wc -l) -gt 0 ]]
  python3 tools_route_audit.py | tee /tmp/likablog-route-audit-pass5.txt
  grep -q "MISSING=0$" /tmp/likablog-route-audit-pass5.txt
  sha256sum -c qa/custom-ui.sha256 >/dev/null
  echo "PASS view placement + routes + immutable custom UI"
'

run_pass 6 "RichTextarea-sales-and-frontend-contract" '
  set -e
  ! rg -n "insertTable|table-button|data-rich-text-table" resources/web/components/ui/form/rich-textarea* resources/scripts/components/ui/form/rich-textarea.js 2>/dev/null
  for f in resources/web/frontend/catalog/article.blade.php resources/web/frontend/catalog/recipe.blade.php resources/web/frontend/catalog/ingredient.blade.php; do rg -q "frontend.components.related-products" "$f"; done
  rg -q "route\(\x27courses.buy\x27" resources/web/frontend/catalog/course.blade.php
  rg -q "route\(\x27products.buy\x27" resources/web/frontend/catalog/product.blade.php
  rg -q "route\x27\?" /dev/null || true
  echo "PASS RichTextarea scope + selling surfaces"
'

run_pass 7 "payments-scheduler-and-media" '
  set -e
  rg -q "Idempotence-Key" app/Services/Public/YooKassaClient.php
  grep -Fq "getPayment(\$paymentId)" app/Http/Controllers/Public/PaymentController.php
  rg -q "lockForUpdate" app/Repositories/Eloquent/PaymentIntentRepository.php
  grep -Fq "PublishScheduledContentCommand::class)->everyMinute" routes/console.php
  rg -q "ScanOrphanMediaCommand::class" routes/console.php
  rg -q -- "--delete" app/Console/Commands/ScanOrphanMedia.php
  [[ ! -e public/hot ]]
  echo "PASS payment + scheduler + media + production artifact checks"
'

run_pass 8 "release-recheck" './qa/production-audit.sh'

echo "8-PASS QA COMPLETE"
echo "Logs: $OUT"
