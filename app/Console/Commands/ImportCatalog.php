<?php

namespace App\Console\Commands;

use App\Support\Catalog\CategoryImporter;
use App\Support\Catalog\IngredientImporter;
use App\Support\Catalog\ProductImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JsonException;
use RuntimeException;
use Throwable;

class ImportCatalog extends Command
{
    protected $signature = 'catalog:import
        {entity=all : categories|ingredients|products|all}
        {--categories=database/data/catalog/categories.json : Categories JSON file}
        {--ingredients=database/data/catalog/ingredients.json : Ingredients JSON file}
        {--products=database/data/catalog/products.json : Products JSON or JSONL file}
        {--dry-run : Validate and report without writing changes}';

    protected $description = 'Import flat catalog data using stable slug identifiers';

    public function handle(
        CategoryImporter $categoryImporter,
        IngredientImporter $ingredientImporter,
        ProductImporter $productImporter
    ): int {
        $entity = strtolower((string) $this->argument('entity'));
        $dryRun = (bool) $this->option('dry-run');

        $allowed = ['categories', 'ingredients', 'products', 'all'];
        if (! in_array($entity, $allowed, true)) {
            throw new RuntimeException('Invalid entity. Use categories, ingredients, products, or all.');
        }

        if ($dryRun) {
            DB::beginTransaction();

            try {
                $this->runImports($entity, $categoryImporter, $ingredientImporter, $productImporter, false);
            } catch (Throwable $e) {
                DB::rollBack();
                throw $e;
            }

            DB::rollBack();
            $this->info('Dry-run complete. No records were written.');

            return self::SUCCESS;
        }

        $this->runImports($entity, $categoryImporter, $ingredientImporter, $productImporter, false);

        return self::SUCCESS;
    }

    private function runImports(
        string $entity,
        CategoryImporter $categoryImporter,
        IngredientImporter $ingredientImporter,
        ProductImporter $productImporter,
        bool $dryRun
    ): void {
        if ($entity === 'categories' || $entity === 'all') {
            $rows = $this->readDataset((string) $this->option('categories'));
            $result = $categoryImporter->import($rows, $dryRun);
            $this->printResult('Categories', $result);
        }

        if ($entity === 'ingredients' || $entity === 'all') {
            $rows = $this->readDataset((string) $this->option('ingredients'));
            $result = $ingredientImporter->import($rows, $dryRun);
            $this->printResult('Ingredients', $result);
        }

        if ($entity === 'products' || $entity === 'all') {
            $rows = $this->readDataset((string) $this->option('products'));
            $result = $productImporter->import($rows, $dryRun);
            $this->printResult('Products', $result);
        }
    }

    /**
     * @return array<int, mixed>
     */
    private function readDataset(string $path): array
    {
        $fullPath = base_path($path);

        if (! file_exists($fullPath)) {
            throw new RuntimeException('File not found: '.$fullPath);
        }

        $contents = file_get_contents($fullPath);
        if ($contents === false) {
            throw new RuntimeException('Unable to read file: '.$fullPath);
        }

        if (str_ends_with(strtolower($fullPath), '.jsonl')) {
            return $this->decodeJsonLines($contents, $fullPath);
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Invalid JSON in '.$fullPath.': '.$e->getMessage());
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('JSON root must be an array: '.$fullPath);
        }

        return $decoded;
    }

    /**
     * @return array<int, mixed>
     */
    private function decodeJsonLines(string $contents, string $path): array
    {
        $rows = [];
        $lines = preg_split('/\r\n|\r|\n/', $contents) ?: [];

        foreach ($lines as $index => $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            try {
                /** @var mixed $decoded */
                $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new RuntimeException('Invalid JSONL at '.$path.' line '.($index + 1).': '.$e->getMessage());
            }

            if (! is_array($decoded)) {
                throw new RuntimeException('JSONL rows must decode to objects at '.$path.' line '.($index + 1));
            }

            $rows[] = $decoded;
        }

        return $rows;
    }

    /**
     * @param array{created:int,updated:int,skipped:int,total:int} $result
     */
    private function printResult(string $label, array $result): void
    {
        $this->info($label.' import complete.');
        $this->line('Total rows: '.$result['total']);
        $this->line('Created: '.$result['created']);
        $this->line('Updated: '.$result['updated']);
        $this->line('Skipped (no changes): '.$result['skipped']);
    }
}
