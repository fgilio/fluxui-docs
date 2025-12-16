<?php

namespace App\Commands;

use App\Services\SearchIndex;
use LaravelZero\Framework\Commands\Command;

/**
 * Fuzzy search for Flux UI documentation.
 *
 * Returns relevance-ranked results with scoring
 * based on name, title, and description matches.
 */
class SearchCommand extends Command
{
    protected $signature = 'search
        {query : Search term}
        {--limit=10 : Maximum number of results}
        {--json : Output as JSON}';

    protected $description = 'Search Flux UI documentation';

    public function handle(SearchIndex $index): int
    {
        $query = $this->argument('query');
        $limit = (int) $this->option('limit');

        $results = $index->search($query, $limit);

        if ($this->option('json')) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        if (empty($results)) {
            $this->warn("No results for: {$query}");
            $this->line('Try a different search term or run "flux list" to see all items.');
            return self::SUCCESS;
        }

        $this->info("Results for: {$query}");
        $this->newLine();

        $tableData = array_map(function ($result) {
            $pro = ($result['pro'] ?? false) ? ' [Pro]' : '';
            return [
                $result['name'],
                $result['category'],
                mb_substr($result['description'] ?? '', 0, 50) . (strlen($result['description'] ?? '') > 50 ? '...' : '') . $pro,
            ];
        }, $results);

        $this->table(['Name', 'Category', 'Description'], $tableData);

        return self::SUCCESS;
    }
}
