<x-app-layout>
    <x-slot name="title">Échéanciers de Scolarité - Finance</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Finance / Échéanciers</h2>
    </x-slot>

    @push('styles')
    <style>
        .av-card { background:#fff; border-radius:16px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.02); border:1px solid #F1F5F9; }
        .f-select { border:1.5px solid #E2E8F0; border-radius:10px; padding:10px 14px; font-size:13px; color:#1E293B; font-weight:500; outline:none; transition:all .2s ease; background-color: #fff; }
        .f-select:focus { border-color:#5A67D8; box-shadow:0 0 0 3px rgba(90,103,216,.1); }
        .f-table-container { background:#fff; border-radius:16px; border:1px solid #F1F5F9; box-shadow:0 2px 10px rgba(0,0,0,0.02); overflow:hidden; }
        .f-table-header th { background:#F8FAFC; padding:14px 16px; font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; text-align:left; border-bottom:1px solid #F1F5F9; white-space:nowrap; }
        .f-table-row td { padding:14px 16px; font-size:13px; color:#1E293B; border-bottom:1px solid #F8FAFC; vertical-align:middle; }
        .f-table-row:hover td { background:#F8FAFC; }
        
        .badge { padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 600; display: inline-block; text-align:center; white-space: nowrap; }
        .badge-frais { background: #FCE7F3; color: #DB2777; }
        .badge-niveau { background: #D1FAE5; color: #059669; border-radius: 6px; padding: 2px 6px; font-size: 10px; margin-left: 4px; }
        
        .badge-tranche-detail { border: 1px solid #C7D2FE; color: #4F46E5; background: #EEF2FF; border-radius: 6px; padding: 2px 6px; font-size: 10px; margin-right: 4px; margin-bottom: 4px; display: inline-block; }
        
        .status-non-paye { background: #FEE2E2; color: #DC2626; }
        .status-en-cours { background: #FEF3C7; color: #D97706; }
        .status-paye { background: #D1FAE5; color: #059669; }

        .text-montant-total { color: #4F46E5; font-weight: 700; }
        .text-montant-paye { color: #059669; font-weight: 700; }
        .text-montant-restant { color: #DC2626; font-weight: 700; }

        .btn-export { border: 1px solid #E2E8F0; background: #fff; color: #475569; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; display:flex; align-items:center; gap: 6px; transition:all 0.2s; }
        .btn-export:hover { background: #F8FAFC; }
        .btn-primary { background: #831843; color: white; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; display:flex; align-items:center; gap: 6px; transition:all 0.2s; }
        .btn-primary:hover { background: #9D174D; }
        
        .pagination-btn { width:32px; height:32px; border-radius:8px; border:1px solid #E2E8F0; display:flex; align-items:center; justify-content:center; color:#64748B; font-size:13px; background:#fff; }
        .pagination-btn.active { background:#831843; color:white; border-color:#831843; font-weight:bold; }
        
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); z-index: 50; display:flex; align-items:center; justify-content:center; }
        .modal-content { background: #fff; border-radius: 16px; width: 100%; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto; }
    </style>
    @endpush

    @php
    $inscriptionsJson = $inscriptions->map(fn($i) => ['id'=>$i->id, 'etudiant'=>$i->etudiant?->nom.' '.$i->etudiant?->prenom, 'matricule'=>$i->etudiant?->matricule, 'id_niveau'=>$i->id_niveau, 'id_filiere'=>$i->id_filiere]);
    $niveauxJson = $niveaux->map(fn($n) => ['id'=>$n->id, 'libelle'=>$n->libelle, 'code'=>$n->code]);
    $filieresJson = $filieres->map(fn($f) => ['id'=>$f->id, 'libelle'=>$f->libelle]);
    $fraisScolariteJson = $fraisScolarite->map(fn($f) => ['id'=>$f->id, 'id_type_frais'=>$f->id_type_frais]);
    $typesFraisJson = $typesFrais->map(fn($t) => ['id'=>$t->id, 'libelle'=>$t->libelle]);
    
    // Preparation des donnees d'echeanciers avec tranches et paiements
    $echeanciersJson = $echeanciers->map(function($e) use($tranchesPrevues) {
        $tranches = $tranchesPrevues->where('id_echeancier_scolarite', $e->id)->values();
        $paye = 0;
        $tranchesList = $tranches->map(function($t, $idx) use (&$paye) {
            $p_details = \App\Models\PaiementTrancheDetail::where('id_tranche_prevu', $t->id)->get();
            $montant_paye = $p_details->sum('montant_alloue');
            $paye += $montant_paye;
            $suffix = ($idx === 0) ? 'ère' : 'ème';
            return [
                'nom' => ($idx+1).$suffix.' tranche',
                'montant' => $t->montant,
                'date_echeance' => $t->date_echeance,
                'statut_paiement' => $t->statut_paiement
            ];
        });
        
        $net = max(0, $e->montant_total - $e->montant_remise);
        $restant = max(0, $net - $paye);
        
        $statut = 'Non payé';
        if($paye > 0 && $restant > 0) $statut = 'En cours';
        else if($paye >= $net && $net > 0) $statut = 'Payé';
        
        return [
            'id'=>$e->id, 
            'id_inscription'=>$e->id_inscription, 
            'id_frais_scolarite'=>$e->id_frais_scolarite, 
            'montant_total'=>$e->montant_total, 
            'montant_remise'=>$e->montant_remise,
            'net'=>$net,
            'paye'=>$paye,
            'restant'=>$restant,
            'statut'=>$statut,
            'tranches'=>$tranchesList
        ];
    });
    @endphp

    <div x-data="echeanciersPage({{ $inscriptionsJson }}, {{ $niveauxJson }}, {{ $filieresJson }}, {{ $fraisScolariteJson }}, {{ $typesFraisJson }}, {{ $echeanciersJson }})" class="space-y-6">
        
        <!-- Header -->
        <div class="flex items-center flex-wrap gap-3 justify-between">
            <div>
                <h1 class="text-2xl font-bold" style="color:#1E293B">Échéanciers de Scolarité</h1>
                <p class="text-sm mt-1" style="color:#94A3B8" x-text="filteredData().length + ' ligne(s)'"></p>
            </div>
            <div class="flex gap-2">
                <button class="btn-export">
                    <i class="ri-download-2-line"></i> Exporter
                </button>
                <button class="btn-primary" @click="openCreateModal()">
                    <i class="ri-list-check"></i> Créer les Tranches
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex gap-3 flex-wrap">
            <select x-model="filterNiveau" class="f-select max-w-[200px]">
                <option value="">Tous les niveaux</option>
                <template x-for="n in niveaux" :key="n.id">
                    <option :value="n.id" x-text="n.libelle"></option>
                </template>
            </select>
            <select x-model="filterFiliere" class="f-select max-w-[200px]">
                <option value="">Toutes les filières</option>
                <template x-for="f in filieres" :key="f.id">
                    <option :value="f.id" x-text="f.libelle"></option>
                </template>
            </select>
            <div class="relative w-full max-w-[300px]">
                <i class="ri-search-line absolute left-3 top-3 text-gray-400"></i>
                <input type="text" x-model="search" placeholder="Rechercher étudiant..." class="f-select pl-9">
            </div>
        </div>

        <!-- Table -->
        <div class="f-table-container">
            <div class="p-3 border-b border-gray-100 bg-white flex items-center gap-2">
                <span class="text-sm text-gray-500 ml-2">Lignes/page :</span>
                <select x-model="perPage" class="f-select max-w-[80px] py-1 px-2 text-sm">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="f-table-header">
                            <th>ÉTUDIANT</th>
                            <th>FILIÈRE / NIVEAU</th>
                            <th>FRAIS</th>
                            <th>TOTAL</th>
                            <th>TRANCHES</th>
                            <th>PAYÉ</th>
                            <th>RESTANT</th>
                            <th>STATUT</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="e in paginatedData()" :key="e.id">
                            <tr class="f-table-row">
                                <td>
                                    <div class="font-bold text-[#1E293B]" x-text="e.etudiant"></div>
                                    <div class="text-xs text-gray-400 mt-0.5" x-text="e.matricule"></div>
                                </td>
                                <td>
                                    <div class="flex items-center">
                                        <span x-text="e.filiere"></span>
                                        <span class="badge-niveau font-bold" x-text="e.niveau_code"></span>
                                    </div>
                                </td>
                                <td><span class="badge badge-frais" x-text="e.frais_libelle"></span></td>
                                <td class="text-montant-total" x-text="format(e.net)"></td>
                                <td class="max-w-[250px] flex-wrap">
                                    <template x-for="t in e.tranches">
                                        <span class="badge-tranche-detail" x-text="t.nom + ': ' + format(t.montant)"></span>
                                    </template>
                                    <template x-if="e.tranches.length === 0">
                                        <span class="text-xs text-gray-400 italic">Aucune tranche</span>
                                    </template>
                                </td>
                                <td>
                                    <span x-show="e.paye > 0" class="text-montant-paye" x-text="format(e.paye)"></span>
                                    <span x-show="e.paye === 0" class="text-gray-500">0 F</span>
                                </td>
                                <td class="text-montant-restant" x-text="format(e.restant)"></td>
                                <td>
                                    <span class="badge" :class="getBadgeClass(e.statut)" x-text="e.statut"></span>
                                </td>
                                <td>
                                    <button class="text-blue-600 hover:text-blue-800 font-semibold text-xs flex items-center gap-1" @click="viewDetails(e.id)">
                                        <i class="ri-eye-line text-sm"></i> Voir détails
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="paginatedData().length === 0">
                            <td colspan="9" class="text-center py-8 text-gray-400">Aucun échéancier trouvé</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 flex items-center justify-between bg-white">
                <span class="text-sm text-gray-500" x-text="paginationText()"></span>
                <div class="flex items-center gap-1">
                    <button class="pagination-btn" @click="prevPage()" :disabled="currentPage === 1"><i class="ri-arrow-left-s-line"></i></button>
                    <template x-for="page in totalPages()" :key="page">
                        <button class="pagination-btn" :class="currentPage === page ? 'active' : ''" @click="currentPage = page" x-text="page" x-show="page === 1 || page === totalPages() || Math.abs(page - currentPage) <= 1"></button>
                    </template>
                    <button class="pagination-btn" @click="nextPage()" :disabled="currentPage === totalPages()"><i class="ri-arrow-right-s-line"></i></button>
                </div>
            </div>
        </div>

        <!-- Modal Details -->
        <div class="modal-overlay" x-show="showDetailsModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop style="max-width: 600px;">
                <template x-if="selectedEch">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800" x-text="selectedEch.etudiant"></h3>
                                <p class="text-sm text-gray-500" x-text="selectedEch.frais_libelle"></p>
                            </div>
                            <button @click="showDetailsModal = false" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-xl"></i></button>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-3 mb-6">
                            <div class="bg-blue-50 border border-blue-100 rounded-lg p-3">
                                <div class="text-[10px] uppercase font-bold text-blue-500 mb-1">Total net</div>
                                <div class="text-blue-700 font-bold text-lg" x-text="format(selectedEch.net)"></div>
                            </div>
                            <div class="bg-green-50 border border-green-100 rounded-lg p-3">
                                <div class="text-[10px] uppercase font-bold text-green-500 mb-1">Payé</div>
                                <div class="text-green-700 font-bold text-lg" x-text="format(selectedEch.paye)"></div>
                            </div>
                            <div class="bg-red-50 border border-red-100 rounded-lg p-3">
                                <div class="text-[10px] uppercase font-bold text-red-500 mb-1">Restant</div>
                                <div class="text-red-700 font-bold text-lg" x-text="format(selectedEch.restant)"></div>
                            </div>
                        </div>

                        <h4 class="font-bold text-sm text-gray-800 mb-3">Tranches prévues</h4>
                        <div class="space-y-3">
                            <template x-for="t in selectedEch.tranches" :key="t.nom">
                                <div class="flex items-center justify-between p-3 border-b border-gray-100">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-lg" :class="getTrancheIconClass(t.statut_paiement)">
                                            <i :class="getTrancheIcon(t.statut_paiement)"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-800 text-sm" x-text="t.nom"></div>
                                            <div class="text-xs text-gray-400">Échéance : <span x-text="t.date_echeance"></span></div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-gray-800 text-sm" x-text="format(t.montant)"></div>
                                        <div class="text-[10px] font-bold mt-1" :class="getTrancheTextClass(t.statut_paiement)" x-text="t.statut_paiement"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('echeanciersPage', (inscriptions, niveaux, filieres, fraisScolarite, typesFrais, echeanciers) => ({
                inscriptions, niveaux, filieres, fraisScolarite, typesFrais, echeanciers,
                search: '',
                filterNiveau: '',
                filterFiliere: '',
                perPage: 10,
                currentPage: 1,
                
                showDetailsModal: false,
                selectedEch: null,
                
                init() {
                    // Enrich echeanciers data
                    this.echeanciers = this.echeanciers.map(e => {
                        const ins = this.inscriptions.find(i => i.id === e.id_inscription);
                        let niv = null;
                        let fil = null;
                        if(ins) {
                            niv = this.niveaux.find(n => n.id === ins.id_niveau);
                            fil = this.filieres.find(f => f.id === ins.id_filiere);
                        }
                        const frais = this.fraisScolarite.find(f => f.id === e.id_frais_scolarite);
                        let tf = null;
                        if(frais) tf = this.typesFrais.find(t => t.id === frais.id_type_frais);
                        
                        return {
                            ...e,
                            etudiant: ins ? ins.etudiant : 'Inconnu',
                            matricule: ins ? ins.matricule : '',
                            id_niveau: ins ? ins.id_niveau : null,
                            id_filiere: ins ? ins.id_filiere : null,
                            filiere: fil ? fil.libelle : '',
                            niveau_code: niv ? niv.code : '',
                            frais_libelle: tf ? tf.libelle : 'Frais de scolarité',
                        };
                    });
                },

                filteredData() {
                    let data = this.echeanciers;
                    if(this.filterNiveau) {
                        data = data.filter(e => e.id_niveau == this.filterNiveau);
                    }
                    if(this.filterFiliere) {
                        data = data.filter(e => e.id_filiere == this.filterFiliere);
                    }
                    if(this.search.trim() !== '') {
                        const q = this.search.toLowerCase();
                        data = data.filter(e => e.etudiant.toLowerCase().includes(q) || e.matricule.toLowerCase().includes(q));
                    }
                    return data;
                },
                
                paginatedData() {
                    const start = (this.currentPage - 1) * this.perPage;
                    return this.filteredData().slice(start, start + parseInt(this.perPage));
                },
                
                totalPages() {
                    return Math.ceil(this.filteredData().length / this.perPage) || 1;
                },
                
                prevPage() { if(this.currentPage > 1) this.currentPage--; },
                nextPage() { if(this.currentPage < this.totalPages()) this.currentPage++; },
                
                paginationText() {
                    const total = this.filteredData().length;
                    if(total === 0) return '0 résultat';
                    const start = (this.currentPage - 1) * this.perPage + 1;
                    const end = Math.min(this.currentPage * this.perPage, total);
                    return `${start}–${end} sur ${total} résultat(s)`;
                },

                format(v) {
                    return Number(v).toLocaleString('fr-FR') + ' F';
                },
                
                getBadgeClass(s) {
                    if(s === 'Payé') return 'status-paye';
                    if(s === 'En cours') return 'status-en-cours';
                    return 'status-non-paye';
                },

                getTrancheIconClass(statut) {
                    if(statut === 'Payé') return 'bg-green-100 text-green-500';
                    if(statut === 'Partiel') return 'bg-orange-100 text-orange-500';
                    return 'bg-gray-100 text-orange-400';
                },

                getTrancheIcon(statut) {
                    if(statut === 'Payé') return 'ri-check-line';
                    if(statut === 'Partiel') return 'ri-time-line';
                    return 'ri-time-line';
                },

                getTrancheTextClass(statut) {
                    if(statut === 'Payé') return 'text-green-500';
                    if(statut === 'Partiel') return 'text-orange-500';
                    return 'text-orange-400';
                },

                openCreateModal() { alert('Modale de création des tranches'); },
                viewDetails(id) { 
                    this.selectedEch = this.echeanciers.find(e => e.id === id);
                    this.showDetailsModal = true;
                }
            }));
            
            Alpine.effect(() => {
                Alpine.store('perPageTrigger2', document.querySelector('[x-model="perPage"]')?.value);
                const comp = document.querySelector('[x-data]')?._x_dataStack[0];
                if(comp && comp.currentPage > comp.totalPages()) comp.currentPage = 1;
            });
        });
    </script>
    @endpush
</x-app-layout>
