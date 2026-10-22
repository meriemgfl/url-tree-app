<?php
// app/Http/Controllers/FolderController.php
namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FolderController extends Controller
{
    public function index(): JsonResponse
    {
        // Only root folders, each with their nested children pre-loaded
        $folders = Folder::roots()->with('children.children', 'bookmarks')->get();
        return response()->json($folders);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string',
            'parent_id' => 'nullable|exists:folders,id',
            'keywords' => 'array',
        ]);

        // $folder = Folder::create($data);

        $folder = Folder::firstOrCreate(
            ['name' => $data['name'], 'parent_id' => $data['parent_id'] ?? null],
            ['keywords' => $data['keywords'] ?? []]
        );
        return response()->json($folder, 201);
    }

    public function flat(): JsonResponse
    {
    $folders = Folder::all(); // id, parent_id, name — enough to build a flat indented list client-side

    return response()->json($folders->map(fn ($f) => [
        'id' => $f->id,
        'name' => $f->name,
        'path' => $f->path(),
    ]));
    }

    public function update(Request $request, Folder $folder): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
        ]);

        $folder->update($data);

        return response()->json($folder);
    }

    public function destroy(Folder $folder): JsonResponse
    {
        // Bookmarks and child folders cascade via nullOnDelete/onDelete in the migrations,
        // so children become unfiled/orphaned rather than being force-deleted — safer default.
        $folder->delete();

        return response()->json(null, 204);
    }
}