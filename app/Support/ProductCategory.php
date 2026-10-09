<?php

namespace App\Support;

use App\Models\CategorySize;
use App\Models\Product;

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
    public const BODY = 'Body & Fairings';
    public const BRAKE = 'Brake System';
    public const COOLING = 'Cooling System';
    public const DRIVE_TRAIN = 'Drive Train & Transmission';
    public const ELECTRICAL = 'Electrical & Lighting';
    public const ENGINE = 'Engine Parts';
    public const EXHAUST = 'Exhaust & Emissions';
    public const FASTENERS = 'Fasteners & Hardware';
    public const FRAME = 'Frame & Chassis';
    public const FUEL = 'Fuel System & Air Intake';
    public const HANDLEBARS = 'Handlebars & Controls';
    public const INSTRUMENTATION = 'Instrumentation & Gauges';
    public const LUBRICANTS = 'Lubricants & Maintenance';
    public const ACCESSORIES = 'Mirrors & Accessories';
    public const SUSPENSION = 'Suspension & Steering';
    public const TIRES = 'Tires & Inner Tubes';
    public const WHEELS = 'Wheels & Rims';

    public const ALL = [
        self::BEARINGS,
        self::BODY,
        self::BRAKE,
        self::COOLING,
        self::DRIVE_TRAIN,
        self::ELECTRICAL,
        self::ENGINE,
        self::EXHAUST,
        self::FASTENERS,
        self::FRAME,
        self::FUEL,
        self::HANDLEBARS,
        self::INSTRUMENTATION,
        self::LUBRICANTS,
        self::ACCESSORIES,
        self::SUSPENSION,
        self::TIRES,
        self::WHEELS,
    ];

    /**
     * Where a product lands when nothing in its type/name matches a rule.
     *
     * NULL would hide the item from every single-category view, so an
     * uncategorised part is parked in the broadest bucket instead. Only
     * resolve() and its callers that need a guaranteed-canonical result use
     * this; new saves go through resolveDynamic() so a brand-new type the
     * owner typed becomes its own category instead.
     */
    public const FALLBACK = self::ACCESSORIES;

    /**
     * Ordered, first-match-wins rules. Each entry is [category, patterns].
     *
     * Patterns are matched as whole words against the lowercased
     * "type + name" haystack - the type is in there because that is what
     * the owner picks when adding a product ("Fuel System & Air Intake"),
     * so a type naming a system wins whenever no rule above it disagrees.
     * Order carries real meaning:
     *  - handlebars before accessories, so a "Handlebar Grips" set stays
     *    with its bar instead of being filed as a generic accessory
     *  - accessories before brake, so "Accessories / Handle Levers" stays in
     *    Accessories while a bare "Lever" still resolves to Brake System
     *  - suspension before wheels/tires, so a "Shock Inner Tube" is not a
     *    tire and "Steering Bearing" (checked first) stays Bearings
     *  - fuel before the bare "filter" rule, so an air filter is a fuel
     *    system part rather than Engine Parts, while "Oil Filter" - which
     *    never says "air" - is still Engine Parts
     *  - engine "filter" before lubricants, so "Oil Filter" is Engine Parts
     *  - lubricants before engine, so "Engine Oil" is not Engine Parts
     *  - lubricants before drive train, so "Chain Lube" is not Drive Train
     *  - instrumentation before drive train, so a "Speedometer Cable" is
     *    instrumentation rather than transmission cabling
     *  - body dead last, so "Body Bolt" stays Fasteners. A "Body Fairing"
     *    typed Mirrors & Accessories stays there via the "accessor" rule,
     *    while an untyped fender or body panel lands in Body & Fairings
     */
    private const RULES = [
        [self::BEARINGS, ['bearing']],
        [self::HANDLEBARS, ['handlebar', 'handle bar']],
        [self::ACCESSORIES, ['accessor', 'mirror', 'grip', 'seat cover']],
        [self::BRAKE, ['brake', 'lever', 'master cylinder', 'disc', 'rotor']],
        [self::SUSPENSION, [
            'shock', 'absorber', 'suspension', 'steering', 'swing arm',
            'swingarm', 'ball joint', 'tie rod',
        ]],
        [self::WHEELS, ['wheel', 'rim', 'mag', 'spoke']],
        [self::TIRES, ['tire', 'tyre', 'tube']],
        [self::EXHAUST, ['exhaust', 'muffler', 'silencer']],
        [self::COOLING, ['radiator', 'cooling', 'thermostat']],
        [self::FRAME, ['frame', 'chassis', 'crash guard', 'side stand', 'center stand']],
        [self::FUEL, ['fuel', 'air filter', 'intake']],
        [self::ENGINE, ['filter']],
        [self::LUBRICANTS, [
            'oil', 'lubricant', 'lube', 'grease', 'coolant', 'sealant',
            'spray', 'maintenance', 'cleaning', 'cleaner', 'atf', 'additive',
            'fluid',
        ]],
        [self::INSTRUMENTATION, ['speedometer', 'odometer', 'gauge', 'instrument']],
        [self::DRIVE_TRAIN, [
            'chain', 'sprocket', 'clutch', 'roller weight', 'drive belt',
            'gearbox', 'transmission', 'cable', 'drive train',
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
        [self::BODY, ['fender', 'body']],
    ];

    /**
     * Resolve a normalised category from a product's type and name.
     */
    public static function resolve(?string $type, ?string $name = null): string
    {
        return static::match($type, $name) ?? static::FALLBACK;
    }

    /**
     * The save-time variant: a type that matches no rule becomes its own
     * category (stored verbatim) instead of the fallback bucket, so the
     * owner's real groups show up in the dynamic dropdowns automatically.
     * Only a blank type falls back.
     */
    public static function resolveDynamic(?string $type, ?string $name = null): string
    {
        $resolved = static::match($type, $name);

        if ($resolved !== null) {
            return $resolved;
        }

        $type = is_string($type) ? trim((string) $type) : '';

        // Codes like "0.25" and stray symbols are variant/size values, not
        // categories - they keep falling back. Anything with a letter in it
        // reads like a real group, so it becomes one.
        if ($type !== '' && preg_match('/[a-z]/i', $type) === 1) {
            return trim(preg_replace('/\s+/', ' ', $type));
        }

        return static::FALLBACK;
    }

    /**
     * What a type field should suggest: the curated `category_sizes` groups,
     * the canonical groups, and any category already stored on a product -
     * so a brand-new type becomes suggestible the moment its first product
     * exists, without ever showing `type`'s variant codes.
     *
     * @return list<string>
     */
    public static function typeSuggestions(): array
    {
        return collect(static::ALL)
            ->merge(CategorySize::query()->distinct()->orderBy('category')->pluck('category'))
            ->merge(Product::query()->whereNotNull('category')->distinct()->pluck('category'))
            ->filter(fn ($value) => trim((string) $value) !== '')
            ->unique(fn ($value) => mb_strtolower(trim((string) $value)))
            ->sort()
            ->values()
            ->all();
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
