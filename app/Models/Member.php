<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = [
        'display_name',
        'legal_name',
        'kana_name',
        'email',
        'phone',
        'status',
        'joined_at',
        'withdrawn_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(MemberAccount::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(MemberRole::class);
    }

    public function directPermissions(): HasMany
    {
        return $this->hasMany(MemberPermission::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_member_id');
    }
}
