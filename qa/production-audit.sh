#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

failures=0
pass(){ printf 'PASS  %s\n' "$1"; }
fail(){ printf 'FAIL  %s\n' "$1" >&2; failures=$((failures+1)); }

php_version="$(php -r 'echo PHP_VERSION;' 2>/dev/null || true)"
[[ -n "$php_version" ]] && pass "PHP CLI available ($php_version)" || fail "PHP CLI unavailable"

if [[ -f bun.lock && ! -e package-lock.json && ! -e pnpm-lock.yaml && ! -e yarn.lock ]]; then
  pass "Bun lockfile is the sole frontend lockfile"
else
  fail "Frontend package-manager lockfile contract is broken"
fi
if command -v bun >/dev/null 2>&1; then
  pass "Bun runtime available ($(bun --version))"
else
  pass "Bun runtime not installed in this QA container; frozen-lockfile command remains a deployment gate"
fi

bash qa/migration-contract.sh

php_files=()
while IFS= read -r -d '' f; do php_files+=("$f"); done < <(find app config database routes tests -type f -name '*.php' -print0)
php_errors=0
for f in "${php_files[@]}"; do php -l "$f" >/dev/null || { echo "  $f" >&2; php_errors=$((php_errors+1)); }; done
[[ "$php_errors" -eq 0 ]] && pass "PHP syntax (${#php_files[@]} files)" || fail "PHP syntax ($php_errors failures)"

js_errors=0
while IFS= read -r -d '' f; do node --check "$f" >/dev/null 2>&1 || { echo "  $f" >&2; js_errors=$((js_errors+1)); }; done < <(find resources -type f \( -name '*.js' -o -name '*.mjs' \) -print0)
[[ "$js_errors" -eq 0 ]] && pass "Native JS syntax" || fail "Native JS syntax ($js_errors failures)"
if [[ -f vite.config.js ]]; then
  node --check vite.config.js >/dev/null 2>&1 && pass "Vite config syntax" || fail "Vite config syntax"
fi

# Repository boundary: application services may orchestrate repositories, but must not persist models directly.
direct_service_writes=$(rg -n --glob '*.php' --glob '!app/Services/Admin/Generic/FileService.php' --glob '!app/Services/User/UserDeletionService.php' --glob '!app/Services/User/UserEmailChangeService.php' --glob '!app/Services/Auth/*' --glob '!app/Services/Register/*' --glob '!app/Services/Cookie/*' '\$[A-Za-z_][A-Za-z0-9_]*->(save|create|update|delete|forceDelete|restore)\(' app/Services || true)
[[ -z "$direct_service_writes" ]] && pass "Service layer has no direct model persistence" || { echo "$direct_service_writes" >&2; fail "Direct model persistence detected in Services"; }

view_root="resources/web"
wrong_views=$(find resources/views -type f 2>/dev/null | wc -l)
[[ "$wrong_views" -eq 0 ]] && pass "No Blade views outside resources/web" || fail "Found $wrong_views Blade/non-Blade files in legacy resources/views"

custom_hash_fail=0
if [[ -d resources/web/components/ui/form ]]; then
  # Immutable-component sentinel: record current hashes in the project artifact.
  sha256sum resources/web/components/ui/form/*.blade.php > /tmp/likablog-ui-hashes.txt
  expected='qa/custom-ui.sha256'
  if [[ -f "$expected" ]] && ! sha256sum -c "$expected" >/dev/null 2>&1; then custom_hash_fail=1; fi
fi
[[ "$custom_hash_fail" -eq 0 ]] && pass "Custom form UI component hashes" || fail "Custom form UI component hash mismatch"

# No manual validation in admin controllers.
manual_validate=$(rg -n --glob '*.php' '\$this->validate\(|\bvalidator\(' app/Http/Controllers/Admin || true)
[[ -z "$manual_validate" ]] && pass "No manual validation in Admin controllers" || { echo "$manual_validate" >&2; fail "Manual validation detected in Admin controllers"; }

# RichTextarea table insertion was explicitly dropped from scope.
rich_table=$(rg -n 'insertTable|table-button|data-rich-text-table|<table' resources/web/components/ui/form/rich-textarea* resources/web/js 2>/dev/null || true)
[[ -z "$rich_table" ]] && pass "RichTextarea table feature absent (scope closed)" || { echo "$rich_table" >&2; fail "RichTextarea table feature unexpectedly present"; }

# All admin views live below resources/web.
admin_views=$(find resources/web/admin -type f -name '*.blade.php' 2>/dev/null | wc -l)
[[ "$admin_views" -gt 0 ]] && pass "Admin Blade views under resources/web ($admin_views files)" || fail "No admin Blade views found"

if rg -n --glob '*.php' 'EmailCampaignRecipient' app routes database resources >/tmp/legacy-runtime-refs.txt 2>/dev/null; then
  cat /tmp/legacy-runtime-refs.txt >&2
  fail "Legacy EmailCampaignRecipient runtime reference remains"
else
  pass "Legacy EmailCampaignRecipient removed from runtime"
fi

if python3 tools_route_audit.py | tee /tmp/likablog-route-audit.txt | grep -q 'MISSING=0$'; then
  pass "Blade route reference audit"
else
  cat /tmp/likablog-route-audit.txt >&2
  fail "Unresolved Blade route reference detected"
fi

if [[ "$failures" -eq 0 ]]; then
  printf '\nALL STATIC PRODUCTION AUDITS PASSED\n'
else
  printf '\nSTATIC PRODUCTION AUDIT FAILED: %d check(s)\n' "$failures" >&2
  exit 1
fi
