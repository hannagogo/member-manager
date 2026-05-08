<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'actor_member_id',
        'action',
        'target_type',
        'target_id',
        'before_json',
        'after_json',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'before_json' => 'array',
            'after_json' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'actor_member_id');
    }
}
