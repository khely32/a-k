<?php

namespace Tests\Unit;

use App\Support\PartFamily;
use Tests\TestCase;

class PartFamilyTest extends TestCase
{
    /**
     * The part list the seeded `category_sizes` table ships, so a name can
     * be matched even before that table exists (fresh install, tests).
     */
    public function test_the_seeded_part_list_is_available(): void
    {
        foreach (['Tire', 'Spark Plug', 'Oil Filter', 'Brake Pad', 'Chain', 'Battery', 'Tools'] as $part) {
            $this->assertContains($part, PartFamily::all());
        }
    }

    /**
     * The whole point: a product name says what part it is.
     */
    public function test_a_product_name_resolves_to_its_part_family(): void
    {
        $this->assertSame('Tire', PartFamily::resolve('Motorcycle Tire'));
        $this->assertSame('Spark Plug', PartFamily::resolve('Iridium Spark Plug'));
        $this->assertSame('Brake Pad', PartFamily::resolve('Brembo Brake Pad'));
        $this->assertSame('Battery', PartFamily::resolve('Motorcycle Battery'));
        $this->assertSame('Engine Oil', PartFamily::resolve('Shell Helix HX3 SAE-30 Mono-Grade Engine Oil'));
        $this->assertSame('Gear Oil', PartFamily::resolve('Gear Oil 80W-90'));
        $this->assertSame('Bearing', PartFamily::resolve('NTN Ball Bearing 6201'));
        $this->assertSame('CDI Unit', PartFamily::resolve('CDI Unit P710'));
    }

    /**
     * "Air Filter" must not be reported as a generic Filter, and a name
     * holding two known parts keeps the most specific one.
     */
    public function test_the_longest_match_wins(): void
    {
        $this->assertSame('Air Filter', PartFamily::resolve('Air Filter'));
        $this->assertSame('Oil Filter', PartFamily::resolve('Oil Filter'));
        $this->assertSame('Tire Tube', PartFamily::resolve('Motorcycle Tire Tube'));
        $this->assertSame('Disc Brake Rotor', PartFamily::resolve('Front Disc Brake Rotor'));
    }

    /**
     * Product names are routinely written in the plural.
     */
    public function test_a_trailing_plural_still_matches(): void
    {
        $this->assertSame('Spark Plug', PartFamily::resolve('Spark Plugs'));
        $this->assertSame('Bearing', PartFamily::resolve('Bearings'));
        $this->assertSame('Tools', PartFamily::resolve('Tools'));
    }

    public function test_resolution_is_case_insensitive(): void
    {
        $this->assertSame('Bearing', PartFamily::resolve('ntn ball bearing 6201'));
        $this->assertSame('Spark Plug', PartFamily::resolve('IRIDIUM SPARK PLUG'));
    }

    /**
     * A chemical with no part in its name has no part family - it stays
     * under "All Parts" rather than being forced into a wrong bucket.
     */
    public function test_a_name_without_a_known_part_returns_null(): void
    {
        $this->assertNull(PartFamily::resolve('Koby De-Rust Lubricating Spray'));
        $this->assertNull(PartFamily::resolve('All Purpose Cleaner'));
        $this->assertNull(PartFamily::resolve(''));
        $this->assertNull(PartFamily::resolve(null));
    }

    /**
     * Oils rarely spell the part out in full, but they still say the word
     * "oil" - the best shared word wins.
     */
    public function test_a_name_sharing_a_single_word_with_a_part_still_resolves(): void
    {
        $this->assertSame('Engine Oil', PartFamily::resolve('Slick 4T Road Runner Motorcycle Oil'));
        $this->assertSame('Engine Oil', PartFamily::resolve('Slick 4T Synthetic Performance Scooter Oil'));
        $this->assertSame('Engine Oil', PartFamily::resolve('Motor Oil'));
        $this->assertSame('Handle Grip', PartFamily::resolve('Handle Bar Weight'));
    }

    /**
     * With nothing else to go on, the broad system narrows it down: a lube
     * becomes Lubricants instead of falling out of the filter entirely.
     */
    public function test_the_broad_category_is_the_last_resort(): void
    {
        $this->assertSame('Lubricants', PartFamily::resolve('Koby De-Rust Spray', 'Lubricants & Maintenance'));
        $this->assertSame('Accessories', PartFamily::resolve('Mystery Box', 'Mirrors & Accessories'));
        $this->assertSame('Tire', PartFamily::resolve('Tubeless Sealant', 'Tires & Inner Tubes'));

        // Not every system maps to a listed part.
        $this->assertNull(PartFamily::resolve('Rear Shock Absorber', 'Suspension & Steering'));
        $this->assertNull(PartFamily::resolve('Rear Shock Absorber'));
    }

    public function test_canonicalise_corrects_casing_and_refuses_unknowns(): void
    {
        $this->assertSame('Spark Plug', PartFamily::canonicalise('spark plug'));
        $this->assertSame('Spark Plug', PartFamily::canonicalise('  SPARK PLUG  '));

        $this->assertNull(PartFamily::canonicalise('Engine Parts'));
        $this->assertNull(PartFamily::canonicalise(''));
        $this->assertNull(PartFamily::canonicalise(null));
    }

    /**
     * The dropdown must never show the same part twice because two rows
     * spell it differently.
     */
    public function test_options_are_trimmed_deduplicated_and_sorted(): void
    {
        $this->assertSame(
            ['Brake Pad', 'Spark Plug', 'Tire'],
            PartFamily::options(['Tire', '  spark plug  ', 'SPARK PLUG', 'Brake Pad', ''])
        );
    }
}
