<?php

namespace Tests\Unit;

use App\Support\ProductCategory;
use Tests\TestCase;

class ProductCategoryTest extends TestCase
{
    public function test_dropdown_list_is_the_nine_canonical_categories(): void
    {
        $this->assertSame([
            'Bearings',
            'Brake System',
            'Drive Train & Transmission',
            'Electrical & Lighting',
            'Engine Parts',
            'Fasteners & Hardware',
            'Lubricants & Maintenance',
            'Mirrors & Accessories',
            'Tires & Inner Tubes',
        ], ProductCategory::ALL);
    }

    /**
     * Every duplicate/variant spelling the original report called out must
     * collapse onto one canonical value.
     */
    public function test_redundant_type_spellings_collapse_to_one_category(): void
    {
        $this->assertSame(ProductCategory::LUBRICANTS, ProductCategory::resolve('Lubricant'));
        $this->assertSame(ProductCategory::LUBRICANTS, ProductCategory::resolve('Lubricants'));
        $this->assertSame(ProductCategory::LUBRICANTS, ProductCategory::resolve('Lubricants / Gear Oil'));
        $this->assertSame(ProductCategory::LUBRICANTS, ProductCategory::resolve('Maintenance'));
        $this->assertSame(ProductCategory::LUBRICANTS, ProductCategory::resolve('Maintenance Sprays'));
        $this->assertSame(ProductCategory::LUBRICANTS, ProductCategory::resolve('Maintenance / Chemical Sealants'));

        $this->assertSame(ProductCategory::ACCESSORIES, ProductCategory::resolve('Accessories'));
        $this->assertSame(ProductCategory::ACCESSORIES, ProductCategory::resolve('Accessory'));
        $this->assertSame(ProductCategory::ACCESSORIES, ProductCategory::resolve('Accessories / Handle Levers'));

        $this->assertSame(ProductCategory::BRAKE, ProductCategory::resolve('Brake System'));
        $this->assertSame(ProductCategory::BRAKE, ProductCategory::resolve('Brake System / Hard Parts'));

        $this->assertSame(ProductCategory::BEARINGS, ProductCategory::resolve('bearing'));
        $this->assertSame(ProductCategory::BEARINGS, ProductCategory::resolve('Bearings'));
    }

    /**
     * Selecting "Bearings" has to return NTN, KOYO and generic bearings
     * together, i.e. the grouping ignores brand entirely.
     */
    public function test_grouping_ignores_brand(): void
    {
        $expected = ProductCategory::BEARINGS;

        $this->assertSame($expected, ProductCategory::resolve('Bearing', 'NTN Ball Bearing 6201'));
        $this->assertSame($expected, ProductCategory::resolve('Bearing', 'KOYO Wheel Bearing'));
        $this->assertSame($expected, ProductCategory::resolve('Steering Bearing', 'Generic'));
    }

    public function test_every_canonical_category_is_reachable_from_a_type(): void
    {
        $samples = [
            ProductCategory::BEARINGS      => 'Bearing',
            ProductCategory::BRAKE         => 'Brake Pad',
            ProductCategory::DRIVE_TRAIN   => 'Chain',
            ProductCategory::ELECTRICAL    => 'Battery',
            ProductCategory::ENGINE        => 'Piston',
            ProductCategory::FASTENERS     => 'Bolt',
            ProductCategory::LUBRICANTS    => 'Engine Oil',
            ProductCategory::ACCESSORIES   => 'Mirror',
            ProductCategory::TIRES         => 'Tire',
        ];

        foreach ($samples as $category => $type) {
            $this->assertSame($category, ProductCategory::resolve($type), "type [{$type}]");
        }

        $this->assertSame($samples, array_combine(ProductCategory::ALL, array_values($samples)));
    }

    /**
     * Filters must not depend on spacing or casing.
     */
    public function test_resolve_is_trim_and_case_insensitive(): void
    {
        foreach (['BEARINGS', '  Bearings  ', 'BeArInGs', 'bearings'] as $variant) {
            $this->assertSame(ProductCategory::BEARINGS, ProductCategory::resolve($variant));
        }
    }

