<x-app-layout>
    <x-slot name="title">Paramètres du Système</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Paramètres du Système</h2>
    </x-slot>

    @push('styles')
    <style>
        .setting-card {
            background: #fff; border-radius: 16px; padding: 20px;
            border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 16px;
            cursor: pointer; transition: all 0.2s;
        }
        .setting-card:hover {
            transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05);
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
        $statutsJson = $statuts->map(fn($x) => ['id' => $x->id, 'libelle' => $x->libelle])->toJson();
        $typesDocJson = $typesDocument->map(fn($x) => ['id' => $x->id, 'libelle' => $x->libelle, 'obligatoire' => (bool)$x->obligatoire])->toJson();
        $typesNoteJson = $typesNote->map(fn($x) => ['id' => $x->id, 'libelle' => $x->libelle, 'pourcentage' => (float)$x->pourcentage])->toJson();
        $typesFraisJson = $typesFrais->map(fn($x) => ['id' => $x->id, 'libelle' => $x->libelle, 'obligatoire' => (bool)$x->obligatoire])->toJson();
        $modesPaiementJson = $modesPaiement->map(fn($x) => ['id' => $x->id, 'libelle' => $x->libelle, 'sigle' => $x->sigle])->toJson();
        $typesAbonnementJson = $typesAbonnement->map(fn($x) => [
            'id' => $x->id, 'libelle' => $x->libelle, 
            'nb_utilisateurs_max' => (int)$x->nb_utilisateurs_max, 
            'nb_etudiants_max' => (int)$x->nb_etudiants_max, 
            'prix_mensuel' => (float)$x->prix_mensuel
        ])->toJson();
        $articleTypesJson = $articleTypes->map(fn($x) => ['id' => $x->id, 'libelle' => $x->libelle_article_types])->toJson();
        $accountJson = $account ? json_encode([
            'nom' => $account->nom,
            'prenom' => $account->prenom,
            'mail' => $user->mail
        ]) : 'null';
    @endphp

    <div x-data="settingsPage({{ $statutsJson }}, {{ $typesDocJson }}, {{ $typesNoteJson }}, {{ $typesFraisJson }}, {{ $modesPaiementJson }}, {{ $typesAbonnementJson }}, {{ $articleTypesJson }}, {{ $accountJson }})" class="space-y-6">

        <!-- Back Header (Visible only when in a sub-view) -->
        <div x-show="activeView !== 'grid'" class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl p-3" style="display:none;">
            <button @click="activeView = 'grid'" class="text-slate-600 hover:text-slate-900 font-semibold text-sm flex items-center gap-1.5">
                <i class="ri-arrow-left-line"></i> Retour aux Paramètres
            </button>
        </div>

        <!-- MAIN GRID VIEW -->
        <div x-show="activeView === 'grid'" class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Paramètres</h1>
                <p class="text-sm text-slate-500 mt-1">Configuration du système et référentiels globaux</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Roles Card -->
                <a href="{{ route('roles.index') }}" class="setting-card">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl"><i class="ri-shield-user-line text-2xl"></i></div>
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Rôles & Permissions</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Configuration des privilèges d'accès</div>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-[10px] font-bold">Sécurité</span>
                    </div>
                </a>

                <!-- Statuts Card -->
                <div class="setting-card" @click="activeView = 'statuts'">
                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl"><i class="ri-checkbox-circle-line text-2xl"></i></div>
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Statuts (Global)</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Gestion des statuts système</div>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-bold" x-text="statuts.length + ' statut(s)'"></span>
                    </div>
                </div>

                <!-- Types Doc Card -->
                <div class="setting-card" @click="activeView = 'types_doc'">
                    <div class="p-3 bg-purple-50 text-purple-600 rounded-xl"><i class="ri-file-text-line text-2xl"></i></div>
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Types de Document</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Catégories de documents exigés</div>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded bg-purple-50 text-purple-700 text-[10px] font-bold" x-text="typesDoc.length + ' type(s)'"></span>
                    </div>
                </div>

                <!-- Types Note Card -->
                <div class="setting-card" @click="activeView = 'types_note'">
                    <div class="p-3 bg-orange-50 text-orange-600 rounded-xl"><i class="ri-edit-box-line text-2xl"></i></div>
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Types de Note</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">CC, Devoirs, Examens...</div>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded bg-orange-50 text-orange-700 text-[10px] font-bold" x-text="typesNote.length + ' type(s)'"></span>
                    </div>
                </div>

                <!-- Types Frais Card -->
                <div class="setting-card" @click="activeView = 'types_frais'">
                    <div class="p-3 bg-teal-50 text-teal-600 rounded-xl"><i class="ri-money-dollar-box-line text-2xl"></i></div>
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Types de Frais</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Mensualités, Inscriptions...</div>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded bg-teal-50 text-teal-700 text-[10px] font-bold" x-text="typesFrais.length + ' type(s)'"></span>
                    </div>
                </div>

                <!-- Modes Paiement Card -->
                <div class="setting-card" @click="activeView = 'modes_paiement'">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl"><i class="ri-bank-card-line text-2xl"></i></div>
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Modes de Paiement</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Espèces, Chèques, Wave...</div>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold" x-text="modesPaiement.length + ' mode(s)'"></span>
                    </div>
                </div>

                <!-- Abonnement Card -->
                <div class="setting-card" @click="activeView = 'types_abonnement'">
                    <div class="p-3 bg-rose-50 text-rose-600 rounded-xl"><i class="ri-vip-diamond-line text-2xl"></i></div>
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Types d'Abonnement</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Configuration des forfaits</div>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded bg-rose-50 text-rose-700 text-[10px] font-bold" x-text="typesAbonnement.length + ' type(s)'"></span>
                    </div>
                </div>

                <!-- Couleurs Card -->
                <a href="{{ route('etablissement.couleurs') }}" class="setting-card">
                    <div class="p-3 bg-pink-50 text-pink-600 rounded-xl"><i class="ri-palette-line text-2xl"></i></div>
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Couleurs (Thème)</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Logo, thème et couleurs chartes</div>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded bg-pink-50 text-pink-700 text-[10px] font-bold">Thème</span>
                    </div>
                </a>

                <!-- Articles Card -->
                <div class="setting-card" @click="activeView = 'types_article'">
                    <div class="p-3 bg-teal-50 text-teal-600 rounded-xl"><i class="ri-list-check-3 text-2xl"></i></div>
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Types d'Articles</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Catégories d'articles de stock</div>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded bg-teal-50 text-teal-700 text-[10px] font-bold" x-text="articleTypes.length + ' type(s)'"></span>
                    </div>
                </div>

                <!-- Compte Card -->
                <div class="setting-card" @click="activeView = 'mon_compte'">
                    <div class="p-3 bg-cyan-50 text-cyan-600 rounded-xl"><i class="ri-user-line text-2xl"></i></div>
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Mon Compte</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Informations du compte</div>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded bg-cyan-50 text-cyan-700 text-[10px] font-bold">Profil</span>
                    </div>
                </div>

                <!-- Password Card -->
                <div class="setting-card" @click="activeView = 'password'">
                    <div class="p-3 bg-orange-50 text-orange-600 rounded-xl"><i class="ri-lock-password-line text-2xl"></i></div>
                    <div>
                        <div class="font-bold text-slate-800 text-sm">Mot de passe</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Modifier le mot de passe</div>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded bg-orange-50 text-orange-700 text-[10px] font-bold">Sécurité</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── SUB-VIEW: STATUTS ── -->
        <div x-show="activeView === 'statuts'" class="bg-white border border-slate-200 rounded-xl p-6 space-y-4" style="display:none;">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Statuts (Global)</h3>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs" @click="openModal('statut')">
                    Nouveau Statut
                </button>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
                        <th class="p-3">LIBELLÉ</th>
                        <th class="p-3 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    <template x-for="row in statuts" :key="row.id">
                        <tr>
                            <td class="p-3 font-semibold text-slate-800" x-text="row.libelle"></td>
                            <td class="p-3 text-right space-x-1">
                                <button class="text-blue-600 hover:bg-slate-50 px-2 py-1 rounded" @click="openModal('statut', row)"><i class="ri-edit-line"></i></button>
                                <button class="text-red-600 hover:bg-slate-50 px-2 py-1 rounded" @click="deleteItem('statuts', row.id)"><i class="ri-delete-bin-line"></i></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- ── SUB-VIEW: TYPES DOC ── -->
        <div x-show="activeView === 'types_doc'" class="bg-white border border-slate-200 rounded-xl p-6 space-y-4" style="display:none;">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Types de Document</h3>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs" @click="openModal('type_doc')">
                    Nouveau Type
                </button>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
                        <th class="p-3">LIBELLÉ</th>
                        <th class="p-3">OBLIGATOIRE</th>
                        <th class="p-3 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    <template x-for="row in typesDoc" :key="row.id">
                        <tr>
                            <td class="p-3 font-semibold text-slate-800" x-text="row.libelle"></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="row.obligatoire ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600'" x-text="row.obligatoire ? 'Obligatoire' : 'Facultatif'"></span>
                            </td>
                            <td class="p-3 text-right space-x-1">
                                <button class="text-blue-600 hover:bg-slate-50 px-2 py-1 rounded" @click="openModal('type_doc', row)"><i class="ri-edit-line"></i></button>
                                <button class="text-red-600 hover:bg-slate-50 px-2 py-1 rounded" @click="deleteItem('types-document', row.id)"><i class="ri-delete-bin-line"></i></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- ── SUB-VIEW: TYPES NOTE ── -->
        <div x-show="activeView === 'types_note'" class="bg-white border border-slate-200 rounded-xl p-6 space-y-4" style="display:none;">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Types de Note</h3>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs" @click="openModal('type_note')">
                    Nouveau Type
                </button>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
                        <th class="p-3">LIBELLÉ</th>
                        <th class="p-3">POURCENTAGE (COEF)</th>
                        <th class="p-3 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    <template x-for="row in typesNote" :key="row.id">
                        <tr>
                            <td class="p-3 font-semibold text-slate-800" x-text="row.libelle"></td>
                            <td class="p-3 font-semibold text-slate-600" x-text="row.pourcentage + ' %'"></td>
                            <td class="p-3 text-right space-x-1">
                                <button class="text-blue-600 hover:bg-slate-50 px-2 py-1 rounded" @click="openModal('type_note', row)"><i class="ri-edit-line"></i></button>
                                <button class="text-red-600 hover:bg-slate-50 px-2 py-1 rounded" @click="deleteItem('types-note', row.id)"><i class="ri-delete-bin-line"></i></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- ── SUB-VIEW: TYPES FRAIS ── -->
        <div x-show="activeView === 'types_frais'" class="bg-white border border-slate-200 rounded-xl p-6 space-y-4" style="display:none;">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Types de Frais</h3>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs" @click="openModal('type_frais')">
                    Nouveau Type
                </button>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
                        <th class="p-3">LIBELLÉ</th>
                        <th class="p-3">OBLIGATOIRE</th>
                        <th class="p-3 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    <template x-for="row in typesFrais" :key="row.id">
                        <tr>
                            <td class="p-3 font-semibold text-slate-800" x-text="row.libelle"></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="row.obligatoire ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600'" x-text="row.obligatoire ? 'Obligatoire' : 'Facultatif'"></span>
                            </td>
                            <td class="p-3 text-right space-x-1">
                                <button class="text-blue-600 hover:bg-slate-50 px-2 py-1 rounded" @click="openModal('type_frais', row)"><i class="ri-edit-line"></i></button>
                                <button class="text-red-600 hover:bg-slate-50 px-2 py-1 rounded" @click="deleteItem('types-frais', row.id)"><i class="ri-delete-bin-line"></i></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- ── SUB-VIEW: MODES PAIEMENT ── -->
        <div x-show="activeView === 'modes_paiement'" class="bg-white border border-slate-200 rounded-xl p-6 space-y-4" style="display:none;">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Modes de Paiement</h3>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs" @click="openModal('mode_paiement')">
                    Nouveau Mode
                </button>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
                        <th class="p-3">LIBELLÉ</th>
                        <th class="p-3">SIGLE</th>
                        <th class="p-3 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    <template x-for="row in modesPaiement" :key="row.id">
                        <tr>
                            <td class="p-3 font-semibold text-slate-800" x-text="row.libelle"></td>
                            <td class="p-3 font-bold text-slate-500" x-text="row.sigle"></td>
                            <td class="p-3 text-right space-x-1">
                                <button class="text-blue-600 hover:bg-slate-50 px-2 py-1 rounded" @click="openModal('mode_paiement', row)"><i class="ri-edit-line"></i></button>
                                <button class="text-red-600 hover:bg-slate-50 px-2 py-1 rounded" @click="deleteItem('modes-paiement', row.id)"><i class="ri-delete-bin-line"></i></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- ── SUB-VIEW: TYPES ABONNEMENT ── -->
        <div x-show="activeView === 'types_abonnement'" class="bg-white border border-slate-200 rounded-xl p-6 space-y-4" style="display:none;">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Types d'Abonnement</h3>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs" @click="openModal('abonnement')">
                    Nouveau Forfait
                </button>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
                        <th class="p-3">LIBELLÉ</th>
                        <th class="p-3">UTILISATEURS MAX</th>
                        <th class="p-3">ÉTUDIANTS MAX</th>
                        <th class="p-3">PRIX MENSUEL</th>
                        <th class="p-3 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    <template x-for="row in typesAbonnement" :key="row.id">
                        <tr>
                            <td class="p-3 font-semibold text-slate-800" x-text="row.libelle"></td>
                            <td class="p-3 font-semibold text-slate-600" x-text="row.nb_utilisateurs_max"></td>
                            <td class="p-3 font-semibold text-slate-600" x-text="row.nb_etudiants_max"></td>
                            <td class="p-3 font-bold text-emerald-600" x-text="row.prix_mensuel + ' F'"></td>
                            <td class="p-3 text-right space-x-1">
                                <button class="text-blue-600 hover:bg-slate-50 px-2 py-1 rounded" @click="openModal('abonnement', row)"><i class="ri-edit-line"></i></button>
                                <button class="text-red-600 hover:bg-slate-50 px-2 py-1 rounded" @click="deleteItem('types-abonnement', row.id)"><i class="ri-delete-bin-line"></i></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- ── SUB-VIEW: TYPES ARTICLE ── -->
        <div x-show="activeView === 'types_article'" class="bg-white border border-slate-200 rounded-xl p-6 space-y-4" style="display:none;">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Types d'Articles</h3>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs" @click="openModal('article_type')">
                    Nouveau Type
                </button>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
                        <th class="p-3">LIBELLÉ</th>
                        <th class="p-3 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    <template x-for="row in articleTypes" :key="row.id">
                        <tr>
                            <td class="p-3 font-semibold text-slate-800" x-text="row.libelle"></td>
                            <td class="p-3 text-right space-x-1">
                                <button class="text-blue-600 hover:bg-slate-50 px-2 py-1 rounded" @click="openModal('article_type', row)"><i class="ri-edit-line"></i></button>
                                <button class="text-red-600 hover:bg-slate-50 px-2 py-1 rounded" @click="deleteItem('types-article', row.id)"><i class="ri-delete-bin-line"></i></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- ── SUB-VIEW: MON COMPTE ── -->
        <div x-show="activeView === 'mon_compte'" class="bg-white border border-slate-200 rounded-xl p-6 max-w-lg mx-auto space-y-4" style="display:none;">
            <h3 class="font-bold text-slate-800">Mon Compte</h3>
            <p class="text-xs text-slate-400">Modifier vos informations personnelles</p>
            
            <form @submit.prevent="saveProfil()" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Prénom</label>
                        <input type="text" x-model="compteForm.prenom" required class="f-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nom</label>
                        <input type="text" x-model="compteForm.nom" required class="f-input">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Adresse E-mail</label>
                    <input type="email" x-model="compteForm.mail" required class="f-input">
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-lg text-xs">
                    Enregistrer les modifications
                </button>
            </form>
        </div>

        <!-- ── SUB-VIEW: MOT DE PASSE ── -->
        <div x-show="activeView === 'password'" class="bg-white border border-slate-200 rounded-xl p-6 max-w-lg mx-auto space-y-4" style="display:none;">
            <h3 class="font-bold text-slate-800">Modifier le mot de passe</h3>
            <p class="text-xs text-slate-400">Saisissez votre mot de passe actuel puis définissez le nouveau</p>
            
            <form @submit.prevent="savePassword()" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Mot de passe actuel</label>
                    <input type="password" x-model="passwordForm.ancien" required class="f-input">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nouveau mot de passe</label>
                    <input type="password" x-model="passwordForm.nouveau" required class="f-input">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Confirmer le mot de passe</label>
                    <input type="password" x-model="passwordForm.confirmer" required class="f-input">
                </div>
                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2.5 rounded-lg text-xs">
                    Modifier le mot de passe
                </button>
            </form>
        </div>

        <!-- ── REFERENTIAL EDIT MODAL ── -->
        <div class="modal-overlay" x-show="showModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="font-bold text-slate-800" x-text="editId ? 'Modifier la ligne' : 'Nouvel Enregistrement'"></h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-lg"></i></button>
                </div>
                <form @submit.prevent="submitForm()" class="space-y-4">
                    <!-- Standard Libellé field -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Libellé *</label>
                        <input type="text" x-model="form.libelle" required class="f-input">
                    </div>

                    <!-- Boolean Obligatoire for Types doc/frais -->
                    <template x-if="modalType === 'type_doc' || modalType === 'type_frais'">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" x-model="form.obligatoire" id="m_oblig">
                            <label for="m_oblig" class="text-xs font-bold text-slate-500 uppercase cursor-pointer">Obligatoire</label>
                        </div>
                    </template>

                    <!-- Pourcentage for Types Note -->
                    <template x-if="modalType === 'type_note'">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Pourcentage (Coef) *</label>
                            <input type="number" x-model="form.pourcentage" required class="f-input">
                        </div>
                    </template>

                    <!-- Sigle for Modes Paiement -->
                    <template x-if="modalType === 'mode_paiement'">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Sigle *</label>
                            <input type="text" x-model="form.sigle" required class="f-input">
                        </div>
                    </template>

                    <!-- Abonnement Limits & Price -->
                    <template x-if="modalType === 'abonnement'">
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Utilisateurs max</label>
                                    <input type="number" x-model="form.nb_utilisateurs_max" required class="f-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Étudiants max</label>
                                    <input type="number" x-model="form.nb_etudiants_max" required class="f-input">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Prix mensuel (FCFA)</label>
                                <input type="number" x-model="form.prix_mensuel" required class="f-input">
                            </div>
                        </div>
                    </template>

                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-3 mt-4">
                        <button type="button" @click="showModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('settingsPage', (statuts, typesDoc, typesNote, typesFrais, modesPaiement, typesAbonnement, articleTypes, initialAccount) => ({
                statuts, typesDoc, typesNote, typesFrais, modesPaiement, typesAbonnement, articleTypes,
                activeView: 'grid',

                // Account Forms
                compteForm: initialAccount ? { ...initialAccount } : { nom: '', prenom: '', mail: '' },
                passwordForm: { ancien: '', nouveau: '', confirmer: '' },

                // Modal States
                showModal: false,
                modalType: '', // 'statut', 'type_doc', 'type_note', 'type_frais', 'mode_paiement', 'abonnement', 'article_type'
                editId: null,
                form: { libelle: '', obligatoire: false, pourcentage: 0, sigle: '', nb_utilisateurs_max: 10, nb_etudiants_max: 100, prix_mensuel: 0 },

                openModal(type, row = null) {
                    this.modalType = type;
                    this.editId = row ? row.id : null;
                    
                    if (row) {
                        this.form = {
                            libelle: row.libelle || '',
                            obligatoire: row.obligatoire || false,
                            pourcentage: row.pourcentage || 0,
                            sigle: row.sigle || '',
                            nb_utilisateurs_max: row.nb_utilisateurs_max || 10,
                            nb_etudiants_max: row.nb_etudiants_max || 100,
                            prix_mensuel: row.prix_mensuel || 0
                        };
                    } else {
                        this.form = { libelle: '', obligatoire: false, pourcentage: 0, sigle: '', nb_utilisateurs_max: 10, nb_etudiants_max: 100, prix_mensuel: 0 };
                    }
                    this.showModal = true;
                },

                async submitForm() {
                    let endpoint = '/parametres/';
                    switch (this.modalType) {
                        case 'statut': endpoint += 'statuts'; break;
                        case 'type_doc': endpoint += 'types-document'; break;
                        case 'type_note': endpoint += 'types-note'; break;
                        case 'type_frais': endpoint += 'types-frais'; break;
                        case 'mode_paiement': endpoint += 'modes-paiement'; break;
                        case 'abonnement': endpoint += 'types-abonnement'; break;
                        case 'article_type': endpoint += 'types-article'; break;
                    }

                    const url = this.editId ? `${endpoint}/${this.editId}` : endpoint;
                    const method = this.editId ? 'PUT' : 'POST';

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

                async deleteItem(endpoint, id) {
                    if (!confirm('Voulez-vous supprimer cet enregistrement ?')) return;
                    try {
                        const r = await fetch(`/parametres/${endpoint}/${id}`, {
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

                async saveProfil() {
                    try {
                        const r = await fetch('/parametres/profil', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.compteForm)
                        });
                        const res = await r.json();
                        alert(res.message);
                    } catch(e) {
                        console.error(e);
                    }
                },

                async savePassword() {
                    try {
                        const r = await fetch('/parametres/motdepasse', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.passwordForm)
                        });
                        const res = await r.json();
                        alert(res.message);
                        if (res.success) {
                            this.passwordForm = { ancien: '', nouveau: '', confirmer: '' };
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
