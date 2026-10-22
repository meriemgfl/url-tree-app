<?php
// app/Http/Controllers/BookmarkController.php
namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Folder;
use App\Services\FolderMatcherService;
use App\Services\UrlScraperService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookmarkController extends Controller
{
    public function __construct(
        private UrlScraperService $scraper,
        private FolderMatcherService $matcher,
    ) {}

    /**
     * Step 1 of the flow: scrape the URL and suggest folders.
     * Does NOT save anything yet — this is a preview.
     */
    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'url' => 'required|url',
        ]);

        try {
            $scraped = $this->scraper->scrape($data['url']);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $suggestions = $this->matcher->suggest($scraped['keywords']);

        return response()->json([
            'url' => $data['url'],
            'title' => $scraped['title'],
            'meta_description' => $scraped['meta_description'],
            'keywords' => $scraped['keywords'],
            'suggestions' => $suggestions->map(fn ($s) => [
                'folder_id' => $s['folder']->id,
                'folder_name' => $s['folder']->name,
                'folder_path' => $s['folder']->path(),
                'score' => round($s['score'], 3),
            ]),
        ]);
    }

    /**
     * Step 2 of the flow: user has confirmed a folder (or a new one),
     * actually persist the bookmark.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'url' => 'required|url',
            'title' => 'required|string',
            'meta_description' => 'nullable|string',
            'keywords' => 'array',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $bookmark = Bookmark::create($data);

        // Only "learn" if the bookmark actually landed in a folder
        if (!empty($data['folder_id'])) {
            Folder::find($data['folder_id'])->absorbKeywords($data['keywords'] ?? []);
        }

        return response()->json($bookmark->load('folder'), 201);
    }

    /**
     * List bookmarks — optionally scoped to a folder.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Bookmark::query();

        if ($request->has('folder_id')) {
            $query->where('folder_id', $request->input('folder_id'));
        }

        return response()->json($query->latest()->get());
    }

    public function destroy(Bookmark $bookmark): JsonResponse
    {
        $bookmark->delete();
        return response()->json(null, 204);
    }

    public function update(Request $request, Bookmark $bookmark): JsonResponse
    {
        $data = $request->validate([
            'title' => 'sometimes|string',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $bookmark->update($data);

        return response()->json($bookmark);
    }
}