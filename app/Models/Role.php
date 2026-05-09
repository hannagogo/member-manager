<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'code',
        'short_name',
        'name_ja',
        'name_en',
        'description',
    ];

    public function memberRoles(): HasMany
    {
        return $this->hasMany(MemberRole::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withTimestamps();
    }

    public function getDisplayNameAttribute(): string
    {
        return app()->getLocale() === 'ja'
            ? ($this->name_ja ?? $this->code)
            : ($this->name_en ?? $this->code);
    }
}
