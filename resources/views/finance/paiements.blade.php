<x-app-layout>
    <x-slot name="title">Historique des Paiements - Finance</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Finance / Paiements</h2>
    </x-slot>

    @push('styles')
    <style>
        .av-card { background:#fff; border-radius:16px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.02); border:1px solid #F1F5F9; }
        .f-select { border:1.5px solid #E2E8F0; border-radius:10px; padding:10px 14px; font-size:13px; color:#1E293B; font-weight:500; outline:none; transition:all .2s ease; background-color: #fff; width:100%; }
        .f-select:focus { border-color:#5A67D8; box-shadow:0 0 0 3px rgba(90,103,216,.1); }
        .f-table-container { background:#fff; border-radius:16px; border:1px solid #F1F5F9; box-shadow:0 2px 10px rgba(0,0,0,0.02); overflow:hidden; }
        .f-table-header th { background:#F8FAFC; padding:14px 16px; font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; text-align:left; border-bottom:1px solid #F1F5F9; white-space:nowrap; }
        .f-table-row td { padding:14px 16px; font-size:13px; color:#1E293B; border-bottom:1px solid #F8FAFC; vertical-align:middle; }
        .f-table-row:hover td { background:#F8FAFC; }
        
        .badge { padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 600; display: inline-block; text-align:center; white-space: nowrap; }
        .badge-frais { background: #FCE7F3; color: #DB2777; }
        .badge-mode { background: #E0F2FE; color: #0284C7; }
        .badge-tranche-detail { border: 1px solid #C7D2FE; color: #4F46E5; background: #EEF2FF; border-radius: 6px; padding: 2px 6px; font-size: 10px; margin-right: 4px; margin-bottom: 4px; display: inline-block; }
        
        .text-montant-verse { color: #059669; font-weight: 700; }
        .text-ref { color: #4F46E5; font-weight: 700; }

        .btn-export { border: 1px solid #E2E8F0; background: #fff; color: #475569; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; display:flex; align-items:center; gap: 6px; transition:all 0.2s; }
        .btn-export:hover { background: #F8FAFC; }
        .btn-primary { background: #831843; color: white; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; display:flex; align-items:center; gap: 6px; transition:all 0.2s; }
        .btn-primary:hover { background: #9D174D; }
        
        .pagination-btn { width:32px; height:32px; border-radius:8px; border:1px solid #E2E8F0; display:flex; align-items:center; justify-content:center; color:#64748B; font-size:13px; background:#fff; }
        .pagination-btn.active { background:#831843; color:white; border-color:#831843; font-weight:bold; }
        
        /* Modal */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); z-index: 50; display:flex; align-items:center; justify-content:center; }
        .modal-content { background: #F8FAFC; border-radius: 16px; width: 100%; max-width: 600px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto; }
        .f-label { display:block; font-size:11px; font-weight:600; color:#64748B; margin-bottom:6px; }
        .solde-box { background: #EEF2FF; border-radius: 8px; padding: 12px; margin-top: 4px; font-size: 12px; color: #475569; display: flex; align-items:center; gap:8px;}
    </style>
    @endpush

    @php
    $inscriptionsJson = $inscriptions->map(fn($i) => ['id'=>$i->id, 'etudiant'=>$i->etudiant?->nom.' '.$i->etudiant?->prenom, 'matricule'=>$i->etudiant?->matricule]);
    $modesPaiementJson = $modesPaiement->map(fn($m) => ['id'=>$m->id, 'libelle'=>$m->libelle]);
    $fraisScolariteJson = $fraisScolarite->map(fn($f) => ['id'=>$f->id, 'id_type_frais'=>$f->id_type_frais, 'montant'=>$f->montant]);
    $typesFraisJson = $typesFrais->map(fn($t) => ['id'=>$t->id, 'libelle'=>$t->libelle]);
    
    // Preparer les paiements avec le detail des tranches
    $paiementsJson = $paiements->map(function($p) use($paiementTranchesDetail, $tranchesPrevues, $echeanciers) {
        $details = $paiementTranchesDetail->where('id_paiement', $p->id)->values();
        $distribution = $details->map(function($d) use ($tranchesPrevues, $echeanciers) {
            $t = $tranchesPrevues->firstWhere('id', $d->id_tranche_prevu);
            if(!$t) return null;
            // Trouver l'index de la tranche
            $echTranches = $tranchesPrevues->where('id_echeancier_scolarite', $t->id_echeancier_scolarite)->values();
            $index = $echTranches->search(function($item) use ($t) { return $item->id == $t->id; });
            $suffix = ($index === 0) ? 'ère' : 'ème';
            return [
                'nom' => ($index+1).$suffix.' tranche',
                'montant' => $d->montant_alloue
            ];
        })->filter()->values();
        
        return [
            'id'=>$p->id, 
            'reference'=>$p->reference, 
            'id_inscription'=>$p->id_inscription, 
            'id_frais_scolarite'=>$p->id_frais_scolarite,
            'id_mode_paiement'=>$p->id_mode_paiement,
            'montant_verse'=>$p->montant_verse, 
            'date'=>$p->date,
            'distribution'=>$distribution
        ];
    });
    @endphp

    <div x-data="paiementsPage({{ $inscriptionsJson }}, {{ $modesPaiementJson }}, {{ $fraisScolariteJson }}, {{ $typesFraisJson }}, {{ $paiementsJson }})" class="space-y-6 relative">
        
        <!-- Header -->
        <div class="flex items-center flex-wrap gap-3 justify-between">
            <div>
                <h1 class="text-2xl font-bold" style="color:#1E293B">Historique des Paiements</h1>
                <p class="text-sm mt-1" style="color:#94A3B8" x-text="filteredData().length + ' paiement(s)'"></p>
            </div>
            <div class="flex gap-2">
                <button class="btn-export">
                    <i class="ri-download-2-line"></i> Exporter
                </button>
                <button class="btn-primary" @click="showModal = true">
                    <i class="ri-add-line"></i> Nouveau Paiement
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="f-table-container">
            <div class="p-3 border-b border-gray-100 bg-white flex items-center gap-4">
                <div class="relative w-full max-w-[300px]">
                    <i class="ri-search-line absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" x-model="search" placeholder="Rechercher..." class="f-select pl-9">
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Lignes/page :</span>
                    <select x-model="perPage" class="f-select max-w-[80px] py-1 px-2 text-sm">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="f-table-header">
                            <th>RÉFÉRENCE</th>
                            <th>ÉTUDIANT</th>
                            <th>TYPE FRAIS</th>
                            <th>DISTRIBUTION TRANCHES</th>
                            <th>MONTANT VERSÉ</th>
                            <th>MODE</th>
                            <th>DATE</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="p in paginatedData()" :key="p.id">
                            <tr class="f-table-row">
                                <td class="text-ref" x-text="p.reference"></td>
                                <td>
                                    <div class="font-bold text-[#1E293B]" x-text="p.etudiant"></div>
                                </td>
                                <td><span class="badge badge-frais" x-text="p.frais_libelle"></span></td>
                                <td class="max-w-[250px] flex-wrap">
                                    <template x-for="d in p.distribution">
                                        <span class="badge-tranche-detail" x-text="d.nom + ': ' + format(d.montant)"></span>
                                    </template>
                                </td>
                                <td class="text-montant-verse" x-text="format(p.montant_verse)"></td>
                                <td><span class="badge badge-mode" x-text="p.mode_libelle"></span></td>
                                <td x-text="p.date ? p.date.split('T')[0] : ''"></td>
                                <td>
                                    <div class="flex items-center gap-2 text-lg">
                                        <button class="text-green-600 hover:text-green-800" @click="printRecu(p)" title="Imprimer reçu">
                                            <i class="ri-file-text-line"></i>
                                        </button>
                                        <button class="text-blue-600 hover:text-blue-800" title="Modifier">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                        <button class="text-red-500 hover:text-red-700" title="Supprimer">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="paginatedData().length === 0">
                            <td colspan="8" class="text-center py-8 text-gray-400">Aucun paiement trouvé</td>
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

        <!-- Modal Nouveau Paiement -->
        <div class="modal-overlay" x-show="showModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center text-pink-600">
                            <i class="ri-file-list-3-line"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Nouveau Paiement</h3>
                    </div>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-xl"></i></button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="f-label">Inscription (Étudiant) <span class="text-red-500">*</span></label>
                        <select class="f-select" x-model="form.id_inscription">
                            <option value="">Sélectionner une inscription...</option>
                            <template x-for="i in inscriptions" :key="i.id">
                                <option :value="i.id" x-text="i.matricule + ' - ' + i.etudiant"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="f-label">Frais de scolarité <span class="text-red-500">*</span></label>
                        <select class="f-select" x-model="form.id_frais">
                            <option value="">Sélectionner les frais...</option>
                            <template x-for="f in fraisOptions()" :key="f.id">
                                <option :value="f.id" x-text="f.libelle + ' (' + format(f.montant) + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="f-label">Solde scolarité</label>
                        <div class="solde-box">
                            <i class="ri-wallet-3-line text-blue-500"></i>
                            <span x-html="soldeDetails()"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="f-label">Montant versé (FCFA) <span class="text-red-500">*</span></label>
                            <input type="number" class="f-select" x-model="form.montant" placeholder="0.0">
                        </div>
                        <div>
                            <label class="f-label">Mode Paiement <span class="text-red-500">*</span></label>
                            <select class="f-select" x-model="form.id_mode">
                                <option value="">Sélectionner...</option>
                                <template x-for="m in modesPaiement" :key="m.id">
                                    <option :value="m.id" x-text="m.libelle"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="f-label">Date de paiement <span class="text-red-500">*</span></label>
                            <input type="date" class="f-select" x-model="form.date">
                        </div>
                        <div>
                            <label class="f-label">Référence</label>
                            <input type="text" class="f-select" x-model="form.reference">
                        </div>
                    </div>
                    
                    <div>
                        <label class="f-label">Statut</label>
                        <select class="f-select">
                            <option>Validé</option>
                            <option>En attente</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-8">
                    <button class="px-4 py-2 text-sm font-semibold text-red-500 hover:bg-red-50 rounded-lg" @click="showModal = false">Annuler</button>
                    <button class="btn-primary" @click="savePaiement()">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('paiementsPage', (inscriptions, modesPaiement, fraisScolarite, typesFrais, paiements) => ({
                inscriptions, modesPaiement, fraisScolarite, typesFrais, paiements,
                search: '',
                perPage: 10,
                currentPage: 1,
                showModal: false,
                form: {
                    id_inscription: '',
                    id_frais: '',
                    montant: '',
                    id_mode: '',
                    date: new Date().toISOString().split('T')[0],
                    reference: 'PAY-' + new Date().getFullYear() + '-' + Math.floor(Math.random()*1000).toString().padStart(3,'0')
                },
                
                init() {
                    // Enrich paiements
                    this.paiements = this.paiements.map(p => {
                        const ins = this.inscriptions.find(i => i.id === p.id_inscription);
                        const frais = this.fraisScolarite.find(f => f.id === p.id_frais_scolarite);
                        let tf = null;
                        if(frais) tf = this.typesFrais.find(t => t.id === frais.id_type_frais);
                        const mode = this.modesPaiement.find(m => m.id === p.id_mode_paiement);
                        
                        return {
                            ...p,
                            etudiant: ins ? ins.etudiant : 'Inconnu',
                            matricule: ins ? ins.matricule : '',
                            frais_libelle: tf ? tf.libelle : 'Frais de scolarité',
                            mode_libelle: mode ? mode.libelle : 'Espèces'
                        };
                    });
                },

                filteredData() {
                    let data = this.paiements;
                    if(this.search.trim() !== '') {
                        const q = this.search.toLowerCase();
                        data = data.filter(p => p.etudiant.toLowerCase().includes(q) || p.reference.toLowerCase().includes(q));
                    }
                    return data;
                },
                
                paginatedData() {
                    const start = (this.currentPage - 1) * this.perPage;
                    return this.filteredData().slice(start, start + parseInt(this.perPage));
                },
                
                totalPages() { return Math.ceil(this.filteredData().length / this.perPage) || 1; },
                prevPage() { if(this.currentPage > 1) this.currentPage--; },
                nextPage() { if(this.currentPage < this.totalPages()) this.currentPage++; },
                
                paginationText() {
                    const total = this.filteredData().length;
                    if(total === 0) return '0 résultat';
                    const start = (this.currentPage - 1) * this.perPage + 1;
                    const end = Math.min(this.currentPage * this.perPage, total);
                    return `${start}–${end} sur ${total} résultat(s)`;
                },

                format(v) { return Number(v).toLocaleString('fr-FR') + ' FCFA'; },
                
                // Form logic
                fraisOptions() {
                    return this.fraisScolarite.map(f => {
                        const t = this.typesFrais.find(ty => ty.id === f.id_type_frais);
                        return { id: f.id, libelle: t ? t.libelle : 'Frais', montant: f.montant };
                    });
                },
                soldeDetails() {
                    if(!this.form.id_inscription || !this.form.id_frais) return 'Total: 0 F | Versé: 0 F | Restant: 0 F';
                    // Simulation du calcul (dans un vrai cas on recupere l'échéancier)
                    const f = this.fraisScolarite.find(x => x.id == this.form.id_frais);
                    const total = f ? f.montant : 0;
                    const verse = this.paiements.filter(p => p.id_inscription == this.form.id_inscription && p.id_frais_scolarite == this.form.id_frais).reduce((s,x)=>s+Number(x.montant_verse), 0);
                    return `Total: ${this.format(total)} | Versé: ${this.format(verse)} | Restant: ${this.format(Math.max(0, total - verse))}`;
                },
                async savePaiement() {
                    if(!this.form.id_inscription || !this.form.id_frais || !this.form.montant || !this.form.id_mode) {
                        alert('Veuillez remplir tous les champs obligatoires (*)');
                        return;
                    }
                    try {
                        const response = await fetch('{{ route('finance.paiements.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.form)
                        });
                        const data = await response.json();
                        if(data.success) {
                            alert(data.message || 'Paiement enregistré avec succès.');
                            window.location.reload();
                        } else {
                            alert(data.message || 'Une erreur est survenue.');
                        }
                    } catch (error) {
                        alert('Erreur réseau ou serveur.');
                        console.error(error);
                    }
                },

                printRecu(p) {
                    const w = window.open('', '_blank');
                    w.document.write(`
                    <html>
                    <head>
                        <title>Reçu ${p.reference}</title>
                        <style>
                            body { font-family: sans-serif; padding: 40px; color:#1E293B; }
                            .header { text-align: center; margin-bottom: 40px; }
                            .box { border: 1px solid #E2E8F0; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
                            .row { display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px dashed #E2E8F0; padding-bottom: 5px; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h2>REÇU DE PAIEMENT</h2>
                            <p style="color:#64748B">${p.reference}</p>
                        </div>
                        <div class="box">
                            <div class="row"><span>Étudiant :</span> <strong>${p.etudiant}</strong></div>
                            <div class="row"><span>Date :</span> <strong>${p.date ? p.date.split('T')[0] : ''}</strong></div>
                            <div class="row"><span>Mode de paiement :</span> <strong>${p.mode_libelle}</strong></div>
                            <div class="row"><span>Montant versé :</span> <strong style="color:#059669; font-size:18px;">${this.format(p.montant_verse)}</strong></div>
                        </div>
                        <p style="text-align:center; font-size:12px; color:#94A3B8; margin-top:50px;">Document généré le ${new Date().toLocaleDateString()}</p>
                        <script>window.print();<\/script>
                    </body>
                    </html>
                    `);
                }
            }));
            
            Alpine.effect(() => {
                Alpine.store('perPageTrigger3', document.querySelector('[x-model="perPage"]')?.value);
                const comp = document.querySelector('[x-data]')?._x_dataStack[0];
                if(comp && comp.currentPage > comp.totalPages()) comp.currentPage = 1;
            });
        });
    </script>
    @endpush
</x-app-layout>
