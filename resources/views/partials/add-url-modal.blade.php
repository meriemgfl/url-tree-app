<div x-show="showAddModal"
     x-cloak
     class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
     x-data="addUrlForm()">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Add URL</h2>

        <template x-if="step === 'input'">
            <div>
                <input x-model="url" type="url" placeholder="https://example.com"
                       class="w-full border rounded px-3 py-2 mb-3">
                <div class="flex justify-end gap-2">
                    <button @click="showAddModal = false" class="px-4 py-2 text-gray-600">Cancel</button>
                    <button @click="fetchPreview()" :disabled="loading"
                            class="px-4 py-2 bg-blue-600 text-white rounded">
                        <span x-show="!loading">Scan URL</span>
                        <span x-show="loading">Scanning...</span>
                    </button>
                </div>
                <p x-show="error" x-text="error" class="text-red-600 text-sm mt-2"></p>
            </div>
        </template>

        <!-- <template x-if="step === 'confirm'">
            <div>
                <p class="font-medium" x-text="preview.title"></p>
                <p class="text-sm text-gray-500 mb-3" x-text="preview.meta_description"></p>

                <p class="text-sm font-medium mb-1">Suggested folder:</p>
                <div class="space-y-1 mb-4">
                    <template x-for="s in preview.suggestions" :key="s.folder_id">
                        <label class="flex items-center gap-2 text-sm border rounded px-2 py-1 cursor-pointer">
                            <input type="radio" name="folder" :value="s.folder_id" x-model="selectedFolderId">
                            <span x-text="s.folder_path"></span>
                            <span class="text-gray-400" x-text="'(' + Math.round(s.score * 100) + '% match)'"></span>
                        </label>
                    </template>
                    <p x-show="preview.suggestions.length === 0" class="text-sm text-gray-400">
                        No matching folder found — this will be saved unfiled for now.
                    </p>
                </div>

                <div class="flex justify-end gap-2">
                    <button @click="step = 'input'" class="px-4 py-2 text-gray-600">Back</button>
                    <button @click="save()" :disabled="loading"
                            class="px-4 py-2 bg-blue-600 text-white rounded">
                        Save
                    </button>
                </div>
            </div>
        </template> -->
        <template x-if="step === 'confirm'">
    <div>
        <p class="font-medium" x-text="preview.title"></p>
        <p class="text-sm text-gray-500 mb-3" x-text="preview.meta_description"></p>

        <p class="text-sm font-medium mb-1">Choose a folder:</p>
        <div class="space-y-1 mb-3">
            <template x-for="s in preview.suggestions" :key="s.folder_id">
                <label class="flex items-center gap-2 text-sm border rounded px-2 py-1 cursor-pointer">
                    <input type="radio" name="folderChoice" :value="s.folder_id"
                           @change="selectedFolderId = s.folder_id; creatingNew = false">
                    <span x-text="s.folder_path"></span>
                    <span class="text-gray-400" x-text="'(' + Math.round(s.score * 100) + '% match)'"></span>
                </label>
            </template>

            <p x-show="preview.suggestions.length === 0" class="text-sm text-gray-400">
                No matching folder found.
            </p>

            <!-- Create new folder option -->
            <label class="flex items-center gap-2 text-sm border rounded px-2 py-1 cursor-pointer border-dashed">
                <input type="radio" name="folderChoice" value="new"
                       @change="creatingNew = true; selectedFolderId = null">
                <span>+ Create new folder</span>
            </label>
        </div>

        <!-- New folder inputs, only shown when that option is picked -->
        <div x-show="creatingNew" x-cloak class="mb-4 pl-2 border-l-2 border-gray-200 space-y-2">
            <input x-model="newFolderName" type="text" placeholder="Folder name"
                   class="w-full border rounded px-3 py-2 text-sm">

            <select x-model="newFolderParentId" class="w-full border rounded px-3 py-2 text-sm">
                <option value="">— No parent (top level) —</option>
                <template x-for="f in allFolders" :key="f.id">
                    <option :value="f.id" x-text="f.path"></option>
                </template>
            </select>
        </div>

        <div class="flex justify-end gap-2">
            <button @click="step = 'input'" class="px-4 py-2 text-gray-600">Back</button>
            <button @click="save()" :disabled="loading || !canSave"
                    class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
                Save
            </button>
        </div>
    </div>
