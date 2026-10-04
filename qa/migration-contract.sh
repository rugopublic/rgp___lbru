#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

python3 - <<'PY'
from pathlib import Path
import re

migrations = sorted(Path('database/migrations').glob('*.php'))
assert len(migrations) == 1, f"expected exactly one canonical migration, got {len(migrations)}"
text = migrations[0].read_text()

for forbidden in [
    "courses_old",
    "seo_data",
    "rename_courses_table_to_courses_old_table",
    "expand_course_rich_text_fields",
]:
    assert forbidden not in text, f"legacy migration artifact remains: {forbidden}"

required_tables = [
    'users', 'courses', 'teachers', 'products', 'articles', 'recipes', 'ingredients',
    'course_enrollments', 'product_purchases', 'payment_intents', 'transactions',
    'seos', 'groups', 'group_user', 'recipe_ingredient_groups', 'course_teacher',
]
for table in required_tables:
    assert f"Schema::create('{table}'" in text, f"missing canonical table: {table}"

# Payment invariants belong to the database too, not only to service code.
assert "string('idempotence_key', 64)->nullable()->unique()" in text
assert "unique('provider_payment_id', 'payment_intents_provider_payment_id_unique')" in text
assert "foreignId('payment_intent_id')->nullable()->unique()->constrained('payment_intents')" in text
assert "unique(['user_id', 'product_id'])" in text
assert "unique(['user_id', 'course_id'])" in text

# Current rich-text/course contract must be representable on a fresh DB.
assert "string('cover', 127)->nullable()" in text
assert "text('objective')->nullable()" in text
assert "text('skills')->nullable()" in text
assert "enum('publish_status', ['draft', 'scheduled', 'published'])" in text

# No production course schema may contain the old field contract.
for obsolete in ['level', 'short_description', 'full_description', 'promise', 'who_for', 'result', 'unique_value', 'requirements']:
    assert f"'{obsolete}'" not in text, f"obsolete course column survived in canonical schema: {obsolete}"

print('PASS canonical migration contract')
PY
