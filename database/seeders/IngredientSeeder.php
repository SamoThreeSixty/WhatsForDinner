<?php

namespace Database\Seeders;

use App\Support\Ingredients\IngredientCatalogImporter;
use Illuminate\Database\Seeder;
use JsonException;
use RuntimeException;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/global_ingredients.base.json');

        if (! file_exists($path)) {
            throw new RuntimeException('Missing base ingredient dataset at '.$path);
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('Unable to read '.$path);
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Invalid JSON in '.$path.': '.$e->getMessage());
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('Dataset root must be an array: '.$path);
        }

        app(IngredientCatalogImporter::class)->import($decoded, truncate: false);
    }
}
