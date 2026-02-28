<?php

namespace App\Support\Ingredients;

use App\Models\Ingredient;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;

class IngredientImporter
{
    /**
     * @param array<int, mixed> $rows
     * @return array{created:int,updated:int,skipped:int,total:int}
     */
    public function import(array $rows, bool $truncate = false): array
    {
        if ($truncate) {
            Ingredient::query()->withTrashed()->forceDelete();
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $index => $row) {
            $validator = Validator::make((array) $row, [
                'name' => ['required', 'string', 'max:255'],
                'category' => ['nullable', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException('Invalid ingredient at index '.$index.': '.$validator->errors()->first());
            }

            $name = Str::lower(trim((string) $row['name']));

            if ($name === '') {
                $skipped++;
                continue;
            }

            $category = isset($row['category']) ? trim((string) $row['category']) : null;
            $category = $category !== '' ? Str::lower($category) : null;

            $payload = [
                'name' => $name,
                'category' => $category,
            ];

            $model = Ingredient::query()
                ->withTrashed()
                ->where('name', $name)
                ->first();

            if ($model === null) {
                Ingredient::query()->create($payload);
                $created++;
                continue;
            }

            if ($model->trashed()) {
                $model->restore();
            }

            $model->fill($payload);

            if ($model->isDirty()) {
                $model->save();
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
