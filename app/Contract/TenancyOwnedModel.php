<?php

namespace App\Contract;

interface TenancyOwnedModel
{
    public function tenantColumn(): string;
}
