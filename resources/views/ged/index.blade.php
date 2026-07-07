<x-app-layout>
    <x-slot name="title">Gestion Documentaire</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">GED - Gestion Documentaire</h2>
    </x-slot>

    @push('styles')
    <style>
        .badge-cat-Administratif { background: #E0E7FF; color: #4338CA; }
        .badge-cat-Academique { background: #CCFBF1; color: #0F766E; }
        .badge-cat-Financier { background: #FEF3C7; color: #B45309; }
        .badge-cat-RH { background: #F3E8FF; color: #6B21A8; }
        .badge-cat-Autre { background: #F1F5F9; color: #475569; }

        .btn-chip {
            padding: 6px 12px; font-size: 11px; font-weight: 600;
            border-radius: 9999px; transition: all 0.2s; border: 1px solid transparent;
            cursor: pointer;
        }
        
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4); z-index: 50; display: flex;
            align-items: center; justify-content: center;
        }
        .modal-content {
            background: #fff; border-radius: 16px; width: 100%; max-width: 550px;
            padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            max-height: 90vh; overflow-y: auto;
        }
        
        .f-input {
            width: 100%; border: 1.5px solid #E2E8F0; border-radius: 8px;
            padding: 8px 12px; font-size: 13px; outline: none; transition: border-color 0.15s;
        }
        .f-input:focus { border-color: #4f46e5; }
    </style>
    @endpush

    @php
        $dossiersJson = $dossiers->map(fn($d) => [
            'id' => $d->id,
            'nom' => $d->nom,
            'id_parent' => $d->id_parent,
            'categorie' => $d->categorie,
            'description' => $d->description
        ])->toJson();

        $documentsJson = $documents->map(fn($d) => [
            'id' => $d->id,
            'id_dossier' => $d->id_dossier,
            'nom' => $d->nom,
            'description' => $d->description,
            'categorie' => $d->categorie,
            'url_fichier' => $d->url_fichier,
            'type_fichier' => $d->type_fichier,
            'taille_octets' => (float)$d->taille_octets
        ])->toJson();
    @endphp

    <div x-data="gedPage({{ $dossiersJson }}, {{ $documentsJson }})" class="space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Gestion Documentaire</h1>
                <p class="text-sm text-slate-500 mt-1" x-text="documents.length + ' document(s) au total'"></p>
            </div>
            <div class="flex gap-2">
                <button class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold py-2 px-4 rounded-lg flex items-center gap-1.5 text-xs" @click="openFolderModal()">
                    <i class="ri-folder-add-line"></i> Nouveau Dossier
                </button>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-1.5 text-xs" @click="openDocModal()">
                    <i class="ri-upload-cloud-2-line"></i> Importer Fichier
                </button>
            </div>
        </div>

        <!-- KPI Row -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="ri-folder-shared-line text-lg"></i>
                </div>
                <div>
                    <div class="text-lg font-bold text-indigo-600" x-text="documents.length"></div>
                    <div class="text-[11px] text-slate-400">Total</div>
                </div>
            </div>
            <template x-for="cat in categories" :key="cat">
                <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="'bg-' + getCategoryColor(cat) + '-50 text-' + getCategoryColor(cat) + '-600'">
                        <i class="ri-price-tag-3-line text-lg"></i>
                    </div>
                    <div>
                        <div class="text-lg font-bold" :class="'text-' + getCategoryColor(cat) + '-600'" x-text="documents.filter(d => d.categorie === cat).length"></div>
                        <div class="text-[11px] text-slate-400" x-text="cat"></div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Main split panel -->
        <div class="flex flex-col lg:flex-row gap-6 min-h-[500px]">
            
            <!-- LEFT PANEL: FOLDERS -->
            <div class="w-full lg:w-72 bg-white border border-slate-200 rounded-xl p-4 flex flex-col space-y-4">
                
                <!-- Category Filter Chips -->
                <div class="flex flex-wrap gap-1.5">
                    <button class="btn-chip" :class="filterCategorie === null ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-slate-50 text-slate-600 border-transparent hover:bg-slate-100'" @click="filterCategorie = null">Tous</button>
                    <template x-for="cat in categories" :key="cat">
                        <button class="btn-chip" :class="filterCategorie === cat ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-slate-50 text-slate-600 border-transparent hover:bg-slate-100'" @click="filterCategorie = cat" x-text="cat"></button>
                    </template>
                </div>

                <hr class="border-slate-100">

                <!-- Folder Tree -->
                <div class="flex-1 flex flex-col">
                    <button class="w-full text-left p-2 rounded-lg text-xs font-semibold flex items-center gap-2" :class="selectedDossier === null ? 'bg-indigo-50/50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50'" @click="selectedDossier = null">
                        <i class="ri-folder-2-line text-sm"></i> Tous les documents
                    </button>
                    <div class="overflow-y-auto flex-1 mt-2 space-y-1">
                        <template x-for="f in flatFolders" :key="f.id">
                            <div class="group flex items-center justify-between p-2 rounded-lg text-xs" :style="'padding-left: ' + (8 + f.depth * 16) + 'px;'" :class="selectedDossier === f.id ? 'bg-indigo-50/50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50'">
                                <button class="flex-1 text-left flex items-center gap-2 truncate" @click="selectedDossier = f.id">
                                    <i :class="f.hasChildren ? 'ri-folder-fill text-amber-500' : 'ri-folder-line text-slate-400'"></i>
                                    <span class="truncate" x-text="f.nom"></span>
                                    <span class="text-[10px] text-slate-400" x-text="'(' + countDocs(f.id) + ')'"></span>
                                </button>
                                <div class="hidden group-hover:flex items-center gap-1.5 ml-2">
                                    <button class="text-slate-400 hover:text-slate-600" title="Modifier" @click="openFolderModal(f)"><i class="ri-edit-line"></i></button>
                                    <button class="text-rose-500 hover:text-rose-700" title="Supprimer" @click="deleteFolder(f)"><i class="ri-delete-bin-line"></i></button>
                                </div>
                            </div>
                        </template>
                        <template x-if="flatFolders.length === 0">
                            <p class="text-xs text-slate-400 italic text-center py-6">Aucun dossier.</p>
                        </template>
                    </div>
                </div>

            </div>

            <!-- RIGHT PANEL: DOCUMENTS -->
            <div class="flex-1 bg-white border border-slate-200 rounded-xl p-4 flex flex-col space-y-4">
                
                <!-- Search -->
                <div class="relative">
                    <i class="ri-search-line absolute left-3 top-2.5 text-slate-400 text-sm"></i>
                    <input type="text" x-model="searchQuery" placeholder="Rechercher un document par son nom..." class="f-input pl-9">
                </div>

                <hr class="border-slate-100">

                <!-- Documents List -->
                <div class="flex-1 overflow-y-auto space-y-2">
                    <template x-for="d in filteredDocs()" :key="d.id">
                        <div class="flex items-center justify-between p-3 border border-slate-100 rounded-xl hover:bg-slate-50/20">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center" :class="'bg-' + getFileColor(d.type_fichier) + '-50 text-' + getFileColor(d.type_fichier) + '-600'">
                                    <i class="text-lg" :class="getFileIcon(d.type_fichier)"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-sm" x-text="d.nom"></div>
                                    <div class="text-xs text-slate-400" x-text="d.description || 'Aucune description'"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="'badge-cat-' + d.categorie" x-text="d.categorie"></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold text-slate-600 bg-slate-100" x-text="d.type_fichier.toUpperCase()"></span>
                                <template x-if="d.url_fichier">
                                    <a :href="d.url_fichier" target="_blank" class="w-8 h-8 rounded-lg hover:bg-slate-50 flex items-center justify-center text-blue-600" title="Télécharger">
                                        <i class="ri-download-2-line"></i>
                                    </a>
                                </template>
                                <button class="w-8 h-8 rounded-lg hover:bg-slate-50 flex items-center justify-center text-indigo-600" title="Modifier" @click="openDocModal(d)">
                                    <i class="ri-edit-line"></i>
                                </button>
                                <button class="w-8 h-8 rounded-lg hover:bg-slate-50 flex items-center justify-center text-rose-600" title="Supprimer" @click="deleteDoc(d.id)">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </div>
                    </template>

                    <template x-if="filteredDocs().length === 0">
                        <div class="flex flex-col items-center justify-center py-16 text-slate-400 space-y-2">
                            <i class="ri-cloud-off-line text-4xl text-slate-300"></i>
                            <p class="font-semibold text-sm">Aucun document trouvé</p>
                            <p class="text-xs text-slate-300">Importez un fichier ou modifiez vos filtres.</p>
                        </div>
                    </template>
                </div>

            </div>

        </div>

        <!-- ── MODAL: DOSSIER ── -->
        <div class="modal-overlay" x-show="showFolderModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="font-bold text-slate-800" x-text="isEditFolder ? 'Modifier le dossier' : 'Nouveau Dossier'"></h3>
                    <button @click="showFolderModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-lg"></i></button>
                </div>
                <form @submit.prevent="saveFolder()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nom du dossier *</label>
                        <input type="text" x-model="folderForm.nom" required placeholder="Ex: Contrats 2024" class="f-input">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Dossier parent</label>
                            <select x-model="folderForm.id_parent" class="f-input">
                                <option value="">Aucun parent</option>
                                <template x-for="f in dossiers.filter(d => d.id !== folderForm.id)" :key="f.id">
                                    <option :value="f.id" x-text="f.nom"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Catégorie *</label>
                            <select x-model="folderForm.categorie" required class="f-input">
                                <template x-for="cat in categories" :key="cat">
                                    <option :value="cat" x-text="cat"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Description</label>
                        <textarea x-model="folderForm.description" rows="2" placeholder="Description optionnelle" class="f-input"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-3 mt-4">
                        <button type="button" @click="showFolderModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── MODAL: DOCUMENT ── -->
        <div class="modal-overlay" x-show="showDocModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="font-bold text-slate-800" x-text="isEditDoc ? 'Modifier le document' : 'Importer un Fichier'"></h3>
                    <button @click="showDocModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-lg"></i></button>
                </div>
                <form @submit.prevent="saveDoc()" class="space-y-4" enctype="multipart/form-data">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nom du document *</label>
                        <input type="text" x-model="docForm.nom" required placeholder="Ex: Contrat enseignant" class="f-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Description</label>
                        <textarea x-model="docForm.description" rows="2" placeholder="Description optionnelle" class="f-input"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Catégorie *</label>
                            <select x-model="docForm.categorie" required class="f-input">
                                <template x-for="cat in categories" :key="cat">
                                    <option :value="cat" x-text="cat"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Dossier</label>
                            <select x-model="docForm.id_dossier" class="f-input">
                                <option value="">Aucun dossier</option>
                                <template x-for="f in dossiers" :key="f.id">
                                    <option :value="f.id" x-text="f.nom"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Téléverser un fichier</label>
                        <input type="file" id="file_input" class="f-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Ou saisir un Lien / URL</label>
                        <input type="text" x-model="docForm.url_fichier" placeholder="Ex: https://..." class="f-input">
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-3 mt-4">
                        <button type="button" @click="showDocModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('gedPage', (dossiers, documents) => ({
                dossiers, documents,
                categories: ['Administratif', 'Academique', 'Financier', 'RH', 'Autre'],
                
                selectedDossier: null,
                filterCategorie: null,
                searchQuery: '',

                flatFolders: [],

                // Folder Modals
                showFolderModal: false,
                isEditFolder: false,
                folderForm: { id: null, nom: '', id_parent: '', categorie: 'Administratif', description: '' },

                // Doc Modals
                showDocModal: false,
                isEditDoc: false,
                docForm: { id: null, nom: '', description: '', categorie: 'Administratif', id_dossier: '', url_fichier: '' },

                init() {
                    this.refreshTree();
                },

                refreshTree() {
                    this.flatFolders = this.buildFlatTree(null, 0);
                },

                buildFlatTree(parentId = null, depth = 0) {
                    let result = [];
                    const children = this.dossiers.filter(d => d.id_parent == parentId);
                    children.forEach(folder => {
                        const hasChildren = this.dossiers.some(d => d.id_parent == folder.id);
                        result.push({
                            ...folder,
                            depth: depth,
                            hasChildren: hasChildren
                        });
                        result = result.concat(this.buildFlatTree(folder.id, depth + 1));
                    });
                    return result;
                },

                countDocs(dossierId) {
                    return this.documents.filter(d => d.id_dossier == dossierId).length;
                },

                getCategoryColor(cat) {
                    const colors = {
                        Administratif: 'indigo',
                        Academique: 'teal',
                        Financier: 'amber',
                        RH: 'purple',
                        Autre: 'slate'
                    };
                    return colors[cat] || 'slate';
                },

                getFileIcon(ext) {
                    if (ext === 'pdf') return 'ri-file-pdf-line';
                    if (['doc', 'docx'].includes(ext)) return 'ri-file-word-line';
                    if (['xls', 'xlsx', 'csv'].includes(ext)) return 'ri-file-excel-line';
                    if (['png', 'jpg', 'jpeg', 'gif', 'webp'].includes(ext)) return 'ri-image-line';
                    return 'ri-file-3-line';
                },

                getFileColor(ext) {
                    if (ext === 'pdf') return 'red';
                    if (['doc', 'docx'].includes(ext)) return 'blue';
                    if (['xls', 'xlsx', 'csv'].includes(ext)) return 'green';
                    if (['png', 'jpg', 'jpeg', 'gif', 'webp'].includes(ext)) return 'orange';
                    return 'slate';
                },

                // ── FILTERS ──
                filteredDocs() {
                    const q = this.searchQuery.toLowerCase();
                    return this.documents.filter(d => {
                        if (this.selectedDossier !== null && d.id_dossier != this.selectedDossier) return false;
                        if (this.filterCategorie !== null && d.categorie !== this.filterCategorie) return false;
                        if (q && !d.nom.toLowerCase().includes(q)) return false;
                        return true;
                    });
                },

                // ── CRUD DOSSIERS ──
                resetFolderForm() {
                    this.folderForm = { id: null, nom: '', id_parent: this.selectedDossier || '', categorie: 'Administratif', description: '' };
                },
                openFolderModal(row = null) {
                    this.isEditFolder = !!row;
                    if (row) {
                        this.folderForm = { ...row, id_parent: row.id_parent || '' };
                    } else {
                        this.resetFolderForm();
                    }
                    this.showFolderModal = true;
                },
                async saveFolder() {
                    const url = this.isEditFolder ? `/ged/dossiers/${this.folderForm.id}` : '/ged/dossiers';
                    const method = this.isEditFolder ? 'PUT' : 'POST';
                    try {
                        const r = await fetch(url, {
                            method,
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.folderForm)
                        });
                        const res = await r.json();
                        if (res.success) {
                            alert(res.message);
                            window.location.reload();
                        } else {
                            alert(res.message || 'Erreur');
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },
                async deleteFolder(f) {
                    if (this.countDocs(f.id) > 0) {
                        alert("Impossible de supprimer ce dossier car il contient des fichiers.");
                        return;
                    }
                    if (!confirm(`Supprimer le dossier "${f.nom}" ?`)) return;
                    try {
                        const r = await fetch(`/ged/dossiers/${f.id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        const res = await r.json();
                        if (res.success) {
                            alert(res.message);
                            window.location.reload();
                        } else {
                            alert(res.message);
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },

                // ── CRUD DOCUMENTS ──
                resetDocForm() {
                    this.docForm = { id: null, nom: '', description: '', categorie: 'Administratif', id_dossier: this.selectedDossier || '', url_fichier: '' };
                },
                openDocModal(row = null) {
                    this.isEditDoc = !!row;
                    if (row) {
                        this.docForm = { ...row, id_dossier: row.id_dossier || '' };
                    } else {
                        this.resetDocForm();
                    }
                    this.showDocModal = true;
                },
                async saveDoc() {
                    const url = this.isEditDoc ? `/ged/documents/${this.docForm.id}` : '/ged/documents';
                    
                    // We use FormData to support real file upload
                    const fd = new FormData();
                    fd.append('nom', this.docForm.nom);
                    fd.append('description', this.docForm.description || '');
                    fd.append('categorie', this.docForm.categorie);
                    fd.append('id_dossier', this.docForm.id_dossier || '');
                    fd.append('url_fichier', this.docForm.url_fichier || '');
                    
                    const fileInput = document.getElementById('file_input');
                    if (fileInput && fileInput.files.length > 0) {
                        fd.append('fichier', fileInput.files[0]);
                    }

                    try {
                        const r = await fetch(url, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: fd
                        });
                        const res = await r.json();
                        if (res.success) {
                            alert(res.message);
                            window.location.reload();
                        } else {
                            alert(res.message || 'Erreur');
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },
                async deleteDoc(id) {
                    if (!confirm('Supprimer ce document ?')) return;
                    try {
                        const r = await fetch(`/ged/documents/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        const res = await r.json();
                        if (res.success) {
                            alert(res.message);
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    }
                }

            }));
        });
    </script>
    @endpush

</x-app-layout>
