<?php
// app/Services/FolderMatcherService.php
namespace App\Services;

use App\Models\Folder;
use Illuminate\Support\Collection;

class FolderMatcherService
{
    /**
     * Compare a set of keywords against every folder in the tree,
     * return ranked suggestions (best match first).
     */
    public function suggest(array $keywords, int $limit = 3): Collection
    {
        $keywords = $this->normalize($keywords);

        return Folder::all()
            ->map(function (Folder $folder) use ($keywords) {
                // $score = $this->jaccardSimilarity(
                $score = $this->overlapCoefficient(
                    $keywords,
                    $this->normalize($folder->keywords ?? [])
                );

                return [
                    'folder' => $folder,
                    'score' => $score,
                ];
            })
            ->filter(fn ($result) => $result['score'] > 0) // no point suggesting a 0% match
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    /**
     * Jaccard similarity: size of intersection / size of union.
     * Returns a value between 0 (no overlap) and 1 (identical sets).
     */
    // private function jaccardSimilarity(array $a, array $b): float
    // {
    //     if (empty($a) || empty($b)) {
    //         return 0.0;
    //     }

    //     $intersection = count(array_intersect($a, $b));
    //     $union = count(array_unique(array_merge($a, $b)));

    //     return $union > 0 ? $intersection / $union : 0.0;
    // }

        private function overlapCoefficient(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }

        $intersection = count(array_intersect($a, $b));
        $smaller = min(count($a), count($b));

        return $smaller > 0 ? $intersection / $smaller : 0.0;
    }

    /**
     * Lowercase + dedupe, so comparisons aren't case-sensitive
     * and don't get skewed by accidental duplicates.
     */
    private function normalize(array $keywords): array
    {
        return array_values(array_unique(array_map('strtolower', $keywords)));
    }
}