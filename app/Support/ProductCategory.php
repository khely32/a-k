<?php

namespace App\Support;

/**
 * Single source of truth for the product category taxonomy.
 *
 * The POS and Master Products dropdowns, the DB column, the backfill
 * migrations and the Product model all read from this class so a category
 * can never be spelled two different ways in two different places.
 *
 * `products.type` is deliberately left alone: it doubles as a variant/size
 * code (e.g. "Std", "C1", "0.25", "Std Piston (13011-K60-T00)") and feeds
 * Product::findDuplicate(). `category` is the new, normalised top-level
 * grouping derived from it.
 */
class ProductCategory
{
    public const BEARINGS = 'Bearings';
    public const BRAKE = 'Brake System';
    public const DRIVE_TRAIN = 'Drive Train & Transmission';
    public const ELECTRICAL = 'Electrical & Lighting';
    public const ENGINE = 'Engine Parts';
    public const FASTENERS = 'Fasteners & Hardware';
    public const LUBRICANTS = 'Lubricants & Maintenance';
    public const ACCESSORIES = 'Mirrors & Accessories';
    public const SUSPENSION = 'Suspension & Steering';
    public const TIRES = 'Tires & Inner Tubes';

    public const ALL = [
        self::BEARINGS,
        self::BRAKE,
        self::DRIVE_TRAIN,
        self::ELECTRICAL,
        self::ENGINE,
        self::FASTENERS,
        self::LUBRICANTS,
        self::ACCESSORIES,
        self::SUSPENSION,
        self::TIRES,
    ];

    /**
     * Where a product lands when nothing in its type/name matches a rule.
     *
     * NULL would hide the item from every single-category view, so an
     * uncategorised part is parked in the broadest bucket instead. Products
     * resolved this way are reported by the backfill so they can be reviewed.
     */
    public const FALLBACK = self::ACCESSORIES;

    /**
     * Ordered, first-match-wins rules. Each entry is [category, patterns].
     *
     * Patterns are matched as whole words against the lowercased
     * "type + name" haystack. Order carries real meaning:
     *  - accessories before brake, so "Accessories / Handle Levers" stays in
     *    Accessories while a bare "Lever" still resolves to Brake System
     *  - engine "filter" before lubricants, so "Oil Filter" is Engine Parts
     *  - lubricants before engine, so "Engine Oil" is not Engine Parts
     *  - lubricants before drive train, so "Chain Lube" is not Drive Train
     *  - suspension after bearings, so "Steering Bearing" stays Bearings
     *  - suspension before lubricants, so "Rear Shock Absorber Oil" is not
     *    filed as a lubricant (a bare "Fork Oil" carries no suspension
     *    keyword and still lands in Lubricants & Maintenance)
     */
    private const RULES = [
        [self::BEARINGS, ['bearing']],
        [self::ACCESSORIES, ['accessor', 'mirror', 'grip', 'fairing', 'seat cover']],
        [self::BRAKE, ['brake', 'lever', 'master cylinder', 'disc', 'rotor']],
        [self::TIRES, ['tire', 'tyre', 'tube', 'rim', 'wheel']],
        [self::SUSPENSION, [
            'shock', 'absorber', 'suspension', 'steering', 'swing arm',
            'swingarm', 'ball joint', 'tie rod',
        ]],
        [self::ENGINE, ['filter']],
        [self::LUBRICANTS, [
            'oil', 'lubricant', 'lube', 'grease', 'coolant', 'sealant',
            'spray', 'maintenance', 'cleaning', 'cleaner', 'atf', 'additive',
            'fluid',
        ]],
        [self::DRIVE_TRAIN, [
            'chain', 'sprocket', 'clutch', 'roller weight', 'drive belt',
            'gearbox', 'transmission', 'cable',
        ]],
        [self::ELECTRICAL, [
            'battery', 'headlight', 'tail light', 'light', 'bulb', 'horn',
            'switch', 'signal', 'cdi', 'coil', 'relay', 'rectifier', 'stator',
            'wiring', 'electrical', 'ignition', 'regulator', 'starter',
        ]],
        [self::ENGINE, [
            'engine', 'piston', 'valve', 'gasket', 'spark plug', 'carburetor',
            'carburettor', 'ring', 'cylinder', 'spark',
        ]],
        [self::FASTENERS, [
            'nut', 'bolt', 'screw', 'washer', 'axle', 'clip', 'fastener',
            'hardware', 'tool', 'pin',
        ]],
    ];

    /**
     * Resolve a normalised category from a product's type and name.
     */
    public static function resolve(?string $type, ?string $name = null): string
    {
        return static::match($type, $name) ?? static::FALLBACK;
    }

    /**
     * Like resolve(), but returns null when no rule matched instead of
     * falling back. Used by canonicalise() so that a real match on
     * Mirrors & Accessories (the fallback bucket) stays distinguishable
     * from an unrecognised input.
     */
    private static function match(?string $type, ?string $name = null): ?string
    {
        $haystack = static::normalise(implode(' ', array_filter([
            $type,
            $name,
        ], static fn ($v) => trim((string) $v) !== '')));

        if ($haystack === '') {
            return null;
        }

        foreach (static::RULES as [$category, $patterns]) {
            foreach ($patterns as $pattern) {
                if (static::matches($haystack, $pattern)) {
                    return $category;
                }
            }
        }

        return null;
    }

    /**
     * True when $value is one of the canonical categories.
     */
    public static function isValid(?string $value): bool
    {
        return $value !== null && in_array($value, static::ALL, true);
    }

    /**
     * Map a legacy/alias category string onto its canonical spelling.
     * Returns null when the input cannot be recognised.
     */
    public static function canonicalise(?string $value): ?string
    {
        $needle = static::normalise($value);

        if ($needle === '') {
            return null;
        }

        foreach (static::ALL as $category) {
            if (static::normalise($category) === $needle) {
                return $category;
            }
        }

        $resolved = static::match($value);

        // match() returns null when nothing matched, so an unknown string
        // stays unknown rather than being silently filed under the fallback.
        return $resolved;
    }

    /**
     * Turn raw stored category values into dropdown options.
     *
     * Each value is canonicalised (so a legacy alias never reaches the UI),
     * trimmed, deduplicated case-insensitively and sorted - otherwise a
     * column holding "Tire" and "tires" renders as two separate options.
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

    private static function matches(string $haystack, string $pattern): bool
    {
        $quoted = preg_quote($pattern, '/');
        $regex = '/\b' . str_replace(' ', '\s+', $quoted) . '/u';

        return preg_match($regex, $haystack) === 1;
    }

    private static function normalise(?string $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9&\/]+/u', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
