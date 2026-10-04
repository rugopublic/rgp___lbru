<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Segments the provided UUID into parts.
 *
 * @author Ruslan Gontar <rugomath@gmail.com>
 *
 * @since 1.0
 *
 * @param string $source The UUID to segment.
 *
 * @return Collection<string> The first characters from each UUID hyphen segment.
 *
 * @throws InvalidArgumentException If the provided ID is not a valid UUID.
 */
if(!function_exists('segmentUuid')) {

    /**
     * Handles segment uuid for the helpers.
     *
     * @param string $source
     * @return Collection
     */
    function segmentUuid(string $source): Collection
    {
        $uuid = Str::of($source);

        if(!$uuid->isUuid()) {
            throw new InvalidArgumentException("The source string must be a valid UUID.");
        }

        return $uuid->split('/-/')
                    ->map(fn (string $part) => $part[0]);
    }
}


if (!function_exists('trans_xchoice')) {

    /**
     * Handles trans xchoice for the helpers.
     *
     * @param string $key
     * @param int $count
     * @param array $replace
     * @param ?string $locale
     * @return string
     */
    function trans_xchoice(string $key, int $count, array $replace = [], ?string $locale = null): string
    {
        if (Arr::exists($replace, 'count')) {
            // Replace everything except :count. Warn in log if we have :count in $replace array.
            Log::warning("[trans_xchoice] Ignoring 'count' in replace array", [
                'key' => $key,
                'count_value' => $replace['count']
            ]);
        }

        $needles = Arr::except($replace, 'count');
        $pattern = __($key, $needles, $locale);

        if(!Str::contains($pattern, '[...')) {
            return trans_choice($key, $count, $replace, $locale);
        }

        $pattern = Str::replace(':count', (string) $count, $pattern);

        $parts = Str::of($pattern)->split('/\s*\|\s*/');
        $fallback = $parts->last();

        foreach ($parts as $part) {
            if(preg_match('/^\[\s*\.\.\.\s*(\d+(?:\s*-\s*\d+)?)\s*]\s*(.+)$/', $part, $m)) {
                // Log::debug("Regex matched: ", $m);
                $range = Str::of($m[1]);
                $text = Str::trim($m[2]);

                if($range->contains('-')) {
                    $bounds = $range->split('/-/')
                        ->transform(fn ($item) => ((int) $item))
                        ->sort();

                    $mods = $bounds->map(
                        fn ($item) => ( $item == 0 ? 1 : floor(log10($item) + 1) )
                    );

                    // Take into account only upper bound as the greatest one.
                    $lastDigits = $count % pow(10, $mods[1]);

                    $conditionResult = ($bounds[0] <= $lastDigits) && ($lastDigits <= $bounds[1]);

                    if($conditionResult) {
                        return $text;
                    }
                } else {
                    $value = $range->toInteger();

                    $mod = ( $value == 0 ? 1 : floor(log10($value) + 1) );

                    $lastDigits = $count % pow(10, $mod);

                    if($lastDigits == $value) {
                        return $text;
                    }
                }
            } else {
                $fallback = Str::trim($part);
            }
        }

        return $fallback;
    }
}

if (!function_exists('random_public_id')) {

    /**
     * Handles random public id for the helpers.
     *
     * @param int $length
     * @return string
     */
    function random_public_id(int $length = 28): string
    {
        return Str::random($length);
    }
}

if (!function_exists('unique_slug')) {

    /**
     * Handles unique slug for the helpers.
     *
     * @param string $value
     * @param string $table
     * @param ?int $ignoreId
     * @return string
     */
    function unique_slug(string $value, string $table, ?int $ignoreId = null): string
    {
        $base = Str::slug(Str::transliterate(trim($value)));
        $base = $base !== '' ? $base : 'item';
        $slug = $base;
        $suffix = 1;

        while (DB::table($table)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
