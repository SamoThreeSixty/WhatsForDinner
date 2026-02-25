<?php

namespace App\Observers;

use App\Contract\TenancyOwnedModel;
use App\Support\Tenancy\CurrentHousehold;

class HouseholdTenancyObserver
{
    public function creating(object $model): void
    {
        if (!$model instanceof TenancyOwnedModel) {
            return;
        }

        // Check if the tenant column has been set
        $column = $model->tenantColumn();
        if (!empty($model->$column)) {
            return;
        }

        $householdId = app(CurrentHousehold::class)->id();
        if ($householdId !== null) {
            $model->$column = $householdId;
        }
    }
}
