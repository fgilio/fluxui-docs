<?php

namespace App\Services;

/**
 * Fuzzy search implementation for Flux UI documentation.
 *
 * Provides relevance-ranked search results with scoring
 * based on name, title, description, and keyword matches.
 */
class SearchIndex
{
    public function __construct(
        private DocRepository $repository
    ) {}

    /**
     * Search documentation by query string.
     *
     * Returns ranked results with relevance scoring.
     */
    public function search(string $query, int $limit = 10): array
    {
        $index = $this->repository->loadIndex();

        if (! $index || empty($index['items'])) {
            return [];
        }

        $query = strtolower(trim($query));
        $results = [];

        foreach ($index['items'] as $item) {
            $score = $this->calculateScore($query, $item);

            if ($score > 0) {
                $results[] = array_merge($item, ['score' => $score]);
            }
        }

        // Sort by score descending
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($results, 0, $limit);
    }

    /**
     * Calculate relevance score for an item against a query.
     */
    private function calculateScore(string $query, array $item): float
    {
        $score = 0;
        $name = strtolower($item['name'] ?? '');
        $title = strtolower($item['title'] ?? '');
        $description = strtolower($item['description'] ?? '');
        $keywords = array_map('strtolower', $item['keywords'] ?? []);

        // Exact name match - highest priority
        if ($name === $query) {
            return 100;
        }

        // Exact title match
        if ($title === $query) {
            return 90;
        }

        // Name starts with query
        if (str_starts_with($name, $query)) {
            $score += 70;
        }
        // Name contains query
        elseif (str_contains($name, $query)) {
            $score += 50;
        }

        // Title starts with query
        if (str_starts_with($title, $query)) {
            $score += 40;
        }
        // Title contains query
        elseif (str_contains($title, $query)) {
            $score += 30;
        }

        // Description contains query
        if (str_contains($description, $query)) {
            $score += 20;
        }

        // Keyword matches
        foreach ($keywords as $keyword) {
            if ($keyword === $query) {
                $score += 15;
            } elseif (str_contains($keyword, $query)) {
                $score += 10;
            }
        }

        // Fuzzy match on name using Levenshtein
        $distance = levenshtein($query, $name);
        if ($distance <= 2 && $distance > 0) {
            $score += max(0, 25 - ($distance * 10));
        }

        return $score;
    }
}
