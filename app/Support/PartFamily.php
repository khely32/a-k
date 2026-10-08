<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Motorcycle part families - Tire, Spark Plug, Oil Filter, Brake Pad - used
 * by the Master Products "Part" filter.
 *
 * Distinct from ProductCategory's broad groups: a category answers "which
 * system does this belong to" (Engine Parts), a part family answers "what
 * is this part" (Spark Plug). Products get one by matching their name,
 * then their broad category, against the curated `category_sizes` list.
 *
 * The built-in copy of that list keeps resolution working when the table is
 * empty or missing (tests, fresh installs); anything extra the table holds
 * is merged in.
 */
class PartFamily
{
    /**
     * The distinct `category_sizes.category` values the seeded table ships.
     */
    private const SEEDED = [
        'Accessories',
        'Air Filter',
        'Battery',
        'Bearing',
        'Brake Fluid',
        'Brake Pad',
        'Cable',
        'CDI Unit',
        'Chain',
        'Clutch Plate',
        'Coolant',
        'Disc Brake Rotor',
        'Engine Oil',
        'Fork Oil',
        'Fuel Filter',
        'Gasket Set',
        'Gear Oil',
        'Handle Grip',
        'Headlight',
        'Horn',
        'Ignition Coil',
        'Light Bulb',
        'Lubricants',
        'Mirror',
        'Oil Filter',
        'Regulator Rectifier',
        'Rim',
        'Spark Plug',
        'Starter Relay',
        'Tail Light',
        'Tire',
        'Tire Tube',
        'Tools',
    ];

    /**
     * @var list<string>|null
     */
    private static ?array $all = null;

    /**
     * Every known part family, sorted, deduplicated case-insensitively.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        if (static::$all !== null) {
            return static::$all;
        }

        $fromDb = [];

        try {
            if (Schema::hasTable('category_sizes')) {
                $fromDb = DB::table('category_sizes')->distinct()->pluck('category')->all();
            }
        } catch (\Throwable) {
            $fromDb = [];
        }

        $values = array_merge(static::SEEDED, $fromDb);
        $values = array_filter($values, fn ($value) => trim((string) $value) !== '');

        sort($values, SORT_STRING | SORT_FLAG_CASE);

        static::$all = array_values(array_unique($values, SORT_STRING | SORT_FLAG_CASE));

        return static::$all;
    }

    /**
     * The part family a product is, or null when nothing identifies one.
     *
     * Resolution walks from most to least confident:
     *  1. a known part named outright in the product name ("Iridium Spark
     *     Plug" -> Spark Plug), longest match first so "Tire Tube" beats
     *     the "Tire" inside it;
     *  2. a known part sharing a word with the name ("Slick 4T Road Runner
     *     Motorcycle Oil" -> Engine Oil), which is what covers oils and
     *     similar goods that never spell the part out in full;
     *  3. a known part named outright in the broad category, so a lube with
     *     no name to go on still lands in "Lubricants" instead of nowhere.
     *
     * A trailing plural is tolerated throughout: "Spark Plugs" resolves.
     */
    public static function resolve(?string $name, ?string $category = null): ?string
    {
        $name = mb_strtolower(trim((string) $name));

        if ($name !== '') {
            $match = static::phraseMatch($name) ?? static::wordMatch($name);

            if ($match !== null) {
                return $match;
            }
        }

        $category = mb_strtolower(trim((string) $category));

        return $category === '' ? null : static::phraseMatch($category);
    }

    /**
     * Longest part whose full name appears in $text.
     */
    private static function phraseMatch(string $text): ?string
    {
        $best = null;
        $bestLength = -1;

        foreach (static::all() as $part) {
            $length = mb_strlen($part);

            if ($length <= $bestLength) {
                continue;
            }

            if (static::matchesPhrase($text, $part)) {
                $best = $part;
                $bestLength = $length;
            }
        }

        return $best;
    }

    /**
     * Part sharing the most words with $text; ties go to the longest part,
     * then to the earlier entry in the sorted list, so the outcome never
     * depends on database row order.
     */
    private static function wordMatch(string $text): ?string
    {
        $words = preg_split('/[^a-z0-9]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $words = array_flip($words);

        $best = null;
        $bestScore = 0;
        $bestLength = -1;

        foreach (static::all() as $part) {
            $partWords = preg_split('/[^a-z0-9]+/u', mb_strtolower($part), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            $score = 0;
            foreach ($partWords as $word) {
                if (isset($words[$word])) {
                    $score++;
                }
            }

            $length = mb_strlen($part);

            if ($score > $bestScore || ($score === $bestScore && $length > $bestLength)) {
                $best = $part;
                $bestScore = $score;
                $bestLength = $length;
            }
        }

        return $bestScore > 0 ? $best : null;
    }

    private static function matchesPhrase(string $text, string $part): bool
    {
        $pattern = '/\b' . preg_quote(mb_strtolower($part), '/') . 's?\b/u';

        return preg_match($pattern, $text) === 1;
    }

    /**
     * Correct the casing of a stored value against the known list.
     * Returns null for anything unrecognised.
     */
    public static function canonicalise(?string $value): ?string
    {
        $needle = mb_strtolower(trim((string) $value));

        if ($needle === '') {
            return null;
        }

        foreach (static::all() as $part) {
            if (mb_strtolower($part) === $needle) {
                return $part;
            }
        }

        return null;
    }

    /**
     * Turn raw stored part values into dropdown options: canonicalised,
     * trimmed, deduplicated case-insensitively and sorted.
     *
     * @param  iterable<int, mixed>  $values
     * @return list<string>
     */
    public static function options(iterable $values): array
    {
        return collect($values)
            ->map(fn ($value) => static::canonicalise($value) ?? trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->unique(fn ($value) => mb_strtolower($value))
            ->sort()
            ->values()
            ->all();
    }
}
