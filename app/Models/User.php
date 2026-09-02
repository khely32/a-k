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
    'role',
    'branch',
    'branch_id',
    'session_id',
    'security_contact'
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
        $branchName = $this->branch()->value('branch_name');

        if (!$branchName || str_contains(strtolower($branchName), 'moroboro')) {
            return 'MAIN BRANCH';
        }

        return strtoupper($branchName);
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}