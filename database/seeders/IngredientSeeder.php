<?php

namespace Database\Seeders;

use App\Support\Catalog\CategoryImporter;
use App\Support\Catalog\IngredientImporter;
use Illuminate\Database\Seeder;
use JsonException;
use RuntimeException;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $categoriesPath = database_path('data/catalog/categories.json');
        $ingredientsPath = database_path('data/catalog/ingredients.json');

        if (! file_exists($categoriesPath)) {
            throw new RuntimeException('Missing category dataset at '.$categoriesPath);
        }

        if (! file_exists($ingredientsPath)) {
            throw new RuntimeException('Missing ingredient dataset at '.$ingredientsPath);
        }

        $categoryContents = file_get_contents($categoriesPath);
        $ingredientContents = file_get_contents($ingredientsPath);

        if ($categoryContents === false) {
            throw new RuntimeException('Unable to read '.$categoriesPath);
        }

        if ($ingredientContents === false) {
            throw new RuntimeException('Unable to read '.$ingredientsPath);
        }

        try {
            /** @var mixed $decodedCategories */
            $decodedCategories = json_decode($categoryContents, true, 512, JSON_THROW_ON_ERROR);
            /** @var mixed $decodedIngredients */
            $decodedIngredients = json_decode($ingredientContents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Invalid JSON dataset: '.$e->getMessage());
        }

        if (! is_array($decodedCategories)) {
            throw new RuntimeException('Category dataset root must be an array: '.$categoriesPath);
        }

        if (! is_array($decodedIngredients)) {
            throw new RuntimeException('Ingredient dataset root must be an array: '.$ingredientsPath);
        }

        app(CategoryImporter::class)->import($decodedCategories, dryRun: false);
        app(IngredientImporter::class)->import($decodedIngredients, dryRun: false);
    }
}
