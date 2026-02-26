<?php

namespace App\Models;

use App\Contract\TenancyOwnedModel;
use App\Models\Scopes\HouseholdTenancyScope;
use App\Observers\HouseholdTenancyObserver;
use App\Support\Tenancy\CurrentHousehold;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AbstractTenancyModel extends Model implements TenancyOwnedModel
{
    public function scopeForCurrentHousehold(Builder $query): Builder
    {
        $householdId = app(CurrentHousehold::class)->id();

        if ($householdId === null) {
            return $query;
        }

        return $query->where($query->qualifyColumn($this->tenantColumn()), $householdId);
    }

    public function tenantColumn(): string
    {
        return 'household_id';
    }

    protected static function booted(): void
    {
        static::addGlobalScope(HouseholdTenancyScope::class);
        static::observe(HouseholdTenancyObserver::class);
    }
}
