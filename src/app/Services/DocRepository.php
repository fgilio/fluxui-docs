<?php

namespace App\Services;

/**
 * Repository for reading and writing Flux UI documentation JSON files.
 *
 * Handles data directory resolution relative to the binary location,
 * supporting both development (src/) and production (skill root) contexts.
 */
class DocRepository
{
    private string $dataPath;

    public function __construct()
    {
        $this->dataPath = $this->resolveDataPath();
    }

    /**
     * Resolve the data directory path relative to the binary.
     */
    private function resolveDataPath(): string
    {
        // When running from PHAR, look for sibling data/ directory
        if (\Phar::running()) {
            $pharPath = \Phar::running(false);
            return dirname($pharPath) . '/data';
        }

        // Development: go up from src/ to skill root
        return dirname(__DIR__, 3) . '/data';
    }

    /**
     * List all documentation items, optionally filtered by category.
     */
    public function list(?string $category = null): array
    {
        $items = [];

        $categories = $category
            ? [$category]
            : ['components', 'layouts', 'guides'];

        foreach ($categories as $cat) {
            $items[$cat] = $this->listCategory($cat);
        }

        return $items;
    }

    /**
     * List all items in a category.
     */
    private function listCategory(string $category): array
    {
        $path = "{$this->dataPath}/{$category}";

        if (! is_dir($path)) {
            return [];
        }

        $files = glob("{$path}/*.json");
        $names = [];

        foreach ($files as $file) {
            $names[] = basename($file, '.json');
        }

        sort($names);
        return $names;
    }

    /**
     * Find a documentation item by name.
     *
     * Searches all categories if not specified.
     */
    public function find(string $name, ?string $category = null): ?array
    {
        $categories = $category
            ? [$category]
            : ['components', 'layouts', 'guides'];

        foreach ($categories as $cat) {
            $path = "{$this->dataPath}/{$cat}/{$name}.json";

            if (file_exists($path)) {
                $content = file_get_contents($path);
                return json_decode($content, true);
            }
        }

        return null;
    }

    /**
     * Save a documentation item.
     */
    public function save(string $category, string $name, array $data): void
    {
        $dir = "{$this->dataPath}/{$category}";

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = "{$dir}/{$name}.json";
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        file_put_contents($path, $json . "\n");
    }

    /**
     * Get suggestions for a name that wasn't found.
     */
    public function suggest(string $name, int $limit = 5): array
    {
        $all = $this->getAllNames();
        $suggestions = [];

        foreach ($all as $item) {
            $distance = levenshtein(strtolower($name), strtolower($item));
            $suggestions[$item] = $distance;
        }

        asort($suggestions);
        return array_slice(array_keys($suggestions), 0, $limit);
    }

    /**
     * Get all item names across all categories.
     */
    public function getAllNames(): array
    {
        $names = [];

        foreach (['components', 'layouts', 'guides'] as $category) {
            $names = array_merge($names, $this->listCategory($category));
        }

        return array_unique($names);
    }

    /**
     * Load the search index.
     */
    public function loadIndex(): ?array
    {
        $path = "{$this->dataPath}/index.json";

        if (! file_exists($path)) {
            return null;
        }

        return json_decode(file_get_contents($path), true);
    }

    /**
     * Save the search index.
     */
    public function saveIndex(array $index): void
    {
        $path = "{$this->dataPath}/index.json";
        $json = json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        file_put_contents($path, $json . "\n");
    }

    /**
     * Rebuild the search index from all documentation files.
     */
    public function rebuildIndex(): array
    {
        $items = [];

        foreach (['components', 'layouts', 'guides'] as $category) {
            foreach ($this->listCategory($category) as $name) {
                $doc = $this->find($name, $category);

                if ($doc) {
                    $items[] = [
                        'name' => $doc['name'] ?? $name,
                        'title' => $doc['title'] ?? ucfirst($name),
                        'description' => $doc['description'] ?? '',
                        'category' => $category,
                        'pro' => $doc['pro'] ?? false,
                        'keywords' => $this->extractKeywords($doc),
                    ];
                }
            }
        }

        $index = [
            'version' => '1.0',
            'updated_at' => date('c'),
            'items' => $items,
        ];

        $this->saveIndex($index);
        return $index;
    }

    /**
     * Extract searchable keywords from a doc.
     */
    private function extractKeywords(array $doc): array
    {
        $keywords = [];

        // Add title words
        if (! empty($doc['title'])) {
            $keywords = array_merge($keywords, explode(' ', strtolower($doc['title'])));
        }

        // Add related components
        if (! empty($doc['related'])) {
            $keywords = array_merge($keywords, $doc['related']);
        }

        // Add section titles
        foreach ($doc['sections'] ?? [] as $section) {
            if (! empty($section['title'])) {
                $keywords[] = strtolower($section['title']);
            }
        }

        // Add prop names from reference
        foreach ($doc['reference'] ?? [] as $component => $ref) {
            foreach ($ref['props'] ?? [] as $prop) {
                if (! empty($prop['name'])) {
                    $keywords[] = strtolower($prop['name']);
                }
            }
        }

        return array_values(array_unique($keywords));
    }

    /**
     * Get the data path for external use.
     */
    public function getDataPath(): string
    {
        return $this->dataPath;
    }
}
