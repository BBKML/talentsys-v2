<x-app-layout>
    <x-slot name="title">Module Achats</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Module Achats</h2>
    </x-slot>

    @push('styles')
    <style>
        .badge-statut-brouillon { background: #E2E8F0; color: #475569; }
        .badge-statut-valide { background: #DBEAFE; color: #1E40AF; }
        .badge-statut-envoye { background: #FEF3C7; color: #92400E; }
        .badge-statut-recu { background: #D1FAE5; color: #065F46; }
        .badge-statut-annule { background: #FEE2E2; color: #991B1B; }
        .badge-statut-en_attente { background: #FEF3C7; color: #92400E; }
        .badge-statut-payee { background: #D1FAE5; color: #065F46; }

        .btn-tab {
            padding: 12px 16px; font-size: 13px; font-weight: 600;
            color: #64748B; border-bottom: 3px solid transparent;
            transition: all 0.2s; display: flex; align-items: center; gap: 8px;
        }
        .btn-tab.active {
            color: #4338CA; border-bottom-color: #4338CA;
        }
        
        .kpi-card {
            background: #fff; border-radius: 12px; padding: 16px;
            border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 12px; flex: 1;
        }

        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4); z-index: 50; display: flex;
            align-items: center; justify-content: center;
        }
        .modal-content {
            background: #fff; border-radius: 16px; width: 100%; max-width: 650px;
            padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            max-height: 90vh; overflow-y: auto;
        }
        
        .f-input {
            width: 100%; border: 1.5px solid #E2E8F0; border-radius: 8px;
            padding: 8px 12px; font-size: 13px; outline: none; transition: border-color 0.15s;
        }
        .f-input:focus { border-color: #4338CA; }
        
        .table-action-btn {
            width: 28px; height: 28px; border-radius: 6px; display: inline-flex;
            align-items: center; justify-content: center; transition: all 0.15s;
        }
        .table-action-btn:hover { background: #F1F5F9; }
    </style>
    @endpush

    @php
        $fournisseursJson = $fournisseurs->map(fn($f) => [
            'id' => $f->id,
            'nom' => $f->nom,
            'numero_contribuable' => $f->numero_contribuable,
            'telephone' => $f->telephone,
            'email' => $f->email,
            'adresse' => $f->adresse,
            'id_compte_charge' => $f->id_compte_charge,
            'compte_nom' => $f->compte ? $f->compte->numero_compte . ' - ' . $f->compte->libelle : '—',
            'actif' => (bool)$f->actif
        ])->toJson();

        $planComptableJson = $planComptable->map(fn($c) => [
            'id' => $c->id,
            'numero_compte' => $c->numero_compte,
            'libelle' => $c->libelle
        ])->toJson();

        $bonsCommandeJson = $bonsCommande->map(fn($b) => [
            'id' => $b->id,
            'id_fournisseur' => $b->id_fournisseur,
            'fournisseur_nom' => $b->fournisseur?->nom ?? '—',
            'numero' => $b->numero,
            'date_commande' => $b->date_commande,
            'statut' => $b->statut,
            'notes' => $b->notes,
            'total' => (float)$b->total,
            'lignes' => $b->lignes->map(fn($l) => [
                'id' => $l->id,
                'designation' => $l->designation,
                'quantite' => (float)$l->quantite,
                'prix_unitaire' => (float)$l->prix_unitaire,
                'montant' => (float)$l->montant
            ])
        ])->toJson();

        $bonsReceptionJson = $bonsReception->map(fn($r) => [
            'id' => $r->id,
            'id_bon_commande' => $r->id_bon_commande,
            'commande_numero' => $r->bonCommande?->numero ?? '—',
            'fournisseur_nom' => $r->bonCommande?->fournisseur?->nom ?? '—',
            'date_reception' => $r->date_reception,
            'notes' => $r->notes
        ])->toJson();

        $facturesJson = $factures->map(fn($f) => [
            'id' => $f->id,
            'id_fournisseur' => $f->id_fournisseur,
            'fournisseur_nom' => $f->fournisseur?->nom ?? '—',
            'id_bon_commande' => $f->id_bon_commande,
            'commande_numero' => $f->bonCommande?->numero ?? '—',
            'numero_facture' => $f->numero_facture,
            'montant' => (float)$f->montant,
            'date_facture' => $f->date_facture,
            'date_echeance' => $f->date_echeance,
            'statut' => $f->statut,
            'notes' => $f->notes,
            'url_document' => $f->url_document
        ])->toJson();

        $signaturesJson = $signatures->map(fn($s) => [
            'id' => $s->id,
            'nom' => $s->nom,
            'fonction' => $s->fonction,
            'url_signature' => $s->url_signature,
            'actif' => (bool)$s->actif
        ])->toJson();
    @endphp

    <div x-data="achatsPage({{ $fournisseursJson }}, {{ $planComptableJson }}, {{ $bonsCommandeJson }}, {{ $bonsReceptionJson }}, {{ $facturesJson }}, {{ $signaturesJson }}, {{ (int)request('tab', 0) }})" class="space-y-6">

        <!-- Title & Subtitle -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Module Achats</h1>
                <p class="text-sm text-slate-500 mt-1" x-text="bonsCommande.length + ' commande(s) au total'"></p>
            </div>
        </div>

        <!-- KPI Row -->
        <div class="flex flex-col md:flex-row gap-4">
            <div class="kpi-card">
                <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="ri-receipt-long-line text-lg"></i>
                </div>
                <div>
                    <div class="text-lg font-bold text-indigo-600" x-text="bonsCommande.length"></div>
                    <div class="text-[11px] text-slate-400">Commandes</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                    <i class="ri-truck-line text-lg"></i>
                </div>
                <div>
                    <div class="text-lg font-bold text-amber-600" x-text="bonsCommande.filter(b => b.statut === 'envoye').length"></div>
                    <div class="text-[11px] text-slate-400">En cours</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="ri-checkbox-circle-line text-lg"></i>
                </div>
                <div>
                    <div class="text-lg font-bold text-emerald-600" x-text="bonsCommande.filter(b => b.statut === 'recu').length"></div>
                    <div class="text-[11px] text-slate-400">Reçues</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="w-10 h-10 rounded-lg bg-teal-50 flex items-center justify-center text-teal-600">
                    <i class="ri-money-dollar-circle-line text-lg"></i>
                </div>
                <div>
                    <div class="text-lg font-bold text-teal-600" x-text="format(bonsCommande.reduce((acc, b) => acc + b.total, 0))"></div>
                    <div class="text-[11px] text-slate-400">Engagé</div>
                </div>
            </div>
        </div>

        <!-- Tab Bar -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-x-auto flex">
            <button class="btn-tab" :class="activeTab === 0 ? 'active' : ''" @click="activeTab = 0">
                <i class="ri-receipt-long-line"></i> Commandes
            </button>
            <button class="btn-tab" :class="activeTab === 1 ? 'active' : ''" @click="activeTab = 1">
                <i class="ri-inbox-archive-line"></i> Réceptions
            </button>
            <button class="btn-tab" :class="activeTab === 2 ? 'active' : ''" @click="activeTab = 2">
                <i class="ri-bill-line"></i> Factures
            </button>
            <button class="btn-tab" :class="activeTab === 3 ? 'active' : ''" @click="activeTab = 3">
                <i class="ri-radar-line"></i> Suivi
            </button>
            <button class="btn-tab" :class="activeTab === 4 ? 'active' : ''" @click="activeTab = 4">
                <i class="ri-truck-line"></i> Fournisseurs
            </button>
            <button class="btn-tab" :class="activeTab === 5 ? 'active' : ''" @click="activeTab = 5">
                <i class="ri-pen-nib-line"></i> Signatures
            </button>
        </div>

        <!-- TAB CONTENT -->
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            
            <!-- ── TAB 0: COMMANDES ── -->
            <div x-show="activeTab === 0" class="space-y-4" style="display:none;">
                <div class="flex items-center gap-3">
                    <input type="text" x-model="searchCommande" placeholder="Rechercher par N° ou fournisseur..." class="f-input max-w-xs">
                    <button class="ml-auto bg-indigo-700 hover:bg-indigo-800 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-1.5 text-xs" @click="openCommandeModal()">
                        <i class="ri-add-line"></i> Nouveau Bon
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="p-3">N° COMMANDE</th>
                                <th class="p-3">FOURNISSEUR</th>
                                <th class="p-3">DATE</th>
                                <th class="p-3">TOTAL</th>
                                <th class="p-3">STATUT</th>
                                <th class="p-3 text-right">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <template x-for="b in filteredCommandes()" :key="b.id">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-3 font-bold text-slate-800" x-text="b.numero"></td>
                                    <td class="p-3 text-slate-600" x-text="b.fournisseur_nom"></td>
                                    <td class="p-3 text-slate-500" x-text="b.date_commande"></td>
                                    <td class="p-3 font-semibold text-slate-800" x-text="format(b.total)"></td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="'badge-statut-' + b.statut" x-text="getStatutLabel(b.statut)"></span>
                                    </td>
                                    <td class="p-3 text-right space-x-1">
                                        <button class="table-action-btn text-indigo-600" title="Lignes" @click="openLignesModal(b)">
                                            <i class="ri-list-check"></i>
                                        </button>
                                        <button class="table-action-btn text-teal-600" title="Imprimer" @click="printCommande(b)">
                                            <i class="ri-printer-line"></i>
                                        </button>
                                        <button class="table-action-btn text-blue-600" title="Modifier" @click="openCommandeModal(b)">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button class="table-action-btn text-red-600" title="Supprimer" @click="deleteCommande(b.id)">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── TAB 1: RÉCEPTIONS ── -->
            <div x-show="activeTab === 1" class="space-y-4" style="display:none;">
                <div class="flex items-center gap-3">
                    <button class="ml-auto bg-indigo-700 hover:bg-indigo-800 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-1.5 text-xs" @click="openReceptionModal()">
                        <i class="ri-add-line"></i> Nouvelle Réception
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="p-3">COMMANDE</th>
                                <th class="p-3">FOURNISSEUR</th>
                                <th class="p-3">DATE RÉCEPTION</th>
                                <th class="p-3">NOTES</th>
                                <th class="p-3 text-right">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <template x-for="r in bonsReception" :key="r.id">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-3 font-bold text-slate-800" x-text="r.commande_numero"></td>
                                    <td class="p-3 text-slate-600" x-text="r.fournisseur_nom"></td>
                                    <td class="p-3 text-slate-500" x-text="r.date_reception"></td>
                                    <td class="p-3 text-slate-400 italic text-xs" x-text="r.notes || '—'"></td>
                                    <td class="p-3 text-right space-x-1">
                                        <button class="table-action-btn text-blue-600" title="Modifier" @click="openReceptionModal(r)">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button class="table-action-btn text-red-600" title="Supprimer" @click="deleteReception(r.id)">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── TAB 2: FACTURES ── -->
            <div x-show="activeTab === 2" class="space-y-4" style="display:none;">
                <div class="flex items-center gap-3">
                    <button class="ml-auto bg-indigo-700 hover:bg-indigo-800 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-1.5 text-xs" @click="openFactureModal()">
                        <i class="ri-add-line"></i> Nouvelle Facture
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="p-3">N° FACTURE</th>
                                <th class="p-3">FOURNISSEUR</th>
                                <th class="p-3">MONTANT</th>
                                <th class="p-3">DATE</th>
                                <th class="p-3">ÉCHÉANCE</th>
                                <th class="p-3">STATUT</th>
                                <th class="p-3 text-right">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <template x-for="f in factures" :key="f.id">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-3 font-bold text-slate-800" x-text="f.numero_facture"></td>
                                    <td class="p-3 text-slate-600" x-text="f.fournisseur_nom"></td>
                                    <td class="p-3 font-semibold text-slate-800" x-text="format(f.montant)"></td>
                                    <td class="p-3 text-slate-500" x-text="f.date_facture"></td>
                                    <td class="p-3 text-slate-500" x-text="f.date_echeance || '—'"></td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="'badge-statut-' + f.statut" x-text="getStatutLabel(f.statut)"></span>
                                    </td>
                                    <td class="p-3 text-right space-x-1">
                                        <template x-if="f.url_document">
                                            <a :href="f.url_document" target="_blank" class="table-action-btn text-teal-600" title="Télécharger">
                                                <i class="ri-download-line"></i>
                                            </a>
                                        </template>
                                        <button class="table-action-btn text-blue-600" title="Modifier" @click="openFactureModal(f)">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button class="table-action-btn text-red-600" title="Supprimer" @click="deleteFacture(f.id)">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── TAB 3: SUIVI ── -->
            <div x-show="activeTab === 3" class="space-y-6" style="display:none;">
                <div>
                    <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3">Résumé des commandes</h3>
                    <div class="flex flex-wrap gap-3">
                        <div class="border border-slate-100 rounded-xl px-5 py-3 bg-slate-50/50 min-w-[120px]">
                            <div class="text-xl font-bold text-slate-600" x-text="bonsCommande.filter(b=>b.statut==='brouillon').length"></div>
                            <div class="text-xs text-slate-400">Brouillons</div>
                        </div>
                        <div class="border border-slate-100 rounded-xl px-5 py-3 bg-blue-50/20 min-w-[120px]">
                            <div class="text-xl font-bold text-blue-600" x-text="bonsCommande.filter(b=>b.statut==='valide').length"></div>
                            <div class="text-xs text-blue-400">Validées</div>
                        </div>
                        <div class="border border-slate-100 rounded-xl px-5 py-3 bg-amber-50/20 min-w-[120px]">
                            <div class="text-xl font-bold text-amber-600" x-text="bonsCommande.filter(b=>b.statut==='envoye').length"></div>
                            <div class="text-xs text-amber-400">Envoyées</div>
                        </div>
                        <div class="border border-slate-100 rounded-xl px-5 py-3 bg-emerald-50/20 min-w-[120px]">
                            <div class="text-xl font-bold text-emerald-600" x-text="bonsCommande.filter(b=>b.statut==='recu').length"></div>
                            <div class="text-xs text-emerald-400">Reçues</div>
                        </div>
                        <div class="border border-slate-100 rounded-xl px-5 py-3 bg-rose-50/20 min-w-[120px]">
                            <div class="text-xl font-bold text-rose-600" x-text="bonsCommande.filter(b=>b.statut==='annule').length"></div>
                            <div class="text-xs text-rose-400">Annulées</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3">Factures fournisseurs</h3>
                    <div class="flex flex-wrap gap-3">
                        <div class="border border-slate-100 rounded-xl px-5 py-3 bg-indigo-50/10 min-w-[120px]">
                            <div class="text-xl font-bold text-indigo-700" x-text="factures.length"></div>
                            <div class="text-xs text-indigo-500">Total factures</div>
                        </div>
                        <div class="border border-slate-100 rounded-xl px-5 py-3 bg-emerald-50/10 min-w-[120px]">
                            <div class="text-xl font-bold text-emerald-700" x-text="format(factures.filter(f=>f.statut==='payee').reduce((acc,f)=>acc+f.montant, 0))"></div>
                            <div class="text-xs text-emerald-500">Montant payé</div>
                        </div>
                        <div class="border border-slate-100 rounded-xl px-5 py-3 bg-amber-50/10 min-w-[120px]">
                            <div class="text-xl font-bold text-amber-700" x-text="format(factures.filter(f=>f.statut==='en_attente').reduce((acc,f)=>acc+f.montant, 0))"></div>
                            <div class="text-xs text-amber-500">Montant en attente</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── TAB 4: FOURNISSEURS ── -->
            <div x-show="activeTab === 4" class="space-y-4" style="display:none;">
                <div class="flex items-center gap-3">
                    <input type="text" x-model="searchFournisseur" placeholder="Rechercher par nom, téléphone..." class="f-input max-w-xs">
                    <button class="ml-auto bg-indigo-700 hover:bg-indigo-800 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-1.5 text-xs" @click="openFournisseurModal()">
                        <i class="ri-add-line"></i> Nouveau Fournisseur
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="p-3">NOM</th>
                                <th class="p-3">N° CONTRIBUABLE</th>
                                <th class="p-3">TÉLÉPHONE</th>
                                <th class="p-3">EMAIL</th>
                                <th class="p-3">COMPTE CHARGE</th>
                                <th class="p-3">STATUT</th>
                                <th class="p-3 text-right">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <template x-for="f in filteredFournisseurs()" :key="f.id">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-3 font-bold text-slate-800" x-text="f.nom"></td>
                                    <td class="p-3 text-slate-500 text-xs" x-text="f.numero_contribuable || '—'"></td>
                                    <td class="p-3 text-slate-500 text-xs" x-text="f.telephone || '—'"></td>
                                    <td class="p-3 text-slate-500 text-xs" x-text="f.email || '—'"></td>
                                    <td class="p-3 text-slate-400 text-xs" x-text="f.compte_nom"></td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="f.actif ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'" x-text="f.actif ? 'Actif' : 'Inactif'"></span>
                                    </td>
                                    <td class="p-3 text-right space-x-1">
                                        <button class="table-action-btn text-blue-600" title="Modifier" @click="openFournisseurModal(f)">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button class="table-action-btn text-red-600" title="Supprimer" @click="deleteFournisseur(f.id)">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── TAB 5: SIGNATURES ── -->
            <div x-show="activeTab === 5" class="space-y-4" style="display:none;">
                <div class="flex items-center gap-3">
                    <p class="text-xs text-slate-500">Signatures des responsables pouvant apparaître sur les documents officiels.</p>
                    <button class="ml-auto bg-indigo-700 hover:bg-indigo-800 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-1.5 text-xs" @click="openSignatureModal()">
                        <i class="ri-add-line"></i> Nouvelle Signature
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="p-3">APERÇU</th>
                                <th class="p-3">NOM</th>
                                <th class="p-3">FONCTION</th>
                                <th class="p-3">STATUT</th>
                                <th class="p-3 text-right">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <template x-for="s in signatures" :key="s.id">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-3">
                                        <template x-if="s.url_signature">
                                            <img :src="s.url_signature" class="h-10 w-24 object-contain border border-slate-100 rounded bg-white">
                                        </template>
                                        <template x-if="!s.url_signature">
                                            <div class="h-10 w-24 bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-300 text-xs">Aperçu</div>
                                        </template>
                                    </td>
                                    <td class="p-3 font-bold text-slate-800" x-text="s.nom"></td>
                                    <td class="p-3 text-slate-500 text-xs" x-text="s.fonction"></td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="s.actif ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'" x-text="s.actif ? 'Active' : 'Inactive'"></span>
                                    </td>
                                    <td class="p-3 text-right space-x-1">
                                        <button class="table-action-btn text-blue-600" title="Modifier" @click="openSignatureModal(s)">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button class="table-action-btn text-red-600" title="Supprimer" @click="deleteSignature(s.id)">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ── MODAL: FOURNISSEUR ── -->
        <div class="modal-overlay" x-show="showFournisseurModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="font-bold text-slate-800" x-text="isEditFournisseur ? 'Modifier Fournisseur' : 'Nouveau Fournisseur'"></h3>
                    <button @click="showFournisseurModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-lg"></i></button>
                </div>
                <form @submit.prevent="saveFournisseur()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nom / Raison sociale *</label>
                        <input type="text" x-model="fournisseurForm.nom" required class="f-input">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">N° Contribuable</label>
                            <input type="text" x-model="fournisseurForm.numero_contribuable" class="f-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Téléphone</label>
                            <input type="text" x-model="fournisseurForm.telephone" class="f-input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Email</label>
                        <input type="email" x-model="fournisseurForm.email" class="f-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Adresse</label>
                        <textarea x-model="fournisseurForm.adresse" rows="2" class="f-input"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Compte de charge (Classe 6)</label>
                        <select x-model="fournisseurForm.id_compte_charge" class="f-input">
                            <option value="">Sélectionner un compte...</option>
                            <template x-for="c in planComptable" :key="c.id">
                                <option :value="c.id" x-text="c.numero_compte + ' — ' + c.libelle"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="fournisseurForm.actif" id="f_actif">
                        <label for="f_actif" class="text-xs font-bold text-slate-500 uppercase cursor-pointer">Fournisseur actif</label>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-3 mt-4">
                        <button type="button" @click="showFournisseurModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white rounded-lg text-xs font-semibold">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── MODAL: BON DE COMMANDE ── -->
        <div class="modal-overlay" x-show="showCommandeModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="font-bold text-slate-800" x-text="isEditCommande ? 'Modifier Bon de Commande' : 'Nouveau Bon de Commande'"></h3>
                    <button @click="showCommandeModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-lg"></i></button>
                </div>
                <form @submit.prevent="saveCommande()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Fournisseur *</label>
                        <select x-model="commandeForm.id_fournisseur" required class="f-input">
                            <option value="">Sélectionner un fournisseur...</option>
                            <template x-for="f in fournisseurs" :key="f.id">
                                <option :value="f.id" x-text="f.nom"></option>
                            </template>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Date commande *</label>
                            <input type="date" x-model="commandeForm.date_commande" required class="f-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Statut *</label>
                            <select x-model="commandeForm.statut" required class="f-input">
                                <option value="brouillon">Brouillon</option>
                                <option value="valide">Validé</option>
                                <option value="envoye">Envoyé</option>
                                <option value="recu">Reçu</option>
                                <option value="annule">Annulé</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Notes</label>
                        <textarea x-model="commandeForm.notes" rows="2" class="f-input"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-3 mt-4">
                        <button type="button" @click="showCommandeModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white rounded-lg text-xs font-semibold">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── MODAL: LIGNES DE COMMANDE ── -->
        <div class="modal-overlay" x-show="showLignesModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop style="max-width: 750px;">
                <div class="flex items-center justify-between bg-indigo-700 text-white p-4 -m-6 mb-4 rounded-t-xl">
                    <h3 class="font-bold flex items-center gap-2">
                        <i class="ri-list-check"></i> Lignes — <span x-text="selectedBon ? selectedBon.numero : ''"></span>
                    </h3>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-indigo-100">Total : <strong class="text-white" x-text="format(selectedBon ? selectedBon.total : 0)"></strong></span>
                        <button @click="showLignesModal = false" class="text-indigo-200 hover:text-white"><i class="ri-close-line text-xl"></i></button>
                    </div>
                </div>
                <div class="pt-6 space-y-4">
                    <!-- Saisie nouvelle ligne -->
                    <form @submit.prevent="addLigne()" class="flex flex-wrap md:flex-nowrap gap-2 bg-slate-50 p-3 rounded-lg">
                        <div class="flex-1">
                            <input type="text" x-model="ligneForm.designation" required placeholder="Désignation" class="f-input">
                        </div>
                        <div class="w-20">
                            <input type="number" x-model="ligneForm.quantite" required placeholder="Qté" class="f-input">
                        </div>
                        <div class="w-28">
                            <input type="number" x-model="ligneForm.prix_unitaire" required placeholder="P.U. (FCFA)" class="f-input">
                        </div>
                        <button type="submit" class="bg-indigo-700 text-white font-bold px-4 rounded-lg text-xs hover:bg-indigo-800 whitespace-nowrap">Ajouter</button>
                    </form>

                    <!-- Liste des lignes -->
                    <div class="max-h-60 overflow-y-auto space-y-2">
                        <template x-for="(l, index) in (selectedBon ? selectedBon.lignes : [])" :key="l.id">
                            <div class="flex items-center justify-between p-3 border border-slate-100 rounded-lg hover:bg-slate-50/30">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full bg-indigo-50 flex items-center justify-center text-xs font-bold text-indigo-700" x-text="index + 1"></div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm" x-text="l.designation"></div>
                                        <div class="text-xs text-slate-400"><span x-text="l.quantite"></span> x <span x-text="format(l.prix_unitaire)"></span></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="font-bold text-slate-800 text-sm" x-text="format(l.montant)"></div>
                                    <button @click="deleteLigne(l.id)" class="text-rose-600 hover:bg-rose-50 w-7 h-7 rounded flex items-center justify-center">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template x-if="selectedBon && selectedBon.lignes.length === 0">
                            <p class="text-slate-400 text-xs italic text-center py-4">Aucune ligne. Ajoutez des articles ci-dessus.</p>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── MODAL: BON DE RÉCEPTION ── -->
        <div class="modal-overlay" x-show="showReceptionModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="font-bold text-slate-800" x-text="isEditReception ? 'Modifier Réception' : 'Nouveau Bon de Réception'"></h3>
                    <button @click="showReceptionModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-lg"></i></button>
                </div>
                <form @submit.prevent="saveReception()" class="space-y-4">
                    <div x-show="!isEditReception">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Bon de Commande *</label>
                        <select x-model="receptionForm.id_bon_commande" :required="!isEditReception" class="f-input">
                            <option value="">Sélectionner un bon...</option>
                            <template x-for="b in bonsCommande.filter(x => x.statut === 'envoye' || x.statut === 'valide')" :key="b.id">
                                <option :value="b.id" x-text="b.numero + ' — ' + b.fournisseur_nom"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Date réception *</label>
                        <input type="date" x-model="receptionForm.date_reception" required class="f-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Notes</label>
                        <textarea x-model="receptionForm.notes" rows="2" class="f-input"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-3 mt-4">
                        <button type="button" @click="showReceptionModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white rounded-lg text-xs font-semibold">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── MODAL: FACTURE FOURNISSEUR ── -->
        <div class="modal-overlay" x-show="showFactureModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="font-bold text-slate-800" x-text="isEditFacture ? 'Modifier Facture' : 'Nouvelle Facture Fournisseur'"></h3>
                    <button @click="showFactureModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-lg"></i></button>
                </div>
                <form @submit.prevent="saveFacture()" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">N° Facture *</label>
                            <input type="text" x-model="factureForm.numero_facture" required class="f-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Fournisseur *</label>
                            <select x-model="factureForm.id_fournisseur" required class="f-input">
                                <option value="">Sélectionner...</option>
                                <template x-for="f in fournisseurs" :key="f.id">
                                    <option :value="f.id" x-text="f.nom"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Bon de Commande (optionnel)</label>
                        <select x-model="factureForm.id_bon_commande" class="f-input">
                            <option value="">Aucun bon</option>
                            <template x-for="b in bonsCommande" :key="b.id">
                                <option :value="b.id" x-text="b.numero + ' — ' + b.fournisseur_nom"></option>
                            </template>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Montant (FCFA) *</label>
                            <input type="number" x-model="factureForm.montant" required class="f-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Statut *</label>
                            <select x-model="factureForm.statut" required class="f-input">
                                <option value="en_attente">En attente</option>
                                <option value="payee">Payée</option>
                                <option value="annulee">Annulée</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Date facture *</label>
                            <input type="date" x-model="factureForm.date_facture" required class="f-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Date échéance</label>
                            <input type="date" x-model="factureForm.date_echeance" class="f-input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Document facture (Lien / URL)</label>
                        <input type="text" x-model="factureForm.url_document" placeholder="Ex: https://..." class="f-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Notes</label>
                        <textarea x-model="factureForm.notes" rows="2" class="f-input"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-3 mt-4">
                        <button type="button" @click="showFactureModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white rounded-lg text-xs font-semibold">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── MODAL: SIGNATURE ELECTRONIQUE ── -->
        <div class="modal-overlay" x-show="showSignatureModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="font-bold text-slate-800" x-text="isEditSignature ? 'Modifier Signature' : 'Nouvelle Signature'"></h3>
                    <button @click="showSignatureModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-lg"></i></button>
                </div>
                <form @submit.prevent="saveSignature()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nom du signataire *</label>
                        <input type="text" x-model="signatureForm.nom" required class="f-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Fonction / Titre *</label>
                        <input type="text" x-model="signatureForm.fonction" required class="f-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Image de la signature (Lien / URL)</label>
                        <input type="text" x-model="signatureForm.url_signature" placeholder="Ex: https://..." class="f-input">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="signatureForm.actif" id="s_actif">
                        <label for="s_actif" class="text-xs font-bold text-slate-500 uppercase cursor-pointer">Signature active</label>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-3 mt-4">
                        <button type="button" @click="showSignatureModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white rounded-lg text-xs font-semibold">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('achatsPage', (fournisseurs, planComptable, bonsCommande, bonsReception, factures, signatures, initialTab) => ({
                fournisseurs, planComptable, bonsCommande, bonsReception, factures, signatures,
                activeTab: initialTab,
                
                searchCommande: '',
                searchFournisseur: '',

                // Modal States
                showFournisseurModal: false,
                isEditFournisseur: false,
                fournisseurForm: { id: null, nom: '', numero_contribuable: '', telephone: '', email: '', adresse: '', id_compte_charge: '', actif: true },

                showCommandeModal: false,
                isEditCommande: false,
                commandeForm: { id: null, id_fournisseur: '', date_commande: '', statut: 'brouillon', notes: '' },

                showLignesModal: false,
                selectedBon: null,
                ligneForm: { designation: '', quantite: 1, prix_unitaire: 0 },

                showReceptionModal: false,
                isEditReception: false,
                receptionForm: { id: null, id_bon_commande: '', date_reception: '', notes: '' },

                showFactureModal: false,
                isEditFacture: false,
                factureForm: { id: null, numero_facture: '', id_fournisseur: '', id_bon_commande: '', montant: 0, date_facture: '', date_echeance: '', statut: 'en_attente', notes: '', url_document: '' },

                showSignatureModal: false,
                isEditSignature: false,
                signatureForm: { id: null, nom: '', fonction: '', url_signature: '', actif: true },

                init() {
                    this.resetFournisseurForm();
                    this.resetCommandeForm();
                    this.resetReceptionForm();
                    this.resetFactureForm();
                    this.resetSignatureForm();
                },

                format(v) {
                    return Number(v).toLocaleString('fr-FR') + ' F';
                },

                getStatutLabel(statut) {
                    const labels = {
                        brouillon: 'Brouillon',
                        valide: 'Validé',
                        envoye: 'Envoyé',
                        recu: 'Reçu',
                        annule: 'Annulé',
                        en_attente: 'En attente',
                        payee: 'Payée'
                    };
                    return labels[statut] || statut;
                },

                // ── FILTERS ──
                filteredCommandes() {
                    const q = this.searchCommande.toLowerCase();
                    return this.bonsCommande.filter(b => 
                        b.numero.toLowerCase().includes(q) || 
                        b.fournisseur_nom.toLowerCase().includes(q)
                    );
                },

                filteredFournisseurs() {
                    const q = this.searchFournisseur.toLowerCase();
                    return this.fournisseurs.filter(f => 
                        f.nom.toLowerCase().includes(q) || 
                        (f.telephone && f.telephone.includes(q))
                    );
                },

                // ── CRUD FOURNISSEURS ──
                resetFournisseurForm() {
                    this.fournisseurForm = { id: null, nom: '', numero_contribuable: '', telephone: '', email: '', adresse: '', id_compte_charge: '', actif: true };
                },
                openFournisseurModal(row = null) {
                    this.isEditFournisseur = !!row;
                    if(row) {
                        this.fournisseurForm = { ...row };
                    } else {
                        this.resetFournisseurForm();
                    }
                    this.showFournisseurModal = true;
                },
                async saveFournisseur() {
                    const url = this.isEditFournisseur ? `/achats/fournisseurs/${this.fournisseurForm.id}` : '/achats/fournisseurs';
                    const method = this.isEditFournisseur ? 'PUT' : 'POST';
                    try {
                        const r = await fetch(url, {
                            method,
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.fournisseurForm)
                        });
                        const res = await r.json();
                        if(res.success) {
                            alert(res.message);
                            window.location.reload();
                        } else {
                            alert(res.message || 'Erreur');
                        }
                    } catch(e) {
                        console.error(e);
                        alert('Erreur de connexion');
                    }
                },
                async deleteFournisseur(id) {
                    if(!confirm('Voulez-vous supprimer ce fournisseur ?')) return;
                    try {
                        const r = await fetch(`/achats/fournisseurs/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        const res = await r.json();
                        if(res.success) {
                            alert(res.message);
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },

                // ── CRUD COMMANDES ──
                resetCommandeForm() {
                    const today = new Date().toISOString().substring(0,10);
                    this.commandeForm = { id: null, id_fournisseur: '', date_commande: today, statut: 'brouillon', notes: '' };
                },
                openCommandeModal(row = null) {
                    this.isEditCommande = !!row;
                    if(row) {
                        this.commandeForm = { ...row };
                    } else {
                        this.resetCommandeForm();
                    }
                    this.showCommandeModal = true;
                },
                async saveCommande() {
                    const url = this.isEditCommande ? `/achats/commandes/${this.commandeForm.id}` : '/achats/commandes';
                    const method = this.isEditCommande ? 'PUT' : 'POST';
                    try {
                        const r = await fetch(url, {
                            method,
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.commandeForm)
                        });
                        const res = await r.json();
                        if(res.success) {
                            alert(res.message);
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },
                async deleteCommande(id) {
                    if(!confirm('Supprimer ce bon de commande ?')) return;
                    try {
                        const r = await fetch(`/achats/commandes/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        const res = await r.json();
                        if(res.success) {
                            alert(res.message);
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },

                // ── LIGNES DE COMMANDE ──
                openLignesModal(bon) {
                    this.selectedBon = bon;
                    this.ligneForm = { designation: '', quantite: 1, prix_unitaire: 0 };
                    this.showLignesModal = true;
                },
                async addLigne() {
                    if(!this.selectedBon) return;
                    try {
                        const r = await fetch(`/achats/commandes/${this.selectedBon.id}/lignes`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.ligneForm)
                        });
                        const res = await r.json();
                        if(res.success) {
                            this.selectedBon.lignes.push(res.ligne);
                            this.selectedBon.total = res.total;
                            this.ligneForm = { designation: '', quantite: 1, prix_unitaire: 0 };
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },
                async deleteLigne(idLigne) {
                    if(!this.selectedBon) return;
                    try {
                        const r = await fetch(`/achats/commandes/${this.selectedBon.id}/lignes/${idLigne}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        const res = await r.json();
                        if(res.success) {
                            this.selectedBon.lignes = this.selectedBon.lignes.filter(x => x.id !== idLigne);
                            this.selectedBon.total = res.total;
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },
                printCommande(bon) {
                    alert('Génération de PDF Bon de commande en cours de développement (HTML d\'impression local)...');
                },

                // ── CRUD RÉCEPTIONS ──
                resetReceptionForm() {
                    const today = new Date().toISOString().substring(0,10);
                    this.receptionForm = { id: null, id_bon_commande: '', date_reception: today, notes: '' };
                },
                openReceptionModal(row = null) {
                    this.isEditReception = !!row;
                    if(row) {
                        this.receptionForm = { ...row };
                    } else {
                        this.resetReceptionForm();
                    }
                    this.showReceptionModal = true;
                },
                async saveReception() {
                    const url = this.isEditReception ? `/achats/receptions/${this.receptionForm.id}` : '/achats/receptions';
                    const method = this.isEditReception ? 'PUT' : 'POST';
                    try {
                        const r = await fetch(url, {
                            method,
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.receptionForm)
                        });
                        const res = await r.json();
                        if(res.success) {
                            alert(res.message);
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },
                async deleteReception(id) {
                    if(!confirm('Supprimer cette réception ?')) return;
                    try {
                        const r = await fetch(`/achats/receptions/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        const res = await r.json();
                        if(res.success) {
                            alert(res.message);
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },

                // ── CRUD FACTURES ──
                resetFactureForm() {
                    const today = new Date().toISOString().substring(0,10);
                    this.factureForm = { id: null, numero_facture: '', id_fournisseur: '', id_bon_commande: '', montant: 0, date_facture: today, date_echeance: '', statut: 'en_attente', notes: '', url_document: '' };
                },
                openFactureModal(row = null) {
                    this.isEditFacture = !!row;
                    if(row) {
                        this.factureForm = { ...row };
                    } else {
                        this.resetFactureForm();
                    }
                    this.showFactureModal = true;
                },
                async saveFacture() {
                    const url = this.isEditFacture ? `/achats/factures/${this.factureForm.id}` : '/achats/factures';
                    const method = this.isEditFacture ? 'PUT' : 'POST';
                    try {
                        const r = await fetch(url, {
                            method,
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.factureForm)
                        });
                        const res = await r.json();
                        if(res.success) {
                            alert(res.message);
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },
                async deleteFacture(id) {
                    if(!confirm('Supprimer cette facture ?')) return;
                    try {
                        const r = await fetch(`/achats/factures/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        const res = await r.json();
                        if(res.success) {
                            alert(res.message);
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },

                // ── CRUD SIGNATURES ──
                resetSignatureForm() {
                    this.signatureForm = { id: null, nom: '', fonction: '', url_signature: '', actif: true };
                },
                openSignatureModal(row = null) {
                    this.isEditSignature = !!row;
                    if(row) {
                        this.signatureForm = { ...row };
                    } else {
                        this.resetSignatureForm();
                    }
                    this.showSignatureModal = true;
                },
                async saveSignature() {
                    const url = this.isEditSignature ? `/achats/signatures/${this.signatureForm.id}` : '/achats/signatures';
                    const method = this.isEditSignature ? 'PUT' : 'POST';
                    try {
                        const r = await fetch(url, {
                            method,
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.signatureForm)
                        });
                        const res = await r.json();
                        if(res.success) {
                            alert(res.message);
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },
                async deleteSignature(id) {
                    if(!confirm('Supprimer cette signature ?')) return;
                    try {
                        const r = await fetch(`/achats/signatures/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        const res = await r.json();
                        if(res.success) {
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
