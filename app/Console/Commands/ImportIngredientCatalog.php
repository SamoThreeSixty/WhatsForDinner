<?php

namespace App\Console\Commands;

use App\Support\Ingredients\IngredientImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use JsonException;
use RuntimeException;

class ImportIngredientCatalog extends Command
{
    protected $signature = 'ingredients:catalog-import
        {source=database/data/global_ingredients.base.json : Local JSON path relative to project root}
        {--url= : Import JSON directly from a URL instead of a local file}
        {--truncate : Remove existing catalog before import}';

    protected $description = 'Import ingredient catalog entries from a JSON extract';

    public function handle(IngredientImporter $importer): int
    {
        $json = $this->option('url')
            ? $this->readFromUrl((string) $this->option('url'))
            : $this->readFromFile((string) $this->argument('source'));

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('JSON is invalid: '.$e->getMessage());
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('JSON root must be an array of ingredient objects.');
        }

        $result = $importer->import(
            $decoded,
            truncate: (bool) $this->option('truncate'),
        );

        $this->info('Ingredient catalog import complete.');
        $this->line('Total rows: '.$result['total']);
        $this->line('Created: '.$result['created']);
        $this->line('Updated: '.$result['updated']);
        $this->line('Skipped (no changes): '.$result['skipped']);

        return self::SUCCESS;
    }

    private function readFromFile(string $path): string
    {
        $fullPath = base_path($path);

        if (! file_exists($fullPath)) {
            throw new RuntimeException('File not found: '.$fullPath);
        }

        $contents = file_get_contents($fullPath);

        if ($contents === false) {
            throw new RuntimeException('Unable to read file: '.$fullPath);
        }

        return $contents;
    }

    private function readFromUrl(string $url): string
    {
        $response = Http::timeout(30)->get($url);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to fetch URL: '.$url.' (HTTP '.$response->status().')');
        }

        return $response->body();
    }
}
