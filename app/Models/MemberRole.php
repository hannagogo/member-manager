<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberRole extends Model
{
    protected $fillable = [
        'member_id',
        'role_id',
        'organization_id',
        'status',
        'granted_at',
        'revoked_at',
        'granted_by',
        'revoked_by',
    ];

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'granted_by');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'revoked_by');
    }
}
