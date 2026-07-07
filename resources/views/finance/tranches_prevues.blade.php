<x-app-layout>
    <x-slot name="title">Tranches Prévues - Finance</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Finance / Tranches Prévues</h2>
    </x-slot>

    @push('styles')
    <style>
        .av-card { background:#fff; border-radius:16px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.02); border:1px solid #F1F5F9; }
        .f-select { border:1.5px solid #E2E8F0; border-radius:10px; padding:10px 14px; font-size:13px; color:#1E293B; font-weight:500; outline:none; transition:all .2s ease; background-color: #fff; }
        .f-select:focus { border-color:#5A67D8; box-shadow:0 0 0 3px rgba(90,103,216,.1); }
        .f-table-container { background:#fff; border-radius:16px; border:1px solid #F1F5F9; box-shadow:0 2px 10px rgba(0,0,0,0.02); overflow:hidden; }
        .f-table-header th { background:#F8FAFC; padding:14px 16px; font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; text-align:left; border-bottom:1px solid #F1F5F9; white-space:nowrap; }
        .f-table-row td { padding:14px 16px; font-size:13px; color:#1E293B; border-bottom:1px solid #F8FAFC; vertical-align:middle; white-space:nowrap; }
        .f-table-row:hover td { background:#F8FAFC; }
        
        /* Badges */
        .badge { padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 600; display: inline-block; text-align:center; }
        .badge-attente { background: #F3F4F6; color: #6B7280; }
        .badge-paye { background: #D1FAE5; color: #059669; }
        .badge-retard { background: #FEE2E2; color: #DC2626; }
        .badge-frais { background: #FCE7F3; color: #DB2777; }
        .badge-tranche { background: #E0E7FF; color: #4F46E5; }
        
        /* Filter Chips */
        .filter-chip { padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 500; cursor: pointer; border: 1px solid #E2E8F0; color: #475569; transition: all 0.2s; }
        .filter-chip.active { background: #4F46E5; color: white; border-color: #4F46E5; }
        
        /* Stat Cards */
        .stat-card { border-radius: 12px; padding: 12px 16px; display: flex; align-items: center; gap: 12px; border: 1px solid #E2E8F0; min-width: 140px;}
        .stat-blue { background: #EEF2FF; border-color: #C7D2FE; color: #4F46E5; }
        .stat-green { background: #ECFDF5; border-color: #A7F3D0; color: #059669; }
        .stat-red { background: #FEF2F2; border-color: #FECACA; color: #DC2626; }
        .stat-yellow { background: #FFFBEB; border-color: #FDE68A; color: #D97706; }
        .stat-value { font-size: 20px; font-weight: 800; }
        .stat-label { font-size: 12px; font-weight: 500; opacity: 0.8; }
        
        .text-montant-prevu { color: #4F46E5; font-weight: 700; }
        .text-montant-restant { color: #DC2626; font-weight: 700; }
        
        .pagination-btn { width:32px; height:32px; border-radius:8px; border:1px solid #E2E8F0; display:flex; align-items:center; justify-content:center; color:#64748B; font-size:13px; background:#fff; }
        .pagination-btn.active { background:#831843; color:white; border-color:#831843; font-weight:bold; }
    </style>
    @endpush

    @php
    $inscriptionsJson = $inscriptions->map(fn($i) => ['id'=>$i->id, 'etudiant'=>$i->etudiant?->nom.' '.$i->etudiant?->prenom, 'matricule'=>$i->etudiant?->matricule]);
    $echeanciersJson = $echeanciers->map(fn($e) => ['id'=>$e->id, 'id_inscription'=>$e->id_inscription, 'id_frais_scolarite'=>$e->id_frais_scolarite]);
    $fraisScolariteJson = $fraisScolarite->map(fn($f) => ['id'=>$f->id, 'id_type_frais'=>$f->id_type_frais]);
    $typesFraisJson = $typesFrais->map(fn($t) => ['id'=>$t->id, 'libelle'=>$t->libelle]);
    $tranchesJson = $tranchesPrevues->map(function($t) {
        $p_details = \App\Models\PaiementTrancheDetail::where('id_tranche_prevu', $t->id)->get();
        $montant_paye = $p_details->sum('montant_alloue');
        return [
            'id'=>$t->id, 
            'id_echeancier_scolarite'=>$t->id_echeancier_scolarite, 
            'montant'=>$t->montant, 
            'date_echeance'=>$t->date_echeance, 
            'statut_paiement'=>$t->statut_paiement,
            'nb_paiements'=> $p_details->count(),
            'montant_paye'=> $montant_paye,
        ];
    });
    @endphp

    <div x-data="tranchesPage({{ $inscriptionsJson }}, {{ $echeanciersJson }}, {{ $tranchesJson }}, {{ $fraisScolariteJson }}, {{ $typesFraisJson }})" class="space-y-6">
        
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold" style="color:#1E293B">Tranches Prévues</h1>
            <p class="text-sm mt-1" style="color:#94A3B8" x-text="filteredData().length + ' tranche(s)'"></p>
        </div>

        <!-- Filters & Stats -->
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-3">
                <div class="relative w-full max-w-sm">
                    <i class="ri-search-line absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" x-model="search" placeholder="Rechercher étudiant..." class="f-select pl-9">
                </div>
                <div class="flex items-center gap-2">
                    <template x-for="filter in ['Tous', 'En attente', 'Partiel', 'En retard', 'Payé']">
                        <div class="filter-chip" :class="activeFilter === filter ? 'active' : ''" @click="activeFilter = filter" x-text="filter"></div>
                    </template>
                </div>
            </div>
            
            <div class="flex items-center gap-3 flex-wrap">
                <div class="stat-card stat-blue">
                    <span class="stat-value" x-text="stats.total"></span>
                    <span class="stat-label">Total tranches</span>
                </div>
                <div class="stat-card stat-green">
                    <span class="stat-value" x-text="stats.payees"></span>
                    <span class="stat-label">Payées</span>
                </div>
                <div class="stat-card stat-red">
                    <span class="stat-value" x-text="stats.retard"></span>
                    <span class="stat-label">En retard</span>
                </div>
                <div class="stat-card stat-yellow">
                    <span class="stat-value" x-text="stats.attente"></span>
                    <span class="stat-label">En attente</span>
                </div>
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
                            <th>FRAIS</th>
                            <th>TRANCHE</th>
                            <th>MONTANT PRÉVU</th>
                            <th>NB PAIEMENTS</th>
                            <th>MONTANT PAYÉ</th>
                            <th>RESTANT</th>
                            <th>ÉCHÉANCE</th>
                            <th>STATUT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="t in paginatedData()" :key="t.id">
                            <tr class="f-table-row">
                                <td>
                                    <div class="font-bold text-[#1E293B]" x-text="t.etudiant"></div>
                                    <div class="text-xs text-gray-400 mt-0.5" x-text="t.matricule"></div>
                                </td>
                                <td><span class="badge badge-frais" x-text="t.frais_libelle"></span></td>
                                <td><span class="badge badge-tranche" x-text="t.nom_tranche"></span></td>
                                <td class="text-montant-prevu" x-text="format(t.montant)"></td>
                                <td x-text="t.nb_paiements"></td>
                                <td x-text="format(t.montant_paye)"></td>
                                <td class="text-montant-restant" x-text="format(t.restant)"></td>
                                <td x-text="t.date_echeance"></td>
                                <td>
                                    <span class="badge" :class="getBadgeClass(t.statut_paiement)" x-text="t.statut_paiement"></span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="paginatedData().length === 0">
                            <td colspan="9" class="text-center py-8 text-gray-400">Aucune tranche trouvée</td>
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
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tranchesPage', (inscriptions, echeanciers, tranches, fraisScolarite, typesFrais) => ({
                inscriptions, echeanciers, tranches, fraisScolarite, typesFrais,
                search: '',
                activeFilter: 'Tous',
                perPage: 10,
                currentPage: 1,
                
                init() {
                    // Enrich tranches data
                    let indexTrancheEcheancier = {};
                    
                    this.tranches = this.tranches.sort((a,b) => a.id_echeancier_scolarite - b.id_echeancier_scolarite || new Date(a.date_echeance) - new Date(b.date_echeance)).map(t => {
                        const ech = this.echeanciers.find(e => e.id === t.id_echeancier_scolarite);
                        let ins = null;
                        let frais = null;
                        let tf = null;
                        if(ech) {
                            ins = this.inscriptions.find(i => i.id === ech.id_inscription);
                            frais = this.fraisScolarite.find(f => f.id === ech.id_frais_scolarite);
                            if(frais) tf = this.typesFrais.find(ty => ty.id === frais.id_type_frais);
                        }
                        
                        // Compute index tranche per echeancier
                        if(!indexTrancheEcheancier[t.id_echeancier_scolarite]) indexTrancheEcheancier[t.id_echeancier_scolarite] = 1;
                        const indexT = indexTrancheEcheancier[t.id_echeancier_scolarite]++;
                        const suffix = indexT === 1 ? 'ère' : 'ème';
                        
                        const restant = Math.max(0, t.montant - t.montant_paye);
                        
                        // Determiner statut plus precis
                        let statut = t.statut_paiement;
                        if(statut !== 'Payé') {
                            if(t.montant_paye > 0 && restant > 0) statut = 'Partiel';
                            else if(new Date(t.date_echeance) < new Date() && restant > 0) statut = 'En retard';
                            else statut = 'En attente';
                        }
                        
                        return {
                            ...t,
                            etudiant: ins ? ins.etudiant : 'Inconnu',
                            matricule: ins ? ins.matricule : '',
                            frais_libelle: tf ? tf.libelle : 'Frais de scolarité',
                            nom_tranche: `${indexT}${suffix} tranche`,
                            restant: restant,
                            statut_paiement: statut
                        };
                    });
                },
                
                get stats() {
                    let total = this.tranches.length;
                    let payees = this.tranches.filter(t => t.statut_paiement === 'Payé').length;
                    let retard = this.tranches.filter(t => t.statut_paiement === 'En retard').length;
                    let attente = this.tranches.filter(t => t.statut_paiement === 'En attente' || t.statut_paiement === 'Partiel').length;
                    return { total, payees, retard, attente };
                },

                filteredData() {
                    let data = this.tranches;
                    if(this.activeFilter !== 'Tous') {
                        data = data.filter(t => t.statut_paiement === this.activeFilter);
                    }
                    if(this.search.trim() !== '') {
                        const q = this.search.toLowerCase();
                        data = data.filter(t => t.etudiant.toLowerCase().includes(q) || t.matricule.toLowerCase().includes(q));
                    }
                    // Reset to page 1 on filter
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
                    if(s === 'Payé') return 'badge-paye';
                    if(s === 'En retard') return 'badge-retard';
                    return 'badge-attente';
                }
            }));
            
            Alpine.effect(() => {
                // watch perPage to reset page
                Alpine.store('perPageTrigger', document.querySelector('[x-model="perPage"]')?.value);
                const comp = document.querySelector('[x-data]')?._x_dataStack[0];
                if(comp && comp.currentPage > comp.totalPages()) comp.currentPage = 1;
            });
        });
    </script>
    @endpush
</x-app-layout>
