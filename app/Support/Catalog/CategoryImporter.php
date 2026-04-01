<?php

namespace App\Support\Catalog;

use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class CategoryImporter
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
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException('Invalid category at index '.$index.': '.$validator->errors()->first());
            }

            $slug = trim((string) $row['slug']);
            $name = trim((string) $row['name']);

            $model = Category::query()->where('slug', $slug)->first();

            if ($model === null) {
                if (! $dryRun) {
                    Category::query()->create([
                        'slug' => $slug,
                        'name' => $name,
                    ]);
                }

                $created++;
                continue;
            }

            $model->fill(['name' => $name]);

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
