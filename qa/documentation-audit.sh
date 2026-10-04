#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

php <<'PHP'
<?php
declare(strict_types=1);

$root = realpath(__DIR__);
$phpFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    if (str_contains($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
        continue;
    }
    $phpFiles[] = $path;
}

$total = 0;
$missing = [];
$modifierTokens = array_filter([
    defined('T_PUBLIC') ? T_PUBLIC : null,
    defined('T_PROTECTED') ? T_PROTECTED : null,
    defined('T_PRIVATE') ? T_PRIVATE : null,
    defined('T_STATIC') ? T_STATIC : null,
    defined('T_FINAL') ? T_FINAL : null,
    defined('T_ABSTRACT') ? T_ABSTRACT : null,
    defined('T_READONLY') ? T_READONLY : null,
]);

foreach ($phpFiles as $path) {
    $tokens = token_get_all(file_get_contents($path));
    $count = count($tokens);
    for ($i = 0; $i < $count; $i++) {
        $token = $tokens[$i];
        if (!is_array($token) || $token[0] !== T_FUNCTION) {
            continue;
        }

        $j = $i + 1;
        while ($j < $count) {
            $next = $tokens[$j];
            $id = is_array($next) ? $next[0] : null;
            $text = is_array($next) ? $next[1] : $next;
            if ($id === T_WHITESPACE || $id === T_COMMENT || $text === '&') {
                $j++;
                continue;
            }
            break;
        }
        if ($j >= $count || !is_array($tokens[$j]) || $tokens[$j][0] !== T_STRING) {
            // Anonymous closure: not a named function/method and therefore outside this contract.
            continue;
        }

        $total++;
        $name = $tokens[$j][1];

        // Walk backward through the declaration's modifiers and optional attribute group.
        $k = $i - 1;
        $attributeDepth = 0;
        while ($k >= 0) {
            $previous = $tokens[$k];
            $id = is_array($previous) ? $previous[0] : null;
            $text = is_array($previous) ? $previous[1] : $previous;

            if ($id === T_WHITESPACE || $id === T_COMMENT) {
                $k--;
                continue;
            }
            if (in_array($id, $modifierTokens, true)) {
                $k--;
                continue;
            }
            if ($text === ']') {
                $attributeDepth = 1;
                $k--;
                while ($k >= 0 && $attributeDepth > 0) {
                    $part = $tokens[$k];
                    $partText = is_array($part) ? $part[1] : $part;
                    if ($partText === ']') {
                        $attributeDepth++;
                    } elseif ($partText === '[' || $partText === '#[' || (is_array($part) && defined('T_ATTRIBUTE') && $part[0] === T_ATTRIBUTE)) {
                        $attributeDepth--;
                    }
                    $k--;
                }
                continue;
            }
            break;
        }

        while ($k >= 0 && is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) {
            $k--;
        }

        $hasDoc = $k >= 0 && is_array($tokens[$k]) && $tokens[$k][0] === T_DOC_COMMENT;
        if (!$hasDoc) {
            $missing[] = sprintf('%s:%d %s()', $path, $token[2], $name);
        }
    }
}

if ($missing) {
    fwrite(STDERR, "Missing PHPDoc:\n" . implode("\n", array_map(static fn($item) => "  $item", $missing)) . "\n");
    exit(1);
}

echo "PHPDoc coverage PASS: {$total} named PHP functions/methods.\n";
PHP

python3 - <<'PY'
from pathlib import Path
root=Path('.')
js=[p for p in root.rglob('*.js') if 'node_modules' not in p.parts and 'build' not in p.parts]
missing=[]
for p in js:
    lines=p.read_text(encoding='utf-8',errors='ignore').lstrip('\ufeff').splitlines()
    i=0
    while i < len(lines) and not lines[i].strip(): i += 1
    if i >= len(lines) or not lines[i].lstrip().startswith('/**'):
        missing.append(str(p))
if missing:
    print('Missing JS module JSDoc:')
    print('\n'.join('  '+x for x in missing))
    raise SystemExit(1)
print(f'JSDoc coverage PASS: {len(js)} JavaScript modules.')
PY
