<?php

namespace Database\Factories;

use App\Models\Household;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class HouseholdFactory extends Factory
{
    protected $model = Household::class;

    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(4)),
            'join_code' => Str::upper(Str::random(8)),
            'locale' => 'en',
            'currency' => 'GBP',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
