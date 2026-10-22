<?php
// app/Services/UrlScraperService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class UrlScraperService
{
    public function scrape(string $url): array
    {
        $response = Http::withOptions([
            'verify' => 'C:\\php\\cacert.pem'])
            ->timeout(10)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; UrlTreeApp/1.0)'])
            ->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException("Failed to fetch URL: HTTP {$response->status()}");
        }

        $crawler = new Crawler($response->body());

        $title = $this->extractTitle($crawler);
        $metaDescription = $this->extractMeta($crawler, 'description');
        $metaKeywords = $this->extractMeta($crawler, 'keywords');
        $headings = $this->extractHeadings($crawler);

        $keywords = $this->buildKeywordSet($title, $metaDescription, $metaKeywords, $headings);

        return [
            'title' => $title,
            'meta_description' => $metaDescription,
            'keywords' => $keywords,
        ];
    }

    private function extractTitle(Crawler $crawler): ?string
    {
        $node = $crawler->filter('title')->first();
        return $node->count() > 0 ? trim($node->text()) : null;
    }

    private function extractMeta(Crawler $crawler, string $name): ?string
    {
        $node = $crawler->filter("meta[name=\"{$name}\"]")->first();
        return $node->count() > 0 ? trim($node->attr('content') ?? '') : null;
    }

    private function extractHeadings(Crawler $crawler): string
    {
        return $crawler->filter('h1, h2')
            ->each(fn (Crawler $node) => trim($node->text()))
            ? implode(' ', $crawler->filter('h1, h2')->each(fn (Crawler $node) => trim($node->text())))
            : '';
    }

    private function buildKeywordSet(?string $title, ?string $description, ?string $metaKeywords, string $headings): array
    {
        // If the page explicitly declares keywords, trust those first
        $explicit = [];
        if ($metaKeywords) {
            $explicit = array_map('trim', explode(',', $metaKeywords));
        }

        // Otherwise derive from title + description + headings
        $text = implode(' ', array_filter([$title, $description, $headings]));
        $derived = $this->extractKeywordsFromText($text);

        // Merge, explicit keywords first, dedupe, cap to a reasonable count
        $combined = array_values(array_unique(array_merge($explicit, $derived)));

        return array_slice($combined, 0, 15);
    }

    private function extractKeywordsFromText(string $text): array
    {
        $stopwords = $this->stopwords();

        $words = str_word_count(strtolower($text), 1);

        $words = array_filter($words, fn ($word) =>
            strlen($word) > 2 && !in_array($word, $stopwords)
        );

        // Frequency count — words appearing more often rank higher
        $frequency = array_count_values($words);
        arsort($frequency);

        return array_keys($frequency);
    }

    private function stopwords(): array
    {
        return [
            'the', 'and', 'for', 'are', 'but', 'not', 'you', 'all', 'can',
            'her', 'was', 'one', 'our', 'out', 'day', 'get', 'has', 'him',
            'his', 'how', 'man', 'new', 'now', 'old', 'see', 'two', 'way',
            'who', 'boy', 'did', 'its', 'let', 'put', 'say', 'she', 'too',
            'use', 'with', 'this', 'that', 'from', 'have', 'more', 'will',
            'your', 'what', 'when', 'make', 'like', 'time', 'just', 'know',
            'take', 'into', 'year', 'good', 'some', 'could', 'them', 'than',
            'then', 'look', 'only', 'come', 'over', 'think', 'also', 'back',
        ];
    }
}