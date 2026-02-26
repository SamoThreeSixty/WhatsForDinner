<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Household extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'locale',
        'currency',
        'new_members',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(HouseholdMembership::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'household_memberships')
            ->withPivot(['role', 'status', 'approved_at', 'approved_by'])
            ->withTimestamps();
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(HouseholdAccess::class);
    }
}
