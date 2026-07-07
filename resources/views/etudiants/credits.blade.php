<x-app-layout>
    <x-slot name="title">Crédits Universitaires</x-slot>
    
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
            border: 1px solid #CBD5E1; border-radius: 12px;
            padding: 10px 14px; font-size: 13px; outline: none; transition: all 0.2s;
            background-color: #FFFFFF; color: #334155;
        }
        .f-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(142, 29, 81, 0.15); }
        
        .stat-card {
            background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px;
            padding: 16px; display: flex; align-items: center; gap: 16px; flex: 1;
            min-width: 200px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .btn-burgundy {
            background-color: var(--primary); color: #FFFFFF; font-weight: 600;
            padding: 10px 20px; border-radius: 10px; transition: all 0.2s;
        }
        .btn-burgundy:hover { background-color: var(--primary); }
    </style>
    @endpush

    @php
        $creditsJson = $credits->map(fn($c) => [
            'id' => $c->id,
            'id_inscription' => $c->id_inscription,
            'etudiant_lib' => $c->inscription && $c->inscription->etudiant ? $c->inscription->etudiant->nom . ' ' . $c->inscription->etudiant->prenom : '—',
            'matricule' => $c->inscription && $c->inscription->etudiant ? $c->inscription->etudiant->matricule : '—',
            'id_etudiant' => $c->inscription ? $c->inscription->id_etudiant : null,
            'id_classe' => $c->inscription ? $c->inscription->id_classe : null,
            'ue_lib' => $c->ue ? $c->ue->libelle : '—',
            'id_ue' => $c->id_ue,
            'credits_obtenus' => (int)$c->credits_obtenus,
            'valide' => (bool)$c->valide,
            'date_validation' => $c->date_validation
        ])->toJson();

        $inscriptionsJson = $inscriptions->map(fn($i) => [
            'id' => $i->id,
            'id_etudiant' => $i->id_etudiant,
            'id_classe' => $i->id_classe,
            'etudiant_lib' => $i->etudiant ? $i->etudiant->nom . ' ' . $i->etudiant->prenom : '—',
            'matricule' => $i->etudiant ? $i->etudiant->matricule : '—',
        ])->toJson();

        $uesJson = $ues->map(fn($u) => ['id' => $u->id, 'libelle' => $u->libelle, 'credits' => 6])->toJson(); // assume 6 credits per UE
        $classesJson = $classes->map(fn($c) => ['id' => $c->id, 'libelle' => $c->libelle])->toJson();
    @endphp

    <div x-data="creditsPage({{ $creditsJson }}, {{ $inscriptionsJson }}, {{ $uesJson }}, {{ $classesJson }})" class="space-y-6">

        <!-- Title Row -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Crédits Universitaires</h1>
                <p class="text-xs text-slate-400 mt-1">Validation des Unités d'Enseignement par étudiant</p>
            </div>
            
            <div class="flex items-center gap-3">
                <select x-model="selectedClassFilter" class="f-input max-w-[200px] shadow-sm">
                    <option value="">Toutes les classes</option>
                    <template x-for="c in classes" :key="c.id">
                        <option :value="c.id" x-text="c.libelle"></option>
                    </template>
                </select>
                <button @click="openCreditModal()" class="bg-[#008b8b] hover:bg-[#007070] text-white font-bold py-2.5 px-4 rounded-xl flex items-center gap-1.5 text-xs shadow-sm">
                    <i class="ri-checkbox-circle-line"></i> Attribuer Crédits UE
                </button>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="flex flex-wrap gap-4">
            <div class="stat-card">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="ri-user-line"></i>
                </div>
                <div>
                    <div class="text-2xl font-black text-slate-800" x-text="stats().etudiantsCount">3</div>
                    <div class="text-[11px] text-slate-400 font-bold uppercase">Étudiants</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="ri-checkbox-circle-line"></i>
                </div>
                <div>
                    <div class="text-2xl font-black text-slate-800" x-text="stats().creditsObtenus">18</div>
                    <div class="text-[11px] text-slate-400 font-bold uppercase">Crédits obtenus</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="ri-more-line"></i>
                </div>
                <div>
                    <div class="text-2xl font-black text-slate-800" x-text="stats().creditsPrevus">36</div>
                    <div class="text-[11px] text-slate-400 font-bold uppercase">Crédits prévus</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl">
                    <i class="ri-arrow-up-line"></i>
                </div>
                <div>
                    <div class="text-2xl font-black text-slate-800" x-text="stats().pretsPassage">2</div>
                    <div class="text-[11px] text-slate-400 font-bold uppercase">Prêts au passage</div>
                </div>
            </div>
        </div>

        <!-- Info alert banner -->
        <div class="bg-emerald-50 border border-emerald-200/60 rounded-2xl p-4 space-y-1 text-xs text-emerald-800 shadow-sm">
            <div class="flex items-start gap-2">
                <i class="ri-information-line text-emerald-600 text-base mt-0.5"></i>
                <div>
                    <span class="font-bold"><span x-text="stats().pretsPassage">2</span> étudiant(s) ont validé toutes leurs UEs Fondamentales — éligibles au passage. Dont 2 avec des dettes à rattraper.</span>
                    <p class="text-[11px] text-amber-700 font-bold mt-1 flex items-center gap-1">
                        <i class="ri-error-warning-line text-amber-600"></i> Les UEs Transversales/Optionnelles non validées restent comme dettes à composer l'an prochain.
                    </p>
                </div>
            </div>
        </div>

        <!-- Table Panel -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-4">
                <input type="text" x-model="searchQuery" placeholder="Rechercher..." class="f-input max-w-xs pl-8 bg-slate-50" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w22.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2394A3B8%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><circle cx=%2211%22 cy=%2211%22 r=%228%22/><line x1=%2221%22 y1=%2221%22 x2=%2216.65%22 y2=%2216.65%22/></svg>'); background-repeat: no-repeat; background-position: 10px 12px;">
                
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span>Lignes/page :</span>
                    <select x-model="pageSize" class="f-input py-1 px-2">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="p-4">ÉTUDIANT</th>
                            <th class="p-4">CRÉDITS OBTENUS</th>
                            <th class="p-4">PROGRESSION</th>
                            <th class="p-4">UEs VALIDÉES</th>
                            <th class="p-4">STATUT</th>
                            <th class="p-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        <template x-for="row in pagedStudentRows()" :key="row.matricule">
                            <tr class="hover:bg-slate-50/20">
                                <td class="p-4">
                                    <div class="font-bold text-slate-800" x-text="row.etudiant_lib"></div>
                                    <div class="text-xs text-indigo-600 font-mono" x-text="row.matricule"></div>
                                </td>
                                <td class="p-4">
                                    <span class="font-bold text-emerald-600" :class="row.pct < 100 ? 'text-indigo-600' : 'text-emerald-600'" x-text="row.obtenus + ' / ' + row.prevus"></span>
                                </td>
                                <td class="p-4 w-48">
                                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all" :class="row.pct === 100 ? 'bg-emerald-500' : (row.pct >= 50 ? 'bg-indigo-500' : 'bg-slate-300')" :style="'width: ' + row.pct + '%'"></div>
                                    </div>
                                    <span class="text-[10px] text-slate-400 mt-1 block" x-text="row.pct + ' %'"></span>
                                </td>
                                <td class="p-4">
                                    <span class="font-bold" :class="row.valideesCount === row.uesTotal ? 'text-emerald-600' : 'text-amber-600'" x-text="row.valideesCount + ' / ' + row.uesTotal"></span>
                                </td>
                                <td class="p-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold" :class="row.pct === 100 ? 'bg-emerald-50 text-emerald-700' : (row.pct >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700')" x-text="row.pct === 100 ? 'Tout validé' : (row.pct >= 50 ? 'Passe (2 dettes)' : 'Incomplet')"></span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold py-1.5 px-3 rounded-lg flex items-center gap-1 inline-flex shadow-sm" @click="openDetails(row)">
                                        <i class="ri-search-line"></i> Voir détails
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="pagedStudentRows().length === 0">
                            <tr>
                                <td colspan="6" class="text-center py-8 text-slate-400 italic">Aucun crédit à afficher.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── MODAL: ATTRIBUER CREDITS ── -->
        <div class="modal-overlay" x-show="showCreditModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-lg">Attribuer des crédits</h3>
                    <button @click="showCreditModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-xl"></i></button>
                </div>

                <form @submit.prevent="saveCredit()" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Inscription d'étudiant *</label>
                        <select x-model="form.id_inscription" required class="f-input w-full">
                            <option value="">Choisir l'inscription...</option>
                            <template x-for="i in inscriptions" :key="i.id">
                                <option :value="i.id" x-text="i.etudiant_lib + ' (' + i.matricule + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Unité d'Enseignement (UE) *</label>
                        <select x-model="form.id_ue" required class="f-input w-full">
                            <option value="">Choisir l'UE...</option>
                            <template x-for="u in ues" :key="u.id">
                                <option :value="u.id" x-text="u.libelle"></option>
                            </template>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Crédits obtenus *</label>
                            <input type="number" x-model="form.credits_obtenus" required class="f-input w-full" placeholder="Ex: 6">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Validé *</label>
                            <select x-model="form.valide" required class="f-input w-full">
                                <option value="1">Oui</option>
                                <option value="0">Non</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="showCreditModal = false" class="text-sm font-bold text-red-600 hover:text-red-800">Annuler</button>
                        <button type="submit" class="btn-burgundy">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── MODAL: STUDENT CREDIT DETAILS (Timeline or raw lines of UEs) ── -->
        <div class="modal-overlay" x-show="showDetailsModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop style="max-width: 600px;">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="ri-book-read-line"></i></span>
                        <span x-text="'Détails crédits — ' + (selectedStudent ? selectedStudent.etudiant_lib : '')"></span>
                    </h3>
                    <button @click="showDetailsModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-xl"></i></button>
                </div>
                <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                    <template x-for="c in selectedStudentLines()" :key="c.id">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div>
                                <span class="font-semibold text-slate-700 text-sm" x-text="c.ue_lib"></span>
                                <span class="block text-[10px] text-slate-400" x-text="'Validé le ' + (c.date_validation || '—')"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="c.valide ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'" x-text="c.credits_obtenus + ' crédits (' + (c.valide ? 'Acquis' : 'Échoué') + ')'"></span>
                                <button @click="deleteCredit(c.id)" class="text-rose-600 hover:bg-rose-50 p-1 rounded transition-colors"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </div>
                    </template>
                    <template x-if="selectedStudentLines().length === 0">
                        <p class="text-xs text-slate-400 italic text-center py-8">Aucun crédit attribué pour cet étudiant.</p>
                    </template>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('creditsPage', (credits, inscriptions, ues, classes) => ({
                credits, inscriptions, ues, classes,
                searchQuery: '',
                selectedClassFilter: '',
                pageSize: 10,
                showCreditModal: false,
                showDetailsModal: false,
                selectedStudent: null,

                form: { id: null, id_inscription: '', id_ue: '', credits_obtenus: '', valide: '1' },

                stats() {
                    const rows = this.aggregateRows();
                    const etudiantsCount = rows.length;
                    const creditsObtenus = this.credits.filter(c => c.valide).reduce((sum, c) => sum + c.credits_obtenus, 0);
                    const creditsPrevus = rows.reduce((sum, r) => sum + r.prevus, 0);
                    const pretsPassage = rows.filter(r => r.pct >= 50).length;

                    return { etudiantsCount, creditsObtenus, creditsPrevus, pretsPassage };
                },

                aggregateRows() {
                    const grouped = {};
                    
                    // Filter credits and inscriptions by class
                    const filteredInscs = this.selectedClassFilter 
                        ? this.inscriptions.filter(i => i.id_classe == this.selectedClassFilter) 
                        : this.inscriptions;

                    filteredInscs.forEach(ins => {
                        grouped[ins.matricule] = {
                            etudiant_lib: ins.etudiant_lib,
                            matricule: ins.matricule,
                            obtenus: 0,
                            prevus: 12, // default standard target credits e.g. 12
                            valideesCount: 0,
                            uesTotal: 2,
                            pct: 0
                        };
                    });

                    // Aggregate credits obtained per student
                    this.credits.forEach(c => {
                        if (grouped[c.matricule]) {
                            if (c.valide) {
                                grouped[c.matricule].obtenus += c.credits_obtenus;
                                grouped[c.matricule].valideesCount += 1;
                            }
                        }
                    });

                    // Calculate percentages
                    const rows = [];
                    for (const mat in grouped) {
                        const r = grouped[mat];
                        r.pct = Math.min(100, Math.round((r.obtenus / r.prevus) * 100));
                        rows.push(r);
                    }

                    // Search filter
                    const q = this.searchQuery.toLowerCase();
                    return rows.filter(r => {
                        return !q || r.etudiant_lib.toLowerCase().includes(q) || r.matricule.toLowerCase().includes(q);
                    });
                },

                pagedStudentRows() {
                    return this.aggregateRows().slice(0, this.pageSize);
                },

                openCreditModal() {
                    this.form = { id: null, id_inscription: '', id_ue: '', credits_obtenus: '', valide: '1' };
                    this.showCreditModal = true;
                },

                openDetails(row) {
                    this.selectedStudent = row;
                    this.showDetailsModal = true;
                },

                selectedStudentLines() {
                    if (!this.selectedStudent) return [];
                    return this.credits.filter(c => c.matricule == this.selectedStudent.matricule);
                },

                async saveCredit() {
                    try {
                        const r = await fetch('/etudiants/credits', {
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

                async deleteCredit(id) {
                    if (!confirm('Retirer cette attribution de crédit ?')) return;
                    try {
                        const r = await fetch(`/etudiants/credits/${id}`, {
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
