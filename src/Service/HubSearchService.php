<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use App\Search\HubSearchProviderInterface;

final class HubSearchService
{
    /** @var array<string, HubSearchProviderInterface> */
    private array $providersByKey = [];

    /**
     * @param iterable<HubSearchProviderInterface> $providers
     */
    public function __construct(#[AutowireIterator('app.hub_search_provider')] iterable $providers)
    {
        foreach ($providers as $provider) {
            $this->providersByKey[$provider->getPageKey()] = $provider;
        }
    }

    /**
     * @return array<int, array{key: string, label: string, routeName: string}>
     */
    public function getAvailablePages(): array
    {
        $pages = [];

        foreach ($this->providersByKey as $provider) {
            $pages[] = [
                'key' => $provider->getPageKey(),
                'label' => $provider->getPageLabel(),
                'routeName' => $provider->getPageRouteName(),
            ];
        }

        usort($pages, static fn(array $a, array $b) => strcmp($a['label'], $b['label']));

        return $pages;
    }

    /**
     * @param array<int, string> $enabledPages
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, array $enabledPages, int $limit = 10): array
    {
        $normalizedQuery = $this->normalize($query);
        if ($normalizedQuery === '') {
            return [];
        }

        $tokens = array_values(array_filter(preg_split('/\s+/', $normalizedQuery) ?: []));
        $pageOrder = array_flip($enabledPages);
        $candidates = [];

        foreach ($enabledPages as $pageKey) {
            if (!isset($this->providersByKey[$pageKey])) {
                continue;
            }

            foreach ($this->providersByKey[$pageKey]->getNodes() as $node) {
                $label = (string) ($node['label'] ?? '');
                $normalizedLabel = $this->normalize($label);

                $scoreData = $this->calculateScore($normalizedLabel, $normalizedQuery, $tokens);
                if (!$scoreData['match']) {
                    continue;
                }

                $typePriority = (int) ($node['typePriority'] ?? 0);
                $depth = max(1, (int) ($node['depth'] ?? 1));
                $pagePriority = 100 - ((int) ($pageOrder[$pageKey] ?? 99) * 5);

                $candidates[] = [
                    'uid' => (string) ($node['uid'] ?? ''),
                    'label' => $label,
                    'type' => (string) ($node['type'] ?? 'unknown'),
                    'typeLabel' => (string) ($node['typeLabel'] ?? 'Unknown'),
                    'depth' => $depth,
                    'icon' => (string) ($node['icon'] ?? 'images/icons/icon_default.png'),
                    'routeName' => (string) ($node['routeName'] ?? ''),
                    'routeParams' => (array) ($node['routeParams'] ?? []),
                    'score' => $scoreData['score'] + $typePriority + $pagePriority - ($depth * 3),
                    'queryTokens' => $tokens,
                ];
            }
        }

        usort($candidates, static function (array $a, array $b): int {
            if ($a['score'] !== $b['score']) {
                return $b['score'] <=> $a['score'];
            }

            if ($a['depth'] !== $b['depth']) {
                return $a['depth'] <=> $b['depth'];
            }

            return strcmp($a['label'], $b['label']);
        });

        return array_slice($candidates, 0, $limit);
    }

    public function resolvePageRouteFromQuery(string $query): ?string
    {
        $normalizedQuery = $this->normalize($query);
        if ($normalizedQuery === '') {
            return null;
        }

        $best = null;
        $bestScore = -1;

        foreach ($this->providersByKey as $provider) {
            $label = $this->normalize($provider->getPageLabel());
            $key = $this->normalize($provider->getPageKey());

            $score = 0;
            if ($normalizedQuery === $key || $normalizedQuery === $label) {
                $score = 1000;
            } elseif (str_starts_with($key, $normalizedQuery) || str_starts_with($label, $normalizedQuery)) {
                $score = 600;
            } elseif (str_contains($key, $normalizedQuery) || str_contains($label, $normalizedQuery)) {
                $score = 300;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $provider->getPageRouteName();
            }
        }

        return $bestScore > 0 ? $best : null;
    }

    /**
     * @param array<int, string> $tokens
     * @return array{match: bool, score: int}
     */
    private function calculateScore(string $normalizedLabel, string $normalizedQuery, array $tokens): array
    {
        if ($normalizedLabel === '') {
            return ['match' => false, 'score' => 0];
        }

        $score = 0;

        if ($normalizedLabel === $normalizedQuery) {
            $score += 2000;
        } elseif (str_starts_with($normalizedLabel, $normalizedQuery)) {
            $score += 1200;
        } elseif (str_contains($normalizedLabel, $normalizedQuery)) {
            $score += 700;
        }

        $positions = [];

        foreach ($tokens as $token) {
            $position = strpos($normalizedLabel, $token);
            if ($position === false) {
                return ['match' => false, 'score' => 0];
            }

            $positions[] = $position;
            $score += 130;
        }

        if (count($positions) > 1) {
            $inOrder = true;
            $lastPosition = -1;
            foreach ($positions as $position) {
                if ($position < $lastPosition) {
                    $inOrder = false;
                    break;
                }
                $lastPosition = $position;
            }

            $score += $inOrder ? 220 : 80;
        }

        return ['match' => true, 'score' => $score];
    }

    private function normalize(string $value): string
    {
        $normalized = trim(mb_strtolower($value));
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);

        if ($ascii !== false) {
            $normalized = $ascii;
        }

        return preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
    }
}
