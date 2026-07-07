<x-app-layout>
    <x-slot name="title">Inscriptions & Scolarité</x-slot>
    
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px);
            z-index: 50; display: flex; align-items: center; justify-content: center;
        }
        .modal-content {
            background: #F8FAFC; border-radius: 24px; width: 100%; max-width: 620px;
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
        .action-btn {
            width: 32px; height: 32px; border-radius: 8px; display: inline-flex;
            align-items: center; justify-content: center; transition: all 0.2s;
        }
        .action-btn:hover { background: #E2E8F0; }

        /* Switches styled like Flutter */
        .switch-toggle {
            position: relative; display: inline-block; width: 44px; height: 24px;
        }
        .switch-toggle input { opacity: 0; width: 0; height: 0; }
        .switch-slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background-color: #CBD5E1; transition: .3s; border-radius: 24px;
        }
        .switch-slider:before {
            position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px;
            background-color: white; transition: .3s; border-radius: 50%;
        }
        input:checked + .switch-slider { background-color: var(--primary); }
        input:checked + .switch-slider:before { transform: translateX(20px); }

        .btn-burgundy {
            background-color: var(--primary); color: #FFFFFF; font-weight: 600;
            padding: 10px 20px; border-radius: 10px; transition: all 0.2s;
        }
        .btn-burgundy:hover { filter: brightness(90%); }
    </style>
    @endpush

    @php
        $inscriptionsJson = $inscriptions->map(fn($i) => [
            'id' => $i->id,
            'id_etudiant' => $i->id_etudiant,
            'etudiant_lib' => $i->etudiant ? $i->etudiant->nom . ' ' . $i->etudiant->prenom : '—',
            'matricule' => $i->etudiant ? $i->etudiant->matricule : '—',
            'filiere_lib' => $i->filiere ? $i->filiere->libelle : '—',
            'id_filiere' => $i->id_filiere,
            'niveau_lib' => $i->niveau ? $i->niveau->libelle : '—',
            'id_niveau' => $i->id_niveau,
            'classe_lib' => $i->classe ? $i->classe->libelle : '—',
            'id_classe' => $i->id_classe,
            'numero_inscription' => $i->numero_inscription,
            'type_inscription' => $i->type_inscription,
            'statut_paiement' => $i->statut_paiement,
            'date_inscription' => $i->date_inscription,
            'est_boursier' => (bool)$i->est_boursier,
            'id_bourse' => $i->id_bourse,
            'id_annee_scolaire' => $i->id_annee_scolaire
        ])->toJson();

        $etudiantsJson = $etudiants->map(fn($e) => ['id' => $e->id, 'nom' => $e->nom, 'prenom' => $e->prenom])->toJson();
        $parentsJson = $parents->map(fn($p) => ['id' => $p->id, 'nom' => $p->nom, 'prenom' => $p->prenom])->toJson();
        $filieresJson = $filieres->map(fn($f) => ['id' => $f->id, 'libelle' => $f->libelle])->toJson();
        $niveauxJson = $niveaux->map(fn($n) => ['id' => $n->id, 'libelle' => $n->libelle])->toJson();
        $classesJson = $classes->map(fn($c) => ['id' => $c->id, 'libelle' => $c->libelle, 'id_niveau' => $c->id_niveau, 'id_filiere' => $c->id_filiere])->toJson();
        $boursesJson = $bourses->map(fn($b) => ['id' => $b->id, 'libelle' => $b->libelle])->toJson();
        $anneesJson = $annees->map(fn($a) => ['id' => $a->id, 'libelle' => $a->libelle])->toJson();
    @endphp

    <div x-data="inscPage({{ $inscriptionsJson }}, {{ $etudiantsJson }}, {{ $parentsJson }}, {{ $filieresJson }}, {{ $niveauxJson }}, {{ $classesJson }}, {{ $boursesJson }}, {{ $anneesJson }})" class="space-y-6">

        <!-- Title Row -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Inscriptions & Scolarité</h1>
                <p class="text-sm text-slate-500 mt-1" x-text="filteredInscs().length + ' inscription(s) affichée(s)'"></p>
            </div>
            <div class="flex gap-2">
                <button class="bg-[var(--primary)] hover:bg-[var(--primary)] text-white font-bold py-2 px-4 rounded-lg flex items-center gap-1.5 text-xs shadow-sm" @click="openInscModal()">
                    <i class="ri-add-line text-white"></i> Nouvelle Inscription
                </button>
            </div>
        </div>

        <!-- Filter Row -->
        <div class="flex items-center gap-3">
            <select x-model="filterNiveau" class="f-input max-w-[200px] shadow-sm">
                <option value="">Tous les niveaux</option>
                <template x-for="n in niveaux" :key="n.id">
                    <option :value="n.id" x-text="n.libelle"></option>
                </template>
            </select>

            <select x-model="filterClasse" class="f-input max-w-[200px] shadow-sm">
                <option value="">Toutes les classes</option>
                <template x-for="c in classes" :key="c.id">
                    <option :value="c.id" x-text="c.libelle"></option>
                </template>
            </select>
        </div>

        <!-- Table Panel -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100">
                <input type="text" x-model="searchQuery" placeholder="Rechercher nom, prénom, matricule..." class="f-input max-w-xs pl-8 bg-slate-50" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w22.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2394A3B8%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><circle cx=%2211%22 cy=%2211%22 r=%228%22/><line x1=%2221%22 y1=%2221%22 x2=%2216.65%22 y2=%2216.65%22/></svg>'); background-repeat: no-repeat; background-position: 10px 12px;">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="p-4">N° INSCRIPTION</th>
                            <th class="p-4">MATRICULE</th>
                            <th class="p-4">ÉTUDIANT</th>
                            <th class="p-4">FILIÈRE</th>
                            <th class="p-4">CLASSE</th>
                            <th class="p-4">DATE</th>
                            <th class="p-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        <template x-for="i in filteredInscs()" :key="i.id">
                            <tr class="hover:bg-slate-50/20">
                                <td class="p-4 font-bold text-slate-700" x-text="i.numero_inscription"></td>
                                <td class="p-4 font-mono font-bold text-indigo-600" x-text="i.matricule"></td>
                                <td class="p-4 font-bold text-slate-800" x-text="i.etudiant_lib"></td>
                                <td class="p-4 text-slate-500" x-text="i.filiere_lib"></td>
                                <td class="p-4 text-slate-500" x-text="i.classe_lib"></td>
                                <td class="p-4 text-slate-500" x-text="i.date_inscription"></td>
                                <td class="p-4 text-right space-x-0.5">
                                    <button class="action-btn text-blue-600" title="Modifier" @click="openInscModal(i)"><i class="ri-edit-line"></i></button>
                                    <button class="action-btn text-red-600" title="Supprimer" @click="deleteInsc(i.id)"><i class="ri-delete-bin-line"></i></button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="filteredInscs().length === 0">
                            <tr>
                                <td colspan="7" class="text-center py-8 text-slate-400 italic">Aucune inscription enregistrée.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── MODAL: INSCRIPTION FORM ── -->
        <div class="modal-overlay" x-show="showInscModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-[var(--primary)] flex items-center justify-center">
                            <i class="ri-edit-line text-lg"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 text-lg" x-text="isEditInsc ? 'Modifier Inscription' : 'Nouvelle Inscription'"></h3>
                    </div>
                    <button @click="showInscModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-xl"></i></button>
                </div>

                <form @submit.prevent="saveInsc()" class="p-6 overflow-y-auto space-y-5 flex-1">
                    <div x-show="!isEditInsc">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Étudiant *</label>
                        <div class="flex gap-2">
                            <select x-model="inscForm.id_etudiant" :required="!isEditInsc" class="f-input">
                                <option value="">Rechercher...</option>
                                <template x-for="e in etudiants" :key="e.id">
                                    <option :value="e.id" x-text="e.nom + ' ' + e.prenom"></option>
                                </template>
                            </select>
                            <button type="button" @click="showQuickStudentModal = true" class="bg-primary/10 text-[var(--primary)] hover:bg-primary/20 font-bold w-11 h-11 rounded-xl flex items-center justify-center border border-primary/20 transition-colors">
                                <i class="ri-add-line text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Filière *</label>
                            <select x-model="inscForm.id_filiere" required class="f-input">
                                <option value="">Rechercher...</option>
                                <template x-for="f in filieres" :key="f.id">
                                    <option :value="f.id" x-text="f.libelle"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Niveau *</label>
                            <select x-model="inscForm.id_niveau" required class="f-input">
                                <option value="">Rechercher...</option>
                                <template x-for="n in niveaux" :key="n.id">
                                    <option :value="n.id" x-text="n.libelle"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Classe *</label>
                            <select x-model="inscForm.id_classe" required class="f-input">
                                <option value="">Rechercher...</option>
                                <template x-for="c in filteredClassesForForm()" :key="c.id">
                                    <option :value="c.id" x-text="c.libelle"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Année Scolaire *</label>
                            <select x-model="inscForm.id_annee_scolaire" required class="f-input bg-slate-50">
                                <template x-for="a in annees" :key="a.id">
                                    <option :value="a.id" x-text="a.libelle"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Type</label>
                            <select x-model="inscForm.type_inscription" required class="f-input">
                                <option value="Nouvelle">Nouvelle</option>
                                <option value="Réinscription">Réinscription</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Statut paiement</label>
                            <select x-model="inscForm.statut_paiement" required class="f-input">
                                <option value="En attente de paiement">En attente de paiement</option>
                                <option value="Payé partiellement">Payé partiellement</option>
                                <option value="Soldé">Soldé</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Date</label>
                        <input type="date" x-model="inscForm.date_inscription" required class="f-input">
                    </div>

                    <!-- Toggle Rows -->
                    <div class="border border-primary/10 bg-primary/5 rounded-2xl p-4 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-[var(--primary)]">
                            <i class="ri-checkbox-circle-fill text-lg"></i>
                            <span class="text-sm font-semibold">Affecté</span>
                        </div>
                        <label class="switch-toggle">
                            <input type="checkbox" checked>
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <div class="border border-slate-100 bg-slate-50 rounded-2xl p-4 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-slate-600">
                            <i class="ri-checkbox-blank-circle-line text-lg"></i>
                            <span class="text-sm font-semibold">L'étudiant est-il boursier ?</span>
                        </div>
                        <label class="switch-toggle">
                            <input type="checkbox" x-model="inscForm.est_boursier">
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <div x-show="inscForm.est_boursier" class="grid grid-cols-1 gap-3" style="display:none;" x-transition>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Bourse attribuée *</label>
                            <select x-model="inscForm.id_bourse" :required="inscForm.est_boursier" class="f-input">
                                <option value="">Choisir...</option>
                                <template x-for="b in bourses" :key="b.id">
                                    <option :value="b.id" x-text="b.libelle"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end items-center gap-4 pt-4 border-t border-slate-100">
                        <button type="button" @click="showInscModal = false" class="text-sm font-bold text-red-600 hover:text-red-800">Annuler</button>
                        <button type="submit" class="btn-burgundy">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── SUB-MODAL: QUICK STUDENT CREATION (replicated identically with parent select & photo upload) ── -->
        <div class="modal-overlay" x-show="showQuickStudentModal" style="display:none; z-index: 60;" x-transition>
            <div class="modal-content" @click.stop>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-[var(--primary)] flex items-center justify-center">
                            <i class="ri-edit-line text-lg"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 text-lg">Nouvel Étudiant</h3>
                    </div>
                    <button @click="showQuickStudentModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-xl"></i></button>
                </div>
                <form @submit.prevent="saveQuickStudent()" class="p-6 overflow-y-auto space-y-5 flex-1" id="quick_student_form" enctype="multipart/form-data">
                    <!-- Profile avatar placeholder with camera badge -->
                    <div class="flex flex-col items-center justify-center space-y-2">
                        <div class="relative w-24 h-24 rounded-full bg-slate-200 border-2 border-white shadow-sm flex items-center justify-center overflow-hidden">
                            <template x-if="photoPreviewUrl">
                                <img :src="photoPreviewUrl" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!photoPreviewUrl">
                                <i class="ri-user-3-line text-slate-400 text-4xl"></i>
                            </template>
                            <button type="button" @click="triggerPhotoSelect()" class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-[var(--primary)] hover:bg-[var(--primary)] text-white flex items-center justify-center shadow-sm">
                                <i class="ri-camera-line text-sm"></i>
                            </button>
                        </div>
                        <span class="text-xs text-slate-400">Cliquer sur l'icône pour changer la photo</span>
                        <input type="file" id="photo_file" name="photo" class="hidden" accept="image/*" @change="previewPhoto($event)">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nom *</label>
                            <input type="text" x-model="studentForm.nom" required placeholder="Ex: KONÉ" class="f-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Prénom *</label>
                            <input type="text" x-model="studentForm.prenom" required placeholder="Ex: Abou" class="f-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Matricule *</label>
                            <input type="text" x-model="studentForm.matricule" required class="f-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Genre *</label>
                            <select x-model="studentForm.sexe" required class="f-input">
                                <option value="M">Masculin</option>
                                <option value="F">Féminin</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Date de naissance *</label>
                            <input type="date" x-model="studentForm.date_naissance" required class="f-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nationalité *</label>
                            <input type="text" x-model="studentForm.nationalite" required class="f-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Email</label>
                            <input type="email" x-model="studentForm.email" placeholder="etudiant@email.com" class="f-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Contact</label>
                            <input type="text" x-model="studentForm.contact" placeholder="+225 07 00 00 00" class="f-input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Parent / Tuteur</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <select x-model="studentForm.id_parent" class="f-input">
                                    <option value="">Rechercher...</option>
                                    <template x-for="p in parents" :key="p.id">
                                        <option :value="p.id" x-text="p.nom + ' ' + p.prenom"></option>
                                    </template>
                                </select>
                            </div>
                            <button type="button" class="bg-primary/10 text-[var(--primary)] hover:bg-primary/20 font-bold w-11 h-11 rounded-xl flex items-center justify-center border border-primary/20 transition-colors" @click="showQuickParentModal = true">
                                <i class="ri-add-line text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-3 mt-4">
                        <button type="button" @click="showQuickStudentModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold">Annuler</button>
                        <button type="submit" class="btn-burgundy">Créer l'étudiant</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── SUB-MODAL: QUICK PARENT (inside quick student creation) ── -->
        <div class="modal-overlay" x-show="showQuickParentModal" style="display:none; z-index: 65;" x-transition>
            <div class="modal-content" @click.stop style="max-width: 500px;">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800">Nouveau Parent / Tuteur</h3>
                    <button @click="showQuickParentModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-xl"></i></button>
                </div>
                <form @submit.prevent="saveQuickParent()" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nom *</label>
                            <input type="text" x-model="parentForm.nom" required class="f-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Prénom *</label>
                            <input type="text" x-model="parentForm.prenom" required class="f-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Genre *</label>
                            <select x-model="parentForm.sexe" required class="f-input">
                                <option value="M">Masculin</option>
                                <option value="F">Féminin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Lien parental *</label>
                            <select x-model="parentForm.lien_parental" required class="f-input">
                                <option value="Père">Père</option>
                                <option value="Mère">Mère</option>
                                <option value="Tuteur">Tuteur</option>
                                <option value="Tutrice">Tutrice</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Contact principal *</label>
                        <input type="text" x-model="parentForm.contact_1" required class="f-input">
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-3 mt-4">
                        <button type="button" @click="showQuickParentModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold">Annuler</button>
                        <button type="submit" class="btn-burgundy">Créer le tuteur</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('inscPage', (inscriptions, etudiants, parents, filieres, niveaux, classes, bourses, annees) => ({
                inscriptions, etudiants, parents, filieres, niveaux, classes, bourses, annees,
                searchQuery: '',
                filterNiveau: '',
                filterClasse: '',
                showInscModal: false,
                isEditInsc: false,
                
                showQuickStudentModal: false,
                showQuickParentModal: false,
                photoPreviewUrl: null,

                inscForm: { id: null, id_etudiant: '', id_filiere: '', id_niveau: '', id_classe: '', id_annee_scolaire: '', type_inscription: 'Nouvelle', statut_paiement: 'En attente de paiement', date_inscription: '', est_boursier: false, id_bourse: '' },
                studentForm: { nom: '', prenom: '', sexe: 'M', matricule: '', date_naissance: '', email: '', contact: '', nationalite: 'Ivoirienne', id_parent: '' },
                parentForm: { nom: '', prenom: '', sexe: 'M', contact_1: '', contact_2: '', email: '', lien_parental: 'Père', profession: '', nationalite: 'Ivoirienne' },

                init() {
                    this.resetQuickStudentForm();
                },

                resetQuickStudentForm() {
                    const today = new Date().toISOString().substring(0,10);
                    const randMat = 'ETU-' + new Date().getFullYear() + '-' + Math.floor(1000 + Math.random() * 9000);
                    this.studentForm = { nom: '', prenom: '', sexe: 'M', matricule: randMat, date_naissance: today, email: '', contact: '', nationalite: 'Ivoirienne', id_parent: '' };
                    this.photoPreviewUrl = null;
                },

                filteredInscs() {
                    const q = this.searchQuery.toLowerCase();
                    return this.inscriptions.filter(i => {
                        const mSearch = !q || i.etudiant_lib.toLowerCase().includes(q) || i.numero_inscription.toLowerCase().includes(q) || i.matricule.toLowerCase().includes(q);
                        if (!mSearch) return false;
                        if (this.filterNiveau && i.id_niveau != this.filterNiveau) return false;
                        if (this.filterClasse && i.id_classe != this.filterClasse) return false;
                        return true;
                    });
                },

                filteredClassesForForm() {
                    return this.classes.filter(c => {
                        if (this.inscForm.id_niveau && c.id_niveau != this.inscForm.id_niveau) return false;
                        if (this.inscForm.id_filiere && c.id_filiere && c.id_filiere != this.inscForm.id_filiere) return false;
                        return true;
                    });
                },

                openInscModal(insc = null) {
                    this.isEditInsc = !!insc;
                    const today = new Date().toISOString().substring(0,10);
                    if (insc) {
                        this.inscForm = { ...insc, id_bourse: insc.id_bourse || '' };
                    } else {
                        const defaultAnnee = this.annees.length > 0 ? this.annees[0].id : '';
                        this.inscForm = { id: null, id_etudiant: '', id_filiere: '', id_niveau: '', id_classe: '', id_annee_scolaire: defaultAnnee, type_inscription: 'Nouvelle', statut_paiement: 'En attente de paiement', date_inscription: today, est_boursier: false, id_bourse: '' };
                    }
                    this.showInscModal = true;
                },

                triggerPhotoSelect() {
                    document.getElementById('photo_file').click();
                },

                previewPhoto(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.photoPreviewUrl = URL.createObjectURL(file);
                    }
                },

                async saveInsc() {
                    const url = this.isEditInsc ? `/etudiants/inscriptions/${this.inscForm.id}` : '/etudiants/inscriptions';
                    const method = this.isEditInsc ? 'PUT' : 'POST';
                    try {
                        const r = await fetch(url, {
                            method,
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.inscForm)
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

                async saveQuickStudent() {
                    const fd = new FormData();
                    fd.append('nom', this.studentForm.nom);
                    fd.append('prenom', this.studentForm.prenom);
                    fd.append('sexe', this.studentForm.sexe);
                    fd.append('matricule', this.studentForm.matricule);
                    fd.append('date_naissance', this.studentForm.date_naissance);
                    fd.append('nationalite', this.studentForm.nationalite);
                    fd.append('email', this.studentForm.email || '');
                    fd.append('contact', this.studentForm.contact || '');
                    fd.append('id_parent', this.studentForm.id_parent || '');

                    const photoInput = document.getElementById('photo_file');
                    if (photoInput && photoInput.files.length > 0) {
                        fd.append('photo', photoInput.files[0]);
                    }

                    try {
                        const r = await fetch('/etudiants', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: fd
                        });
                        const res = await r.json();
                        if (res.success) {
                            this.etudiants.push({
                                id: res.data.id,
                                nom: res.data.nom,
                                prenom: res.data.prenom
                            });
                            this.inscForm.id_etudiant = res.data.id;
                            this.showQuickStudentModal = false;
                            this.resetQuickStudentForm();
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },

                async saveQuickParent() {
                    try {
                        const r = await fetch('/etudiants/parents', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.parentForm)
                        });
                        const res = await r.json();
                        if (res.success) {
                            this.parents.push({
                                id: res.id,
                                nom: res.nom,
                                prenom: res.prenom
                            });
                            this.studentForm.id_parent = res.id;
                            this.showQuickParentModal = false;
                            this.parentForm = { nom: '', prenom: '', sexe: 'M', contact_1: '', contact_2: '', email: '', lien_parental: 'Père', profession: '', nationalite: 'Ivoirienne' };
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },

                async deleteInsc(id) {
                    if (!confirm('Supprimer cette inscription ? Les données financières et scolaires liées seront effacées.')) return;
                    try {
                        const r = await fetch(`/etudiants/inscriptions/${id}`, {
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
