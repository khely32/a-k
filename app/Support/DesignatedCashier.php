<?php

namespace App\Support;

/**
 * The person designated to run the cash register for each branch, as
 * maintained by the owner.
 *
 * Keyed by branch name (not id) because that is what the reports and POS
 * header surface, and the lookup is case-insensitive so "POBLACION BRANCH"
 * still finds Len-len. Branches outside the mapping fall back to whoever is
 * logged in, so the header never shows a blank line.
 */
class DesignatedCashier
{
    private const BY_BRANCH = [
        'Moroboro Branch' => 'Irish Sarmiento Deano',
        'Poblacion Branch' => 'Len-len',
        'San Matias Branch' => 'Gio',
        'Banate Branch' => 'Poypoy',
    ];

    /**
     * The cashier name for the branch whose name is given, or null when the
     * branch has not been assigned one.
     */
    public static function forBranchName(?string $branchName): ?string
    {
        if ($branchName === null || trim($branchName) === '') {
            return null;
        }

        foreach (self::BY_BRANCH as $known => $cashier) {
            if (mb_strtolower(trim($known)) === mb_strtolower(trim($branchName))) {
                return $cashier;
            }
        }

        return null;
    }

    /**
     * The cashier name for the given branch model, or null.
     */
    public static function forBranch(?\App\Models\Branch $branch): ?string
    {
        return static::forBranchName($branch?->branch_name);
    }
}