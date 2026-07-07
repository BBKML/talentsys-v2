<x-app-layout>
    <x-slot name="title">Factures - Finance</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Finance / Factures</h2>
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
        
        .status-emise { background: #E0F2FE; color: #0284C7; }
        .status-brouillon { background: #F3F4F6; color: #6B7280; }
        .status-payee { background: #D1FAE5; color: #059669; }
        .status-annulee { background: #FEE2E2; color: #DC2626; }
        
        .text-num { color: #4F46E5; font-weight: 700; font-family: monospace; }
        .text-montant-total { color: #64748B; font-weight: 500; }
        .text-montant-paye { color: #059669; font-weight: 700; }
        .text-montant-restant { color: #DC2626; font-weight: 700; }

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
    $fraisScolariteJson = $fraisScolarite->map(fn($f) => ['id'=>$f->id, 'id_type_frais'=>$f->id_type_frais, 'montant'=>$f->montant]);
    $typesFraisJson = $typesFrais->map(fn($t) => ['id'=>$t->id, 'libelle'=>$t->libelle]);
    
    // Preparer les factures avec calcul du paye et reste basé sur les paiements
    $facturesJson = $factures->map(function($f) use($paiements) {
        $montant_paye = $paiements->where('id_inscription', $f->id_inscription)
                                  ->where('id_frais_scolarite', $f->id_frais_scolarite)
                                  ->sum('montant_verse');
        $reste = max(0, $f->montant_total - $montant_paye);
        
        return [
            'id'=>$f->id, 
            'numero_facture'=>$f->numero_facture, 
            'id_inscription'=>$f->id_inscription, 
            'id_frais_scolarite'=>$f->id_frais_scolarite, 
            'montant_total'=>$f->montant_total, 
            'date_facture'=>$f->date_facture, 
            'statut_facture'=>$f->statut_facture,
            'montant_paye'=>$montant_paye,
            'reste'=>$reste
        ];
    });
    @endphp

    <div x-data="facturesPage({{ $inscriptionsJson }}, {{ $fraisScolariteJson }}, {{ $typesFraisJson }}, {{ $facturesJson }})" class="space-y-6 relative">
        
        <!-- Header -->
        <div class="flex items-center flex-wrap gap-3 justify-between">
            <div>
                <h1 class="text-2xl font-bold" style="color:#1E293B">Factures</h1>
                <p class="text-sm mt-1" style="color:#94A3B8" x-text="filteredData().length + ' facture(s)'"></p>
            </div>
            <div class="flex gap-2">
                <button class="btn-export">
                    <i class="ri-download-2-line"></i> Exporter
                </button>
                <button class="btn-primary" @click="showModal = true">
                    <i class="ri-add-line"></i> Nouvelle Facture
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
                            <th>N° FACTURE</th>
                            <th>ÉTUDIANT</th>
                            <th>TYPE FRAIS</th>
                            <th>TOTAL</th>
                            <th>PAYÉ</th>
                            <th>RESTE</th>
                            <th>DATE</th>
                            <th>STATUT</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="f in paginatedData()" :key="f.id">
                            <tr class="f-table-row">
                                <td class="text-num" x-text="f.numero_facture"></td>
                                <td>
                                    <div class="font-bold text-[#1E293B]" x-text="f.etudiant"></div>
                                </td>
                                <td><span class="badge badge-frais" x-text="f.frais_libelle"></span></td>
                                <td class="text-montant-total" x-text="format(f.montant_total)"></td>
                                <td class="text-montant-paye" x-text="format(f.montant_paye)"></td>
                                <td class="text-montant-restant" x-text="format(f.reste)"></td>
                                <td x-text="f.date_facture ? f.date_facture.split('T')[0] : ''"></td>
                                <td><span class="badge" :class="getBadgeClass(f.statut_facture)" x-text="f.statut_facture"></span></td>
                                <td>
                                    <div class="flex items-center gap-2 text-lg">
                                        <button class="text-teal-600 hover:text-teal-800" @click="printFacture(f)" title="Imprimer">
                                            <i class="ri-printer-line"></i>
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
                            <td colspan="9" class="text-center py-8 text-gray-400">Aucune facture trouvée</td>
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

        <!-- Modal Nouvelle Facture -->
        <div class="modal-overlay" x-show="showModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center text-pink-600">
                            <i class="ri-file-list-3-line"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Nouvelle Facture</h3>
                    </div>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-xl"></i></button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="f-label">N° Facture <span class="text-red-500">*</span></label>
                        <input type="text" class="f-select" x-model="form.numero_facture">
                    </div>

                    <div>
                        <label class="f-label">Inscription <span class="text-red-500">*</span></label>
                        <select class="f-select" x-model="form.id_inscription">
                            <option value="">Sélectionner une inscription...</option>
                            <template x-for="i in inscriptions" :key="i.id">
                                <option :value="i.id" x-text="i.matricule + ' - ' + i.etudiant"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="f-label">Type de Frais <span class="text-red-500">*</span></label>
                        <select class="f-select" x-model="form.id_frais">
                            <option value="">Rechercher...</option>
                            <template x-for="f in fraisOptions()" :key="f.id">
                                <option :value="f.id" x-text="f.libelle"></option>
                            </template>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="f-label">Montant Total (FCFA)</label>
                            <div class="f-select bg-gray-50 flex items-center gap-2 text-gray-500" style="pointer-events:none;">
                                <i class="ri-money-dollar-circle-line"></i>
                                <span x-text="soldeDetails().total"></span>
                            </div>
                        </div>
                        <div>
                            <label class="f-label">Date Facture <span class="text-red-500">*</span></label>
                            <input type="date" class="f-select" x-model="form.date">
                        </div>
                    </div>

                    <div>
                        <label class="f-label">Statut Facture</label>
                        <select class="f-select" x-model="form.statut">
                            <option value="Brouillon">Brouillon</option>
                            <option value="Émise">Émise</option>
                            <option value="Payée">Payée</option>
                        </select>
                    </div>

                    <div>
                        <label class="f-label">Paiements enregistrés</label>
                        <div class="solde-box bg-gray-50 text-gray-500 border border-gray-100">
                            <i class="ri-wallet-3-line"></i>
                            <span>Payé: <span x-text="soldeDetails().paye"></span> | Reste: <span x-text="soldeDetails().reste"></span></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-8">
                    <button class="px-4 py-2 text-sm font-semibold text-red-500 hover:bg-red-50 rounded-lg" @click="showModal = false">Annuler</button>
                    <button class="btn-primary" @click="saveFacture()">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('facturesPage', (inscriptions, fraisScolarite, typesFrais, factures) => ({
                inscriptions, fraisScolarite, typesFrais, factures,
                search: '',
                perPage: 10,
                currentPage: 1,
                showModal: false,
                form: {
                    numero_facture: 'FACT-FRA-' + new Date().getFullYear() + '-' + Math.floor(Math.random()*1000).toString().padStart(3,'0'),
                    id_inscription: '',
                    id_frais: '',
                    date: new Date().toISOString().split('T')[0],
                    statut: 'Brouillon'
                },
                
                init() {
                    // Enrich factures
                    this.factures = this.factures.map(f => {
                        const ins = this.inscriptions.find(i => i.id === f.id_inscription);
                        const frais = this.fraisScolarite.find(fr => fr.id === f.id_frais_scolarite);
                        let tf = null;
                        if(frais) tf = this.typesFrais.find(t => t.id === frais.id_type_frais);
                        
                        return {
                            ...f,
                            etudiant: ins ? ins.etudiant : 'Inconnu',
                            matricule: ins ? ins.matricule : '',
                            frais_libelle: tf ? tf.libelle : 'Frais de scolarité'
                        };
                    });
                },

                filteredData() {
                    let data = this.factures;
                    if(this.search.trim() !== '') {
                        const q = this.search.toLowerCase();
                        data = data.filter(f => f.etudiant.toLowerCase().includes(q) || f.numero_facture.toLowerCase().includes(q));
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
                
                getBadgeClass(s) {
                    if(s === 'Payée') return 'status-payee';
                    if(s === 'Émise') return 'status-emise';
                    if(s === 'Annulée') return 'status-annulee';
                    return 'status-brouillon';
                },
                
                // Form logic
                fraisOptions() {
                    return this.fraisScolarite.map(f => {
                        const t = this.typesFrais.find(ty => ty.id === f.id_type_frais);
                        return { id: f.id, libelle: t ? t.libelle : 'Frais', montant: f.montant };
                    });
                },
                soldeDetails() {
                    if(!this.form.id_frais) return { total: '0 FCFA', paye: '0 F', reste: '0 F' };
                    const f = this.fraisScolarite.find(x => x.id == this.form.id_frais);
                    const total = f ? f.montant : 0;
                    // Logic for real calculation would sum paiements here
                    return { total: this.format(total), paye: '0 F', reste: this.format(total) };
                },
                async saveFacture() {
                    if(!this.form.numero_facture || !this.form.id_inscription || !this.form.id_frais || !this.form.date) {
                        alert('Veuillez remplir tous les champs obligatoires (*)');
                        return;
                    }
                    try {
                        const response = await fetch('{{ route('finance.factures.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.form)
                        });
                        const data = await response.json();
                        if(data.success) {
                            alert(data.message || 'Facture enregistrée.');
                            window.location.reload();
                        } else {
                            alert(data.message || 'Une erreur est survenue.');
                        }
                    } catch (error) {
                        alert('Erreur réseau ou serveur.');
                        console.error(error);
                    }
                },

                printFacture(f) {
                    const w = window.open('', '_blank');
                    w.document.write(`
                    <html>
                    <head>
                        <title>Facture ${f.numero_facture}</title>
                        <style>
                            body { font-family: sans-serif; padding: 40px; color:#1E293B; }
                            .header { text-align: center; margin-bottom: 40px; }
                            .box { border: 1px solid #E2E8F0; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
                            .row { display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px dashed #E2E8F0; padding-bottom: 5px; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h2>FACTURE</h2>
                            <p style="color:#64748B">${f.numero_facture}</p>
                        </div>
                        <div class="box">
                            <div class="row"><span>Étudiant :</span> <strong>${f.etudiant}</strong></div>
                            <div class="row"><span>Date :</span> <strong>${f.date_facture ? f.date_facture.split('T')[0] : ''}</strong></div>
                            <div class="row"><span>Statut :</span> <strong>${f.statut_facture}</strong></div>
                            <div class="row"><span>Montant Total :</span> <strong style="color:#059669; font-size:18px;">${this.format(f.montant_total)}</strong></div>
                            <div class="row"><span>Reste à payer :</span> <strong style="color:#DC2626;">${this.format(f.reste)}</strong></div>
                        </div>
                        <script>window.print();<\/script>
                    </body>
                    </html>
                    `);
                }
            }));
            
            Alpine.effect(() => {
                Alpine.store('perPageTrigger4', document.querySelector('[x-model="perPage"]')?.value);
                const comp = document.querySelector('[x-data]')?._x_dataStack[0];
                if(comp && comp.currentPage > comp.totalPages()) comp.currentPage = 1;
            });
        });
    </script>
    @endpush
</x-app-layout>
