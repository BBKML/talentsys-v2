<x-app-layout>
    <x-slot name="title">Gestion des Abonnements</x-slot>

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
        .f-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(90, 103, 216, 0.15); }
        .btn-burgundy {
            background-color: var(--primary); color: #FFFFFF; font-weight: 600;
            padding: 10px 20px; border-radius: 10px; transition: all 0.2s;
        }
        .btn-burgundy:hover { background-color: var(--primary); }
        .action-btn {
            width: 32px; height: 32px; border-radius: 8px; display: inline-flex;
            align-items: center; justify-content: center; transition: all 0.2s;
        }
        .action-btn:hover { background: #E2E8F0; }
    </style>
    @endpush

    @php
        $typesJson = $types->map(fn($t) => [
            'id' => $t->id,
            'libelle' => $t->libelle,
            'prix_mensuel' => (int)$t->prix_mensuel,
            'nb_utilisateurs_max' => (int)$t->nb_utilisateurs_max,
            'nb_etudiants_max' => (int)$t->nb_etudiants_max,
            'id_statut' => (int)$t->id_statut
        ])->toJson();

        $abonnementsJson = $abonnements->map(fn($a) => [
            'id' => $a->id,
            'id_type_abonnement' => $a->id_type_abonnement,
            'type_lib' => $a->type ? $a->type->libelle : '—',
            'etablissement_lib' => $a->etablissement ? $a->etablissement->nom : '—',
            'id_etablissement' => $a->id_etablissement,
            'date_debut' => $a->date_debut,
            'date_fin' => $a->date_fin ?: '',
            'id_statut' => (int)$a->id_statut,
            'status_lib' => $a->status ? $a->status->libelle : 'Actif'
        ])->toJson();

        $etablissementsJson = $etablissements->map(fn($e) => ['id' => $e->id, 'nom' => $e->nom])->toJson();
        $statutsJson = $statuts->map(fn($s) => ['id' => $s->id, 'libelle' => $s->libelle])->toJson();
    @endphp

    <div x-data="abonsPage({{ $typesJson }}, {{ $abonnementsJson }}, {{ $etablissementsJson }}, {{ $statutsJson }})" class="space-y-8">

        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Abonnements</h1>
            <p class="text-xs text-slate-500 mt-1">Gestion des plans et abonnements SaaS</p>
        </div>

        <!-- Plan cards -->
        <div>
            <h3 class="font-bold text-slate-800 text-sm mb-4">Plans disponibles</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <template x-for="(t, index) in types" :key="t.id">
                    <div class="rounded-2xl p-6 border transition-all flex flex-col justify-between" :class="index === 1 ? 'bg-[var(--primary)] text-white border-[var(--primary)] shadow-lg scale-105' : 'bg-white text-slate-800 border-slate-200 shadow-sm'">
                        <div>
                            <!-- Recommended Badge -->
                            <template x-if="index === 1">
                                <div class="mb-3 inline-block px-3 py-1 bg-white/20 text-white rounded-full text-[10px] font-bold uppercase tracking-wider">
                                    Recommandé
                                </div>
                            </template>
                            
                            <h4 class="font-black text-lg" :class="index === 1 ? 'text-white' : 'text-slate-800'" x-text="t.libelle"></h4>
                            
                            <div class="mt-4 flex items-end gap-1">
                                <span class="text-3xl font-black" :class="index === 1 ? 'text-white' : 'text-[var(--primary)]'" x-text="fmtPrice(t.prix_mensuel)"></span>
                                <span class="text-[11px] mb-1 opacity-70">FCFA/mois</span>
                            </div>

                            <!-- Features -->
                            <div class="mt-6 space-y-3">
                                <div class="flex items-center gap-2 text-xs">
                                    <i class="ri-checkbox-circle-line text-emerald-500" :class="index === 1 ? 'text-white/80' : 'text-emerald-600'"></i>
                                    <span x-text="t.nb_utilisateurs_max + ' utilisateurs max'"></span>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <i class="ri-checkbox-circle-line text-emerald-500" :class="index === 1 ? 'text-white/80' : 'text-emerald-600'"></i>
                                    <span x-text="t.nb_etudiants_max + ' étudiants max'"></span>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <i class="ri-checkbox-circle-line text-emerald-500" :class="index === 1 ? 'text-white/80' : 'text-emerald-600'"></i>
                                    <span>Support inclus</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8">
                            <button @click="openPlanModal(t)" class="w-full font-bold text-xs py-3 rounded-xl transition-all shadow-sm" :class="index === 1 ? 'bg-white text-[var(--primary)] hover:bg-slate-50' : 'bg-[var(--primary)] text-white hover:bg-[var(--primary)]'">
                                Modifier
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Plans table panel -->
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 text-sm">Types d'Abonnement</h3>
                <button @click="openPlanModal()" class="bg-[var(--primary)] hover:bg-[var(--primary)] text-white font-bold py-2 px-4 rounded-xl flex items-center gap-1.5 text-xs shadow-sm">
                    <i class="ri-add-line text-white"></i> Nouveau Plan
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="p-4">LIBELLÉ</th>
                            <th class="p-4">PRIX MENSUEL</th>
                            <th class="p-4">UTILISATEURS</th>
                            <th class="p-4">ÉTUDIANTS</th>
                            <th class="p-4">STATUT</th>
                            <th class="p-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        <template x-for="t in types" :key="t.id">
                            <tr class="hover:bg-slate-50/20">
                                <td class="p-4 font-bold text-[var(--primary)]" x-text="t.libelle"></td>
                                <td class="p-4 font-bold text-emerald-600" x-text="fmtPrice(t.prix_mensuel) + ' FCFA / mois'"></td>
                                <td class="p-4 text-slate-500" x-text="t.nb_utilisateurs_max + ' utilisateurs'"></td>
                                <td class="p-4 text-slate-500" x-text="t.nb_etudiants_max + ' étudiants'"></td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase" :class="t.id_statut === 1 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'" x-text="t.id_statut === 1 ? 'Actif' : 'Inactif'"></span>
                                </td>
                                <td class="p-4 text-right space-x-0.5">
                                    <button class="action-btn text-blue-600" @click="openPlanModal(t)"><i class="ri-edit-line"></i></button>
                                    <button class="action-btn text-red-600" @click="deletePlan(t.id)"><i class="ri-delete-bin-line"></i></button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Abonnements actifs table panel -->
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 text-sm">Abonnements Actifs</h3>
                <button @click="openAbonModal()" class="bg-[var(--primary)] hover:bg-[var(--primary)] text-white font-bold py-2 px-4 rounded-xl flex items-center gap-1.5 text-xs shadow-sm">
                    <i class="ri-add-line text-white"></i> Nouvel Abonnement
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="p-4">PLAN</th>
                            <th class="p-4">ÉTABLISSEMENT</th>
                            <th class="p-4">DATE DÉBUT</th>
                            <th class="p-4">DATE FIN</th>
                            <th class="p-4">STATUT</th>
                            <th class="p-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        <template x-for="a in abonnements" :key="a.id">
                            <tr class="hover:bg-slate-50/20">
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded bg-indigo-50 text-[var(--primary)] font-bold text-xs" x-text="a.type_lib"></span>
                                </td>
                                <td class="p-4 font-bold text-slate-700" x-text="a.etablissement_lib"></td>
                                <td class="p-4 text-slate-500" x-text="a.date_debut"></td>
                                <td class="p-4 text-slate-500" x-text="a.date_fin || '—'"></td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase" :class="a.id_statut === 1 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'" x-text="a.id_statut === 1 ? 'Actif' : 'Expiré'"></span>
                                </td>
                                <td class="p-4 text-right space-x-0.5">
                                    <button class="action-btn text-blue-600" @click="openAbonModal(a)"><i class="ri-edit-line"></i></button>
                                    <button class="action-btn text-red-600" @click="deleteAbon(a.id)"><i class="ri-delete-bin-line"></i></button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── MODAL: TYPE ABONNEMENT (PLAN) ── -->
        <div class="modal-overlay" x-show="showPlanModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-base" x-text="isEditPlan ? 'Modifier Plan' : 'Nouveau Plan'"></h3>
                    <button @click="showPlanModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-xl"></i></button>
                </div>

                <form @submit.prevent="savePlan()" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Libellé *</label>
                        <input type="text" x-model="planForm.libelle" required placeholder="Ex: Starter" class="f-input">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Prix mensuel (FCFA) *</label>
                            <input type="number" x-model="planForm.prix_mensuel" required placeholder="0" class="f-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Utilisateurs max</label>
                            <input type="number" x-model="planForm.nb_utilisateurs_max" required class="f-input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Étudiants max</label>
                        <input type="number" x-model="planForm.nb_etudiants_max" required class="f-input">
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="showPlanModal = false" class="text-sm font-bold text-red-600 hover:text-red-800">Annuler</button>
                        <button type="submit" class="btn-burgundy">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── MODAL: ABONNEMENT ── -->
        <div class="modal-overlay" x-show="showAbonModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-base" x-text="isEditAbon ? 'Modifier Abonnement' : 'Nouvel Abonnement'"></h3>
                    <button @click="showAbonModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-xl"></i></button>
                </div>

                <form @submit.prevent="saveAbon()" class="p-6 space-y-4">
                    <div x-show="!isEditAbon">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Plan *</label>
                        <select x-model="abonForm.id_type_abonnement" :required="!isEditAbon" class="f-input">
                            <option value="">Choisir un plan...</option>
                            <template x-for="t in types" :key="t.id">
                                <option :value="t.id" x-text="t.libelle"></option>
                            </template>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Date début *</label>
                            <input type="date" x-model="abonForm.date_debut" required class="f-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Date fin</label>
                            <input type="date" x-model="abonForm.date_fin" class="f-input">
                        </div>
                    </div>
                    
                    <div x-show="isEditAbon">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Statut *</label>
                        <select x-model="abonForm.id_statut" class="f-input">
                            <template x-for="s in statuts" :key="s.id">
                                <option :value="s.id" x-text="s.libelle"></option>
                            </template>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="showAbonModal = false" class="text-sm font-bold text-red-600 hover:text-red-800">Annuler</button>
                        <button type="submit" class="btn-burgundy">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('abonsPage', (types, abonnements, etablissements, statuts) => ({
                types, abonnements, etablissements, statuts,
                
                showPlanModal: false,
                isEditPlan: false,
                planForm: { id: null, libelle: '', prix_mensuel: '', nb_utilisateurs_max: 5, nb_etudiants_max: 100, id_statut: 1 },

                showAbonModal: false,
                isEditAbon: false,
                abonForm: { id: null, id_type_abonnement: '', date_debut: '', date_fin: '', id_statut: 1 },

                fmtPrice(price) {
                    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
                },

                openPlanModal(plan = null) {
                    this.isEditPlan = !!plan;
                    if (plan) {
                        this.planForm = { ...plan };
                    } else {
                        this.planForm = { id: null, libelle: '', prix_mensuel: 0, nb_utilisateurs_max: 5, nb_etudiants_max: 100, id_statut: 1 };
                    }
                    this.showPlanModal = true;
                },

                async savePlan() {
                    const url = this.isEditPlan ? `/parametres/types-abonnement/${this.planForm.id}` : '/parametres/types-abonnement';
                    const method = this.isEditPlan ? 'PUT' : 'POST';
                    try {
                        const r = await fetch(url, {
                            method,
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.planForm)
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

                async deletePlan(id) {
                    if (!confirm('Supprimer ce plan SaaS ?')) return;
                    try {
                        const r = await fetch(`/parametres/types-abonnement/${id}`, {
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
                },

                openAbonModal(abon = null) {
                    this.isEditAbon = !!abon;
                    const today = new Date().toISOString().substring(0, 10);
                    if (abon) {
                        this.abonForm = { ...abon };
                    } else {
                        const defPlan = this.types.length > 0 ? this.types[0].id : '';
                        this.abonForm = { id: null, id_type_abonnement: defPlan, date_debut: today, date_fin: '', id_statut: 1 };
                    }
                    this.showAbonModal = true;
                },

                async saveAbon() {
                    const url = this.isEditAbon ? `/abonnements/${this.abonForm.id}` : '/abonnements';
                    const method = this.isEditAbon ? 'PUT' : 'POST';
                    try {
                        const r = await fetch(url, {
                            method,
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.abonForm)
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

                async deleteAbon(id) {
                    if (!confirm('Supprimer cet abonnement ?')) return;
                    try {
                        const r = await fetch(`/abonnements/${id}`, {
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
