@extends('layout')

@section('content')
<div class="flex h-screen">
    <!-- Sidebar: folder tree -->
     <aside class="w-72 border-r bg-white p-4 overflow-y-auto" x-data="folderTree()" x-init="init()"> <!--prev x-init="load()" -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-lg">Folders</h2>
            <button @click="showAddModal = true"
                    class="text-sm bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                + Add URL
            </button>
        </div>

        <template x-for="folder in folders" :key="folder.id">
            <div x-html="renderFolder(folder)"></div>
        </template>
    </aside>

    <!-- Main area: selected folder's bookmarks -->
     <main class="flex-1 p-6 overflow-y-auto" x-data="bookmarkList()" x-init="init()">
    <!-- <main class="flex-1 p-6 overflow-y-auto" x-data="bookmarkList()"> -->
        <h1 class="text-xl font-semibold mb-4">Bookmarks</h1>
        <div x-show="bookmarks.length === 0" class="text-gray-400">
            Select a folder to see its bookmarks.
        </div>
        <template x-for="bookmark in bookmarks" :key="bookmark.id">
            <div class="border rounded p-3 mb-2 hover:bg-gray-50">
                <a :href="bookmark.url" target="_blank" class="font-medium text-blue-700" x-text="bookmark.title"></a>
                <p class="text-sm text-gray-500" x-text="bookmark.url"></p>
            </div>
        </template>
   

    <template x-for="bookmark in bookmarks" :key="bookmark.id">
    <div class="border rounded p-3 mb-2 hover:bg-gray-50 flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <a :href="bookmark.url" target="_blank" class="font-medium text-blue-700 block truncate" x-text="bookmark.title"></a>
            <p class="text-sm text-gray-500 truncate" x-text="bookmark.url"></p>
        </div>
        <div class="flex gap-2 shrink-0">
            <select @change="moveBookmark(bookmark.id, $event.target.value)"
                    class="text-xs border rounded px-1 py-0.5">
                <option value="">Move to...</option>
                <template x-for="f in allFolders" :key="f.id">
                    <option :value="f.id" x-text="f.path"></option>
                </template>
            </select>
            <button @click="deleteBookmark(bookmark.id)" class="text-red-500 hover:text-red-700 text-sm">
                🗑️
            </button>
        </div>
    </div>
</template>
 </main>

    @include('partials.add-url-modal')
</div>
<script>
    // function folderTree() {
    //     return {
    //         folders: [],
    //         async load() {
    //             const res = await fetch('/api/folders');
    //             this.folders = await res.json();
    //         },
function folderTree() {
    return {
        folders: [],

        init() {
            this.load();
            window.addEventListener('bookmark-saved', () => this.load());

            window.addEventListener('folder-rename', async (e) => {
                const newName = prompt('Rename folder:', e.detail.name);
                if (newName && newName.trim() && newName !== e.detail.name) {
                    await fetch(`/api/folders/${e.detail.id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ name: newName.trim() }),
                    });
                    this.load();
                }
            });

            window.addEventListener('folder-delete', async (e) => {
                if (confirm('Delete this folder? Bookmarks inside will become unfiled, and sub-folders will move to the top level.')) {
                    await fetch(`/api/folders/${e.detail}`, { method: 'DELETE' });
                    this.load();
                }
            });
        },

        async load() {
            const res = await fetch('/api/folders');
            this.folders = await res.json();
        },

        renderFolder(folder, depth = 0) {
            const indent = depth * 16;
            let html = `<div style="padding-left: ${indent}px" class="py-1 group flex items-center justify-between">
                <button class="text-sm hover:text-blue-600 text-left flex-1"
                        onclick="window.dispatchEvent(new CustomEvent('folder-selected', { detail: ${folder.id} }))">
                    📁 ${folder.name}
                </button>
                <span class="hidden group-hover:flex gap-1 text-xs">
                    <button class="text-gray-400 hover:text-blue-600"
                            onclick="window.dispatchEvent(new CustomEvent('folder-rename', { detail: { id: ${folder.id}, name: '${folder.name.replace(/'/g, "\\'")}' } }))">
                        ✏️
                    </button>
                    <button class="text-gray-400 hover:text-red-600"
                            onclick="window.dispatchEvent(new CustomEvent('folder-delete', { detail: ${folder.id} }))">
                        🗑️
                    </button>
                </span>
            </div>`;

            if (folder.children && folder.children.length) {
                folder.children.forEach(child => {
                    html += this.renderFolder(child, depth + 1);
                });
            }

            return html;
        },
    };
}

function bookmarkList() {
    return {
        bookmarks: [],
        allFolders: [],
        currentFolderId: null,

        init() {
            window.addEventListener('folder-selected', (e) => this.loadFolder(e.detail));
            this.loadAllFolders();
        },

        async loadAllFolders() {
            const res = await fetch('/api/folders/flat');
            this.allFolders = await res.json();
        },

        async loadFolder(folderId) {
            this.currentFolderId = folderId;
            const res = await fetch(`/api/bookmarks?folder_id=${folderId}`);
            this.bookmarks = await res.json();
        },

        async moveBookmark(bookmarkId, newFolderId) {
            if (!newFolderId) return;
            await fetch(`/api/bookmarks/${bookmarkId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ folder_id: newFolderId }),
            });
            this.loadFolder(this.currentFolderId);
        },

        async deleteBookmark(bookmarkId) {
            if (confirm('Delete this bookmark?')) {
                await fetch(`/api/bookmarks/${bookmarkId}`, { method: 'DELETE' });
                this.loadFolder(this.currentFolderId);
            }
        },
    };
}
</script>
@endsection