    /**
     * `type` doubles as a variant code ("Std", "C1", "0.25") for parts whose
     * real identity lives in the name. Those must still land somewhere.
     */
    public function test_variant_codes_fall_back_rather_than_returning_null(): void
    {
        $this->assertSame(ProductCategory::FALLBACK, ProductCategory::resolve('Std', 'Wave 100'));
        $this->assertSame(ProductCategory::FALLBACK, ProductCategory::resolve('C1'));
        $this->assertSame(ProductCategory::FALLBACK, ProductCategory::resolve(null, null));
    }

    /**
     * A part number in `type` must not defeat a real category in the name.
     */
    public function test_part_numbers_do_not_break_resolution(): void
    {
        $this->assertSame(
            ProductCategory::ENGINE,
            ProductCategory::resolve('Std Piston (13011-K60-T00)', 'Click 125')
        );
        $this->assertSame(
            ProductCategory::ENGINE,
            ProductCategory::resolve('Ring (13011-K60-B00)', 'Click 125')
        );
    }

    /**
     * Nothing in `Std (12140-36H50)` + `Smash 115` names a category, so the
     * resolver has no keyword to act on. It must still return a valid
     * canonical value rather than null or an out-of-list one.
     */
    public function test_unrecognised_part_numbers_still_resolve_to_a_valid_category(): void
    {
        $resolved = ProductCategory::resolve('Std (12140-36H50)', 'Smash 115');

        $this->assertSame(ProductCategory::FALLBACK, $resolved);
        $this->assertTrue(ProductCategory::isValid($resolved));
    }

    public function test_is_valid_rejects_non_canonical_values(): void
    {
        foreach (ProductCategory::ALL as $category) {
            $this->assertTrue(ProductCategory::isValid($category));
        }

        $this->assertFalse(ProductCategory::isValid('Lubricant'));
        $this->assertFalse(ProductCategory::isValid('Lubricants'));
        $this->assertFalse(ProductCategory::isValid(''));
        $this->assertFalse(ProductCategory::isValid(null));
        $this->assertFalse(ProductCategory::isValid('  Bearings  '));
    }

    /**
     * canonicalise() must correct casing/aliases but never invent a value
     * for an input nobody defined.
     */
    public function test_canonicalise_fixes_aliases_and_refuses_unknowns(): void
    {
        $this->assertSame(ProductCategory::LUBRICANTS, ProductCategory::canonicalise('lubricant'));
        $this->assertSame(ProductCategory::LUBRICANTS, ProductCategory::canonicalise('LUBRICANTS'));
        $this->assertSame(ProductCategory::LUBRICANTS, ProductCategory::canonicalise(' Lubricants / Gear Oil '));
        $this->assertSame(ProductCategory::ACCESSORIES, ProductCategory::canonicalise('accessories'));
        $this->assertSame(ProductCategory::BEARINGS, ProductCategory::canonicalise('Bearing'));
        $this->assertSame(ProductCategory::BEARINGS, ProductCategory::canonicalise('Bearings'));

        $this->assertNull(ProductCategory::canonicalise('Totally Made Up'));
        $this->assertNull(ProductCategory::canonicalise(''));
        $this->assertNull(ProductCategory::canonicalise(null));
        $this->assertNull(ProductCategory::canonicalise('0.25'));
    }

    /**
     * The original SQL in the brief maps these four groups; each must land
     * on the category the brief specified.
     */
    public function test_spec_sql_equivalents(): void
    {
        foreach (['bearing', 'bearings'] as $from) {
            $this->assertSame(ProductCategory::BEARINGS, ProductCategory::resolve($from));
        }

        foreach (['lubricant', 'lubricants', 'lubricants / gear oil', 'maintenance',
                  'maintenance sprays', 'maintenance / chemical sealants'] as $from) {
            $this->assertSame(ProductCategory::LUBRICANTS, ProductCategory::resolve($from));
        }

        foreach (['accessories', 'accessories / handle levers'] as $from) {
            $this->assertSame(ProductCategory::ACCESSORIES, ProductCategory::resolve($from));
        }

        foreach (['brake system', 'brake system / hard parts'] as $from) {
            $this->assertSame(ProductCategory::BRAKE, ProductCategory::resolve($from));
        }
    }
}
