<x-app-layout>
    <x-slot name="title">Liste des Boursiers</x-slot>
    
    @push('styles')
    <style>
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4); z-index: 50; display: flex;
            align-items: center; justify-content: center;
        }
        .modal-content {
            background: #fff; border-radius: 16px; width: 100%; max-width: 500px;
            padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .f-input {
            width: 100%; border: 1.5px solid #E2E8F0; border-radius: 8px;
            padding: 8px 12px; font-size: 13px; outline: none; transition: border-color 0.15s;
        }
        .f-input:focus { border-color: #5A67D8; }
        .action-btn {
            width: 28px; height: 28px; border-radius: 6px; display: inline-flex;
            align-items: center; justify-content: center; transition: all 0.15s;
        }
        .action-btn:hover { background: #F1F5F9; }
    </style>
    @endpush

    @php
        $boursiersJson = $boursiers->map(fn($b) => [
            'id' => $b->id,
            'etudiant_lib' => $b->etudiant ? $b->etudiant->nom . ' ' . $b->etudiant->prenom : '—',
            'matricule' => $b->etudiant ? $b->etudiant->matricule : '—',
            'bourse_lib' => $b->bourse ? $b->bourse->libelle : '—',
            'valeur' => $b->bourse ? $b->bourse->valeur : 0,
            'type_bourse' => $b->bourse ? $b->bourse->type_bourse : '—',
            'annee_lib' => $b->anneeScolaire ? $b->anneeScolaire->libelle : '—'
        ])->toJson();

        $etudiantsJson = $etudiants->map(fn($e) => ['id' => $e->id, 'nom' => $e->nom, 'prenom' => $e->prenom])->toJson();
        $boursesJson = $bourses->map(fn($b) => ['id' => $b->id, 'libelle' => $b->libelle])->toJson();
        $anneesJson = $annees->map(fn($a) => ['id' => $a->id, 'libelle' => $a->libelle])->toJson();
    @endphp

    <div x-data="boursiersPage({{ $boursiersJson }}, {{ $etudiantsJson }}, {{ $boursesJson }}, {{ $anneesJson }})" class="space-y-6">

        <!-- Title Row -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Liste des Boursiers</h1>
                <p class="text-sm text-slate-500 mt-1" x-text="boursiers.length + ' bourse(s) attribuée(s)'"></p>
            </div>
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-1.5 text-xs" @click="showFormModal = true">
                <i class="ri-add-line"></i> Attribuer Bourse
            </button>
        </div>

        <!-- Table Panel -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100">
                <input type="text" x-model="searchQuery" placeholder="Rechercher par étudiant, matricule, bourse..." class="f-input max-w-xs pl-8 bg-slate-50" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w22.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2394A3B8%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><circle cx=%2211%22 cy=%2211%22 r=%228%22/><line x1=%2221%22 y1=%2221%22 x2=%2216.65%22 y2=%2216.65%22/></svg>'); background-repeat: no-repeat; background-position: 10px 12px;">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="p-4">MATRICULE</th>
                            <th class="p-4">ÉTUDIANT</th>
                            <th class="p-4">BOURSES</th>
                            <th class="p-4">ANNÉE SCOLAIRE</th>
                            <th class="p-4">VALEUR / DÉDUCTION</th>
                            <th class="p-4 text-right">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        <template x-for="b in filteredBoursiers()" :key="b.id">
                            <tr class="hover:bg-slate-50/20">
                                <td class="p-4 font-mono font-bold text-indigo-600" x-text="b.matricule"></td>
                                <td class="p-4 font-bold text-slate-800" x-text="b.etudiant_lib"></td>
                                <td class="p-4 text-slate-600" x-text="b.bourse_lib"></td>
                                <td class="p-4 text-slate-500" x-text="b.annee_lib"></td>
                                <td class="p-4 text-slate-700 font-bold" x-text="b.valeur + ' ' + (b.type_bourse === 'Taux' ? '%' : 'FCFA')"></td>
                                <td class="p-4 text-right">
                                    <button class="action-btn text-red-600" title="Retirer la bourse" @click="deleteBourse(b.id)"><i class="ri-delete-bin-line"></i></button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="filteredBoursiers().length === 0">
                            <tr>
                                <td colspan="6" class="text-center py-8 text-slate-400 italic">Aucune bourse attribuée.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── MODAL: ATTRIBUTION BOURSE ── -->
        <div class="modal-overlay" x-show="showFormModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="font-bold text-slate-800">Attribuer une Bourse</h3>
                    <button @click="showFormModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-lg"></i></button>
                </div>
                <form @submit.prevent="saveBoursier()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Étudiant *</label>
                        <select x-model="form.id_etudiant" required class="f-input">
                            <option value="">Choisir...</option>
                            <template x-for="e in etudiants" :key="e.id">
                                <option :value="e.id" x-text="e.nom + ' ' + e.prenom"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Bourse *</label>
                        <select x-model="form.id_bourse" required class="f-input">
                            <option value="">Choisir...</option>
                            <template x-for="b in bourses" :key="b.id">
                                <option :value="b.id" x-text="b.libelle"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Année Scolaire *</label>
                        <select x-model="form.id_annee_scolaire" required class="f-input">
                            <option value="">Choisir...</option>
                            <template x-for="a in annees" :key="a.id">
                                <option :value="a.id" x-text="a.libelle"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-3 mt-4">
                        <button type="button" @click="showFormModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('boursiersPage', (boursiers, etudiants, bourses, annees) => ({
                boursiers, etudiants, bourses, annees,
                searchQuery: '',
                showFormModal: false,
                form: { id_etudiant: '', id_bourse: '', id_annee_scolaire: '' },

                filteredBoursiers() {
                    const q = this.searchQuery.toLowerCase();
                    return this.boursiers.filter(b => {
                        return !q || b.etudiant_lib.toLowerCase().includes(q) || b.matricule.toLowerCase().includes(q) || b.bourse_lib.toLowerCase().includes(q);
                    });
                },

                async saveBoursier() {
                    try {
                        const r = await fetch('/etudiants/boursiers', {
                            method: 'POST',
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

                async deleteBourse(id) {
                    if (!confirm("Retirer l'attribution de cette bourse ?")) return;
                    try {
                        const r = await fetch(`/etudiants/boursiers/${id}`, {
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
