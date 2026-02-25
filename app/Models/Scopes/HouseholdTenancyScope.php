<?php

namespace App\Models\Scopes;

use App\Contract\TenancyOwnedModel;
use App\Support\Tenancy\CurrentHousehold;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class HouseholdTenancyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!$model instanceof TenancyOwnedModel) {
            return;
        }

        $householdId = app(CurrentHousehold::class)->id();

        if ($householdId === null) {
            return;
        }

        $builder->where($model->qualifyColumn($model->tenantColumn()), $householdId);
    }
}