</template>
    </div>
</div>

<!-- <script>
    function addUrlForm() {
        return {
            step: 'input',
            url: '',
            preview: null,
            selectedFolderId: null,
            loading: false,
            error: null,

            async fetchPreview() {
                this.loading = true;
                this.error = null;
                try {
                    const res = await fetch('/api/bookmarks/preview', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ url: this.url }),
                    });
                    if (!res.ok) throw new Error('Could not fetch that URL');
                    this.preview = await res.json();
                    this.selectedFolderId = this.preview.suggestions[0]?.folder_id ?? null;
                    this.step = 'confirm';
                } catch (e) {
                    this.error = e.message;
                } finally {
                    this.loading = false;
                }
            },

            async save() {
                this.loading = true;
                try {
                    await fetch('/api/bookmarks', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            url: this.preview.url,
                            title: this.preview.title,
                            meta_description: this.preview.meta_description,
                            keywords: this.preview.keywords,
                            folder_id: this.selectedFolderId,
                        }),
                    });
                    this.showAddModal = false;
                    this.step = 'input';
                    this.url = '';
                    window.dispatchEvent(new CustomEvent('bookmark-saved'));
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script> -->
<script>
    function addUrlForm() {
        return {
            step: 'input',
            url: '',
            preview: null,
            selectedFolderId: null,
            creatingNew: false,
            newFolderName: '',
            newFolderParentId: '',
            allFolders: [],
            loading: false,
            error: null,

            get canSave() {
                if (this.creatingNew) return this.newFolderName.trim().length > 0;
                return this.selectedFolderId !== null;
            },

            async fetchPreview() {
                this.loading = true;
                this.error = null;
                try {
                    const res = await fetch('/api/bookmarks/preview', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ url: this.url }),
                    });
                    if (!res.ok) throw new Error('Could not fetch that URL');
                    this.preview = await res.json();

                    // Pre-select the top suggestion if one exists, otherwise default to "create new"
                    if (this.preview.suggestions.length > 0) {
                        this.selectedFolderId = this.preview.suggestions[0].folder_id;
                        this.creatingNew = false;
                    } else {
                        this.creatingNew = true;
                    }

                    // Load full folder list for the parent dropdown
                    const foldersRes = await fetch('/api/folders/flat');
                    this.allFolders = await foldersRes.json();

                    this.step = 'confirm';
                } catch (e) {
                    this.error = e.message;
                } finally {
                    this.loading = false;
                }
            },

            async save() {
                this.loading = true;
                try {
                    let folderId = this.selectedFolderId;

                    // If creating a new folder, do that first, then use its id
                    if (this.creatingNew) {
                        const folderRes = await fetch('/api/folders', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                name: this.newFolderName,
                                parent_id: this.newFolderParentId || null,
                                keywords: this.preview.keywords, // new folder starts with this bookmark's keywords as its identity
                            }),
                        });
                        const newFolder = await folderRes.json();
                        folderId = newFolder.id;
                    }

                    await fetch('/api/bookmarks', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            url: this.preview.url,
                            title: this.preview.title,
                            meta_description: this.preview.meta_description,
                            keywords: this.preview.keywords,
                            folder_id: folderId,
                        }),
                    });

                    // Reset and close
                    this.showAddModal = false;
                    this.step = 'input';
                    this.url = '';
                    this.creatingNew = false;
                    this.newFolderName = '';
                    this.newFolderParentId = '';

                    // Tell the sidebar tree to reload, since a new folder may now exist
                    window.dispatchEvent(new CustomEvent('bookmark-saved'));
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>