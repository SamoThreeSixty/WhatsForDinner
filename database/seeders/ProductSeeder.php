<?php

namespace Database\Seeders;

use App\Support\Catalog\ProductImporter;
use Illuminate\Database\Seeder;
use JsonException;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $productsPath = database_path('data/catalog/products.json');

        if (! file_exists($productsPath)) {
            throw new RuntimeException('Missing product dataset at '.$productsPath);
        }

        $productContents = file_get_contents($productsPath);

        if ($productContents === false) {
            throw new RuntimeException('Unable to read '.$productsPath);
        }

        try {
            /** @var mixed $decodedProducts */
            $decodedProducts = json_decode($productContents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Invalid JSON dataset: '.$e->getMessage());
        }

        if (! is_array($decodedProducts)) {
            throw new RuntimeException('Product dataset root must be an array: '.$productsPath);
        }

        app(ProductImporter::class)->import($decodedProducts, dryRun: false);
    }
}
