<x-app-layout>
    <x-slot name="title">Parcours Scolaire</x-slot>
    
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px);
            z-index: 50; display: flex; align-items: center; justify-content: center;
        }
        .modal-content {
            background: #F8FAFC; border-radius: 24px; width: 100%; max-width: 500px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
            max-height: 92vh; overflow: hidden; display: flex; flex-direction: column;
            border: 1px solid #E2E8F0;
        }
        .f-input {
            width: 100%; border: 1px solid #CBD5E1; border-radius: 12px;
            padding: 10px 14px; font-size: 13px; outline: none; transition: all 0.2s;
            background-color: #FFFFFF; color: #334155;
        }
        .f-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(142, 29, 81, 0.15); }
        .action-btn {
            width: 32px; height: 32px; border-radius: 8px; display: inline-flex;
            align-items: center; justify-content: center; transition: all 0.2s;
        }
        .action-btn:hover { background: #E2E8F0; }
        
        .student-item {
            cursor: pointer; padding: 12px 16px; border-radius: 14px;
            transition: all 0.2s; display: flex; align-items: center; gap: 12px;
        }
        .student-item:hover { background-color: #F1F5F9; }
        .student-item.active { background-color: #E2E8F0; }

        .btn-burgundy {
            background-color: var(--primary); color: #FFFFFF; font-weight: 600;
            padding: 10px 20px; border-radius: 10px; transition: all 0.2s;
        }
        .btn-burgundy:hover { background-color: var(--primary); }
    </style>
    @endpush

    @php
        $parcoursJson = $parcours->map(fn($p) => [
            'id' => $p->id,
            'id_etudiant' => $p->id_etudiant,
            'etablissement' => $p->etablissement,
            'classe' => $p->classe,
            'annee_scolaire' => $p->annee_scolaire,
            'moyenne_generale' => (float)$p->moyenne_generale,
            'decision' => $p->decision
        ])->toJson();

        $etudiantsJson = $etudiants->map(fn($e) => [
            'id' => $e->id,
            'nom' => $e->nom,
            'prenom' => $e->prenom,
            'matricule' => $e->matricule,
            'url_photo' => $e->url_photo
        ])->toJson();

        $inscriptionsJson = $inscriptions->map(fn($i) => [
            'id' => $i->id,
            'id_etudiant' => $i->id_etudiant,
            'annee' => $i->annee ? $i->annee->libelle : '—',
            'classe' => $i->classe ? $i->classe->libelle : '—',
            'niveau' => $i->niveau ? $i->niveau->libelle : '—',
            'filiere' => $i->filiere ? $i->filiere->libelle : '—',
            'decision' => 'Admis' // Fallback decision
        ])->toJson();
    @endphp

    <div x-data="parcoursPage({{ $parcoursJson }}, {{ $etudiantsJson }}, {{ $inscriptionsJson }})" class="flex h-[80vh] gap-6">

        <!-- Left Pane: Students List -->
        <div class="w-80 bg-white border border-slate-200 rounded-2xl flex flex-col overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 space-y-2">
                <span class="text-sm font-bold text-slate-700">Étudiants</span>
                <input type="text" x-model="searchQuery" placeholder="Rechercher..." class="f-input pl-8 bg-slate-50" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w22.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2394A3B8%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><circle cx=%2211%22 cy=%2211%22 r=%228%22/><line x1=%2221%22 y1=%2221%22 x2=%2216.65%22 y2=%2216.65%22/></svg>'); background-repeat: no-repeat; background-position: 10px 12px;">
            </div>

            <div class="flex-1 overflow-y-auto p-2 space-y-1">
                <template x-for="e in filteredEtudiants()" :key="e.id">
                    <div class="student-item" :class="selectedStudent && selectedStudent.id === e.id ? 'active' : ''" @click="selectStudent(e)">
                        <template x-if="e.url_photo">
                            <img :src="e.url_photo" class="w-9 h-9 rounded-full object-cover border border-slate-200">
                        </template>
                        <template x-if="!e.url_photo">
                            <div class="w-9 h-9 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm" x-text="e.nom.charAt(0)"></div>
                        </template>
                        <div>
                            <div class="font-bold text-xs text-slate-800" x-text="e.nom + ' ' + e.prenom"></div>
                            <div class="text-[10px] text-slate-400 font-mono" x-text="e.matricule"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Right Pane: Timeline and details -->
        <div class="flex-1 bg-white border border-slate-200 rounded-2xl flex flex-col overflow-hidden shadow-sm">
            <template x-if="!selectedStudent">
                <div class="flex-1 flex flex-col items-center justify-center space-y-4 text-center">
                    <div class="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center">
                        <i class="ri-graduation-cap-line text-slate-300 text-4xl"></i>
                    </div>
                    <div>
                        <h4 class="text-slate-600 font-bold text-sm">Sélectionnez un étudiant</h4>
                        <p class="text-xs text-slate-400 mt-1">pour voir son parcours scolaire complet</p>
                    </div>
                </div>
            </template>

            <template x-if="selectedStudent">
                <div class="flex-1 flex flex-col overflow-hidden">
                    <!-- Right header -->
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-slate-800 text-lg" x-text="selectedStudent.nom + ' ' + selectedStudent.prenom"></h3>
                            <span class="text-xs text-slate-400 font-mono" x-text="selectedStudent.matricule"></span>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <button class="bg-[var(--primary)] hover:bg-[var(--primary)] text-white text-xs font-bold py-2 px-4 rounded-xl flex items-center gap-1 shadow-sm" @click="openParcoursModal()">
                                <i class="ri-add-line"></i> Nouveau parcours
                            </button>
                        </div>
                    </div>

                    <!-- Tabs switcher -->
                    <div class="flex border-b border-slate-100 bg-slate-50/50 p-2 gap-2 text-xs">
                        <button class="px-4 py-2 rounded-lg font-bold transition-all" :class="activeTab === 'interne' ? 'bg-[var(--primary)] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'" @click="activeTab = 'interne'">
                            Parcours Interne
                        </button>
                        <button class="px-4 py-2 rounded-lg font-bold transition-all" :class="activeTab === 'externe' ? 'bg-[var(--primary)] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'" @click="activeTab = 'externe'">
                            Parcours Externe
                        </button>
                    </div>

                    <!-- Tabs Content -->
                    <div class="flex-1 overflow-y-auto p-6">
                        
                        <!-- Internal course history -->
                        <div x-show="activeTab === 'interne'" class="space-y-4">
                            <template x-for="i in studentInscs" :key="i.id">
                                <div class="relative pl-6 border-l-2 border-slate-100 pb-4">
                                    <div class="absolute -left-1.5 top-1.5 w-3 h-3 rounded-full bg-indigo-600"></div>
                                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono font-bold text-xs text-indigo-600" x-text="i.annee"></span>
                                            <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold" x-text="i.decision"></span>
                                        </div>
                                        <div class="font-bold text-slate-800 text-sm" x-text="i.classe"></div>
                                        <div class="text-xs text-slate-500" x-text="i.filiere"></div>
                                    </div>
                                </div>
                            </template>
                            <template x-if="studentInscs.length === 0">
                                <p class="text-xs text-slate-400 italic">Aucune inscription interne enregistrée.</p>
                            </template>
                        </div>

                        <!-- External course history -->
                        <div x-show="activeTab === 'externe'" class="space-y-4">
                            <template x-for="p in studentParcours" :key="p.id">
                                <div class="relative pl-6 border-l-2 border-slate-100 pb-4">
                                    <div class="absolute -left-1.5 top-1.5 w-3 h-3 rounded-full bg-[var(--primary)]"></div>
                                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono font-bold text-xs text-slate-600" x-text="p.annee_scolaire"></span>
                                            <div class="flex items-center gap-1">
                                                <button class="action-btn text-blue-600" @click="openParcoursModal(p)"><i class="ri-edit-line"></i></button>
                                                <button class="action-btn text-red-600" @click="deleteParcours(p.id)"><i class="ri-delete-bin-line"></i></button>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-sm" x-text="p.etablissement"></div>
                                            <div class="text-xs text-slate-500" x-text="'Classe : ' + p.classe"></div>
                                        </div>
                                        <div class="flex items-center justify-between pt-2 border-t border-slate-200/50">
                                            <span class="text-xs font-semibold text-slate-700" x-text="'Moyenne : ' + p.moyenne_generale"></span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="p.decision === 'Admis' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'" x-text="p.decision"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template x-if="studentParcours.length === 0">
                                <div class="text-center py-12 space-y-2">
                                    <i class="ri-timeline-view text-slate-300 text-3xl"></i>
                                    <p class="text-xs text-slate-400 italic">Aucun parcours externe enregistré.</p>
                                </div>
                            </template>
                        </div>

                    </div>
                </div>
            </template>
        </div>

        <!-- ── MODAL: PARCOURS EXTERNE FORM ── -->
        <div class="modal-overlay" x-show="showParcoursModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-lg" x-text="isEdit ? 'Modifier parcours' : 'Nouveau parcours scolaire'"></h3>
                    <button @click="showParcoursModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-xl"></i></button>
                </div>

                <form @submit.prevent="saveParcours()" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Établissement *</label>
                        <input type="text" x-model="form.etablissement" required placeholder="Nom de l'établissement antérieur" class="f-input">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Classe *</label>
                            <input type="text" x-model="form.classe" required placeholder="Ex: Terminale S" class="f-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Année scolaire *</label>
                            <input type="text" x-model="form.annee_scolaire" required placeholder="Ex: 2022-2023" class="f-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Moyenne générale *</label>
                            <input type="number" step="0.01" x-model="form.moyenne_generale" required placeholder="Ex: 14.50" class="f-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Décision *</label>
                            <select x-model="form.decision" required class="f-input">
                                <option value="Admis">Admis</option>
                                <option value="Exclu">Exclu</option>
                                <option value="Redoublant">Redoublant</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="showParcoursModal = false" class="text-sm font-bold text-red-600 hover:text-red-800">Annuler</button>
                        <button type="submit" class="btn-burgundy">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('parcoursPage', (parcours, etudiants, inscriptions) => ({
                parcours, etudiants, inscriptions,
                searchQuery: '',
                selectedStudent: null,
                activeTab: 'interne',
                showParcoursModal: false,
                isEdit: false,

                studentParcours: [],
                studentInscs: [],

                form: { id: null, id_etudiant: '', etablissement: '', classe: '', annee_scolaire: '', moyenne_generale: '', decision: 'Admis' },

                filteredEtudiants() {
                    const q = this.searchQuery.toLowerCase();
                    return this.etudiants.filter(e => {
                        return !q || e.nom.toLowerCase().includes(q) || e.prenom.toLowerCase().includes(q) || e.matricule.toLowerCase().includes(q);
                    });
                },

                selectStudent(etu) {
                    this.selectedStudent = etu;
                    this.studentParcours = this.parcours.filter(p => p.id_etudiant == etu.id);
                    this.studentInscs = this.inscriptions.filter(i => i.id_etudiant == etu.id);
                },

                openParcoursModal(p = null) {
                    this.isEdit = !!p;
                    if (p) {
                        this.form = { ...p };
                    } else {
                        this.form = { id: null, id_etudiant: this.selectedStudent.id, etablissement: '', classe: '', annee_scolaire: '', moyenne_generale: '', decision: 'Admis' };
                    }
                    this.showParcoursModal = true;
                },

                async saveParcours() {
                    const url = this.isEdit ? `/etudiants/parcours/${this.form.id}` : '/etudiants/parcours';
                    const method = this.isEdit ? 'PUT' : 'POST';
                    try {
                        const r = await fetch(url, {
                            method,
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.form)
                        });
                        const res = await r.json();
                        if (res.success) {
                            alert(res.message);
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },

                async deleteParcours(id) {
                    if (!confirm('Supprimer ce parcours ?')) return;
                    try {
                        const r = await fetch(`/etudiants/parcours/${id}`, {
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
