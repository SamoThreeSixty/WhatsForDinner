<?php

namespace App\Support\Catalog;

use App\Models\Category;
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
    public function import(array $rows, bool $dryRun = false): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $index => $row) {
            $validator = Validator::make((array) $row, [
                'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
                'name' => ['required', 'string', 'max:255'],
                'category_slug' => ['required', 'string', 'max:255', 'exists:categories,slug'],
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException('Invalid ingredient at index '.$index.': '.$validator->errors()->first());
            }

            $slug = trim((string) $row['slug']);
            $name = Str::lower(trim((string) $row['name']));
            $categorySlug = trim((string) $row['category_slug']);

            $category = Category::query()->where('slug', $categorySlug)->first();
            if ($category === null) {
                throw new InvalidArgumentException('Ingredient '.$slug.' references missing category_slug: '.$categorySlug);
            }

            $payload = [
                'slug' => $slug,
                'name' => $name,
                'category_id' => $category->id,
                'category' => $categorySlug,
            ];

            $model = Ingredient::query()->withTrashed()->where('slug', $slug)->first();

            if ($model === null) {
                if (! $dryRun) {
                    Ingredient::query()->create($payload);
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
