<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
protected $fillable = [
    'name',
    'email',
    'password',
    'plain_password',
    'role',
    'branch',
    'branch_id',
    'session_id',
    'security_contact',
    'is_active',
];

    protected $hidden = [
        'password',
        'plain_password',
        'remember_token',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function branchLabel(): string
    {
        // Must call the relation explicitly: the legacy `branch` name-string
        // column on users shadows a `$this->branch` property access.
        $branch = $this->branch()->first();

        if (!$branch || !$branch->branch_name) {
            return 'MAIN BRANCH';
        }

        return $branch->isMainBranch() ? 'MAIN BRANCH' : strtoupper($branch->branch_name);
    }

    public function branchDisplayName(): string
    {
        $branch = $this->branch()->first();

        return (string) ($branch ? $branch->branch_name : '');
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}