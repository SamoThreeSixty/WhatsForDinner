<?php

namespace App\Support\Catalog;

use App\Enums\UnitType;
use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class ProductImporter
{
    /**
     * @param array<int, mixed> $rows
     * @return array{created:int,updated:int,skipped:int,total:int}
     */
    public function import(array $rows, bool $dryRun = false): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $index => $row) {
            $validator = Validator::make((array) $row, [
                'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
                'ingredient_slug' => ['required', 'string', 'max:255', 'exists:ingredients,slug'],
                'company' => ['nullable', 'string', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'unit_type' => ['required', Rule::in(array_column(UnitType::cases(), 'value'))],
                'unit_options' => ['nullable', 'array'],
                'unit_options.*' => ['string', 'max:32'],
                'unit_default' => ['required', 'string', 'max:32'],
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException('Invalid product at index '.$index.': '.$validator->errors()->first());
            }

            $slug = trim((string) $row['slug']);
            $ingredientSlug = trim((string) $row['ingredient_slug']);

            $ingredient = Ingredient::query()->withTrashed()->where('slug', $ingredientSlug)->first();
            if ($ingredient === null) {
                throw new InvalidArgumentException('Product '.$slug.' references missing ingredient_slug: '.$ingredientSlug);
            }

            $payload = [
                'slug' => $slug,
                'ingredient_id' => $ingredient->id,
                'company' => isset($row['company']) ? trim((string) $row['company']) : null,
                'name' => trim((string) $row['name']),
                'unit_type' => (string) $row['unit_type'],
                'unit_options' => isset($row['unit_options']) && is_array($row['unit_options']) ? array_values($row['unit_options']) : [],
                'unit_default' => trim((string) $row['unit_default']),
            ];

            $model = Product::query()->withTrashed()->where('slug', $slug)->first();

            if ($model === null) {
                if (! $dryRun) {
                    Product::query()->create($payload);
                }

                $created++;
                continue;
            }

            if ($model->trashed() && ! $dryRun) {
                $model->restore();
            }

            $model->fill($payload);

            if ($model->isDirty()) {
                if (! $dryRun) {
                    $model->save();
                }

                $updated++;
                continue;
            }

            $skipped++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'total' => count($rows),
        ];
    }
}
