<x-app-layout>
    <x-slot name="title">Liste des Étudiants</x-slot>

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        .badge-status-actif { background: #DCFCE7; color: #15803D; border-radius: 9999px; padding: 2px 10px; font-weight: 600; }
        .badge-status-inactif { background: #FEE2E2; color: #B91C1C; border-radius: 9999px; padding: 2px 10px; font-weight: 600; }
        .badge-valide { background: #D1FAE5; color: #065F46; }
        .badge-attente { background: #FEF3C7; color: #92400E; }
        .badge-rejete { background: #FEE2E2; color: #991B1B; }

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
        .f-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(142, 29, 81, 0.15); }
        
        .action-btn {
            width: 32px; height: 32px; border-radius: 8px; display: inline-flex;
            align-items: center; justify-content: center; transition: all 0.2s;
        }
        .action-btn:hover { background: #E2E8F0; }

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
        .info-tile {
            background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px;
            padding: 10px 14px; display: flex; align-items: flex-start; gap: 10px;
            width: calc(50% - 6px); min-width: 240px;
        }
    </style>
    @endpush

    @php
        $etudiantsJson = $etudiants->map(fn($e) => [
            'id' => $e->id,
            'matricule' => $e->matricule,
            'nom' => $e->nom,
            'prenom' => $e->prenom,
            'sexe' => $e->sexe,
            'date_naissance' => $e->date_naissance,
            'email' => $e->email,
            'contact' => $e->contact,
            'nationalite' => $e->nationalite,
            'url_photo' => $e->url_photo,
            'id_parent' => $e->id_parent,
            'id_statut' => (int)$e->id_statut
        ])->toJson();

        $parentsJson = $parents->map(fn($p) => [
            'id' => $p->id,
            'nom' => $p->nom,
            'prenom' => $p->prenom,
            'sexe' => $p->sexe,
            'contact_1' => $p->contact_1,
            'contact_2' => $p->contact_2,
            'email' => $p->email,
            'lien_parental' => $p->lien_parental,
            'profession' => $p->profession,
            'nationalite' => $p->nationalite
        ])->toJson();

        $niveauxJson = $niveaux->map(fn($n) => ['id' => $n->id, 'libelle' => $n->libelle])->toJson();
        $classesJson = $classes->map(fn($c) => ['id' => $c->id, 'libelle' => $c->libelle])->toJson();
        $typesDocumentJson = $typesDocument->map(fn($t) => ['id' => $t->id, 'libelle' => $t->libelle])->toJson();
        $statutsJson = $statuts->map(fn($s) => ['id' => $s->id, 'libelle' => $s->libelle])->toJson();
        
        $inscriptionsJson = $inscriptions->map(fn($i) => [
            'id' => $i->id,
            'id_etudiant' => $i->id_etudiant,
            'id_niveau' => $i->id_niveau,
            'id_classe' => $i->id_classe,
            'numero_inscription' => $i->numero_inscription,
            'date_inscription' => $i->date_inscription,
            'id_annee_scolaire' => $i->id_annee_scolaire
        ])->toJson();

        $dossiersJson = $dossiers->map(fn($d) => [
            'id' => $d->id,
            'id_etudiant' => $d->id_etudiant,
            'id_inscription' => $d->id_inscription,
            'id_type_document' => $d->id_type_document,
            'url_document' => $d->url_document,
            'date_ajout' => $d->date_ajout,
            'id_statut' => $d->id_statut
        ])->toJson();
    @endphp

    <div x-data="studentsPage({{ $etudiantsJson }}, {{ $parentsJson }}, {{ $niveauxJson }}, {{ $classesJson }}, {{ $typesDocumentJson }}, {{ $statutsJson }}, {{ $inscriptionsJson }}, {{ $dossiersJson }})" class="space-y-6">

        <!-- Title Row -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Étudiants</h1>
                <p class="text-sm text-slate-500 mt-1" x-text="filteredEtudiants().length + ' étudiant(s) affiché(s)'"></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold py-2 px-3 rounded-lg flex items-center gap-1.5 text-xs shadow-sm" @click="downloadTemplate()">
                    <i class="ri-download-line text-slate-500"></i> Modèle CSV
                </button>
                <button class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold py-2 px-3 rounded-lg flex items-center gap-1.5 text-xs shadow-sm" @click="showImportModal = true">
                    <i class="ri-upload-2-line text-slate-500"></i> Importer CSV
                </button>
                <button class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold py-2 px-3 rounded-lg flex items-center gap-1.5 text-xs shadow-sm" @click="exportCsv()">
                    <i class="ri-table-line text-slate-500"></i> Exporter CSV
                </button>
                <button class="bg-[var(--primary)] hover:bg-[var(--primary)] text-white font-bold py-2 px-4 rounded-lg flex items-center gap-1.5 text-xs shadow-sm" @click="openStudentModal()">
                    <i class="ri-add-line text-white"></i> Nouvel Étudiant
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

            <template x-if="filterNiveau || filterClasse">
                <button @click="resetFilters()" class="text-xs text-[var(--primary)] hover:text-[var(--primary)] font-bold flex items-center gap-1">
                    <i class="ri-close-line"></i> Réinitialiser
                </button>
            </template>
        </div>

        <!-- Data Panel -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100">
                <input type="text" x-model="searchQuery" placeholder="Rechercher nom, prénom, matricule..." class="f-input max-w-xs pl-8 bg-slate-50" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w22.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2394A3B8%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><circle cx=%2211%22 cy=%2211%22 r=%228%22/><line x1=%2221%22 y1=%2221%22 x2=%2216.65%22 y2=%2216.65%22/></svg>'); background-repeat: no-repeat; background-position: 10px 12px;">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="p-4">MATRICULE</th>
                            <th class="p-4">ÉTUDIANT</th>
                            <th class="p-4">CONTACT</th>
                            <th class="p-4">STATUT</th>
                            <th class="p-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        <template x-for="e in filteredEtudiants()" :key="e.id">
                            <tr class="hover:bg-slate-50/20">
                                <td class="p-4 font-mono font-bold text-indigo-600" x-text="e.matricule"></td>
                                <td class="p-4 flex items-center gap-3">
                                    <template x-if="e.url_photo">
                                        <img :src="e.url_photo" class="w-8 h-8 rounded-full object-cover border">
                                    </template>
                                    <template x-if="!e.url_photo">
                                        <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs" x-text="e.nom.charAt(0)"></div>
                                    </template>
                                    <div>
                                        <div class="font-bold text-slate-800" x-text="e.nom + ' ' + e.prenom"></div>
                                        <div class="text-xs text-slate-400" x-text="e.email || '—'"></div>
                                    </div>
                                </td>
                                <td class="p-4 text-slate-500" x-text="e.contact || '—'"></td>
                                <td class="p-4">
                                    <span class="badge-status-actif" :class="e.id_statut === 1 ? 'badge-status-actif' : 'badge-status-inactif'" x-text="e.id_statut === 1 ? 'Actif' : 'Inactif'"></span>
                                </td>
                                <td class="p-4 text-right space-x-0.5">
                                    <button class="action-btn text-indigo-600" title="Détails" @click="openDetailsModal(e)"><i class="ri-user-search-line"></i></button>
                                    <button class="action-btn text-teal-600" title="Bulletin" @click="printDoc(e, 'bulletin')"><i class="ri-file-text-line"></i></button>
                                    <button class="action-btn text-purple-600" title="Carte" @click="printDoc(e, 'carte')"><i class="ri-profile-line"></i></button>
                                    <button class="action-btn text-amber-600" title="Dossier" @click="openDossierModal(e)"><i class="ri-folder-open-line"></i></button>
                                    <button class="action-btn text-blue-600" title="Modifier" @click="openStudentModal(e)"><i class="ri-edit-line"></i></button>
                                    <button class="action-btn text-red-600" title="Supprimer" @click="deleteStudent(e.id)"><i class="ri-delete-bin-line"></i></button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="filteredEtudiants().length === 0">
                            <tr>
                                <td colspan="5" class="text-center py-8 text-slate-400 italic">Aucun étudiant trouvé.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── MODAL: DETAILS ── -->
        <div class="modal-overlay" x-show="showDetailsModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop style="max-width: 700px; padding: 0;">
                <div class="bg-indigo-600 text-white p-6 rounded-t-xl flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <template x-if="selectedStudent && selectedStudent.url_photo">
                            <img :src="selectedStudent.url_photo" class="w-14 h-14 rounded-full object-cover border border-white/20">
                        </template>
                        <template x-if="selectedStudent && !selectedStudent.url_photo">
                            <div class="w-14 h-14 bg-white/20 text-white rounded-full flex items-center justify-center font-bold text-lg" x-text="selectedStudent.nom.charAt(0)"></div>
                        </template>
                        <div>
                            <h3 class="font-bold text-lg" x-text="selectedStudent ? selectedStudent.nom + ' ' + selectedStudent.prenom : ''"></h3>
                            <div class="flex gap-2 mt-2">
                                <span class="px-2 py-0.5 rounded bg-white/20 text-white text-[10px] font-bold" x-text="selectedStudent ? selectedStudent.matricule : ''"></span>
                                <span class="px-2 py-0.5 rounded bg-white/20 text-white text-[10px] font-bold" x-text="selectedStudent ? (selectedStudent.sexe === 'M' ? 'Masculin' : 'Féminin') : ''"></span>
                            </div>
                        </div>
                    </div>
                    <button @click="showDetailsModal = false" class="text-white/80 hover:text-white"><i class="ri-close-line text-xl"></i></button>
                </div>
                <div class="p-6 space-y-6 overflow-y-auto max-h-[70vh]">
                    <!-- Infos personnelles -->
                    <div>
                        <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2 border-b border-indigo-50 pb-1 flex items-center gap-1.5"><i class="ri-user-line"></i> Informations personnelles</h4>
                        <div class="flex flex-wrap gap-3">
                            <div class="info-tile">
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase">Date de naissance</div>
                                    <div class="font-semibold text-slate-700 text-sm" x-text="selectedStudent ? selectedStudent.date_naissance : '—'"></div>
                                </div>
                            </div>
                            <div class="info-tile">
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase">Nationalité</div>
                                    <div class="font-semibold text-slate-700 text-sm" x-text="selectedStudent ? selectedStudent.nationalite : '—'"></div>
                                </div>
                            </div>
                            <div class="info-tile">
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase">Email</div>
                                    <div class="font-semibold text-slate-700 text-sm" x-text="selectedStudent ? selectedStudent.email || '—' : '—'"></div>
                                </div>
                            </div>
                            <div class="info-tile">
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase">Contact</div>
                                    <div class="font-semibold text-slate-700 text-sm" x-text="selectedStudent ? selectedStudent.contact || '—' : '—'"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scolarité -->
                    <div>
                        <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2 border-b border-indigo-50 pb-1 flex items-center gap-1.5"><i class="ri-book-open-line"></i> Inscription</h4>
                        <div class="flex flex-wrap gap-3">
                            <div class="info-tile">
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase">Niveau</div>
                                    <div class="font-semibold text-slate-700 text-sm" x-text="selectedInscription() ? selectedInscription().niveau_lib : 'Non inscrit'"></div>
                                </div>
                            </div>
                            <div class="info-tile">
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase">Classe</div>
                                    <div class="font-semibold text-slate-700 text-sm" x-text="selectedInscription() ? selectedInscription().classe_lib : 'Non inscrit'"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Parent / Tuteur -->
                    <div>
                        <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2 border-b border-indigo-50 pb-1 flex items-center gap-1.5"><i class="ri-parent-line"></i> Parent / Tuteur</h4>
                        <template x-if="selectedParent()">
                            <div class="flex flex-wrap gap-3">
                                <div class="info-tile">
                                    <div>
                                        <div class="text-[10px] text-slate-400 uppercase">Nom complet</div>
                                        <div class="font-semibold text-slate-700 text-sm" x-text="selectedParent().nom + ' ' + selectedParent().prenom"></div>
                                    </div>
                                </div>
                                <div class="info-tile">
                                    <div>
                                        <div class="text-[10px] text-slate-400 uppercase">Lien familial</div>
                                        <div class="font-semibold text-slate-700 text-sm" x-text="selectedParent().lien_parental"></div>
                                    </div>
                                </div>
                                <div class="info-tile">
                                    <div>
                                        <div class="text-[10px] text-slate-400 uppercase">Téléphone</div>
                                        <div class="font-semibold text-slate-700 text-sm" x-text="selectedParent().contact_1"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="!selectedParent()">
                            <p class="text-xs text-slate-400 italic">Aucun parent/tuteur rattaché.</p>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── MODAL: NOUVEL ETUDIANT (Flutter styled) ── -->
        <div class="modal-overlay" x-show="showStudentModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop>
                <!-- Title header with card styling -->
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-[var(--primary)] flex items-center justify-center">
                            <i class="ri-edit-line text-lg"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 text-lg" x-text="isEditStudent ? 'Modifier Étudiant' : 'Nouvel Étudiant'"></h3>
                    </div>
                    <button @click="showStudentModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-xl"></i></button>
                </div>

                <form @submit.prevent="saveStudent()" class="p-6 overflow-y-auto space-y-5 flex-1" id="student_form" enctype="multipart/form-data">
                    <!-- Profile avatar placeholder with camera badge -->
                    <div class="flex flex-col items-center justify-center space-y-2">
                        <div class="relative w-24 h-24 rounded-full bg-slate-200 border-2 border-white shadow-sm flex items-center justify-center overflow-hidden">
                            <template x-if="photoPreviewUrl">
                                <img :src="photoPreviewUrl" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!photoPreviewUrl && studentForm.url_photo">
                                <img :src="studentForm.url_photo" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!photoPreviewUrl && !studentForm.url_photo">
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

                    <div class="flex justify-end items-center gap-4 pt-4 border-t border-slate-100">
                        <button type="button" @click="showStudentModal = false" class="text-sm font-bold text-red-600 hover:text-red-800">Annuler</button>
                        <button type="submit" class="btn-burgundy">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── MODAL: QUICK PARENT ── -->
        <div class="modal-overlay" x-show="showQuickParentModal" style="display:none; z-index: 60;" x-transition>
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

        <!-- ── MODAL: DOSSIER (Flutter styled) ── -->
        <div class="modal-overlay" x-show="showDossierModal" style="display:none;" x-transition>
            <div class="modal-content" @click.stop style="max-width: 600px;">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-primary/10 text-[var(--primary)] flex items-center justify-center"><i class="ri-folder-open-line"></i></span>
                        <span x-text="'Dossier — ' + (selectedStudent ? selectedStudent.nom + ' ' + selectedStudent.prenom : '')"></span>
                    </h3>
                    <button @click="showDossierModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-xl"></i></button>
                </div>

                <div class="p-6 flex flex-col flex-1 min-h-[300px] overflow-y-auto">
                    <!-- Uploded documents list inside folder modal -->
                    <div class="flex-1 space-y-2 mb-4">
                        <template x-for="d in selectedStudentDocs()" :key="d.id">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2 text-xs">
                                <div>
                                    <div class="font-bold text-slate-700" x-text="getTypeDocLibelle(d.id_type_document)"></div>
                                    <div class="text-[10px] text-slate-400" x-text="d.date_ajout"></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold" :class="d.id_statut === 1 ? 'badge-valide' : (d.id_statut === 2 ? 'badge-rejete' : 'badge-attente')" x-text="d.id_statut === 1 ? 'Validé' : (d.id_statut === 2 ? 'Rejeté' : 'En attente')"></span>
                                    <template x-if="d.url_document">
                                        <a :href="d.url_document" target="_blank" class="w-7 h-7 hover:bg-slate-50 rounded flex items-center justify-center text-blue-600">
                                            <i class="ri-open-in-new-line"></i>
                                        </a>
                                    </template>
                                    <button @click="deleteDoc(d.id)" class="w-7 h-7 hover:bg-slate-50 rounded flex items-center justify-center text-rose-600">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template x-if="selectedStudentDocs().length === 0">
                            <div class="py-12 flex flex-col items-center justify-center space-y-3">
                                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                    <i class="ri-folder-line text-3xl"></i>
                                </div>
                                <span class="text-slate-500 font-medium">Aucun document dans ce dossier</span>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center gap-4 pt-6 justify-between border-t border-slate-100">
                        <button type="button" @click="showDossierModal = false" class="text-sm font-bold text-slate-500 hover:text-slate-700">Fermer</button>
                        <button type="button" @click="showDocTypeSelectModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl flex items-center gap-2 text-xs transition-all shadow-sm">
                            <i class="ri-file-upload-line text-sm"></i> Ajouter des documents
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── SUB-MODAL: TYPE DE DOCUMENT SELECTION (Flutter styled inside Dossier) ── -->
        <div class="modal-overlay" x-show="showDocTypeSelectModal" style="display:none; z-index: 55;" x-transition>
            <div class="modal-content" @click.stop style="max-width: 440px;">
                <div class="p-6 space-y-5">
                    <h3 class="font-bold text-slate-800 text-lg">Type de document</h3>
                    
                    <!-- Radio list of document types -->
                    <div class="space-y-3 max-h-56 overflow-y-auto">
                        <template x-for="t in typesDocument" :key="t.id">
                            <label class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50/50 transition-colors">
                                <span class="text-sm text-slate-700 font-medium" x-text="t.libelle"></span>
                                <input type="radio" name="selected_doc_type" :value="t.id" x-model="selectedDocType" class="text-[var(--primary)] focus:ring-[var(--primary)] h-4 w-4">
                            </label>
                        </template>
                    </div>

                    <div class="flex justify-end items-center gap-4 pt-4 border-t border-slate-100">
                        <button type="button" @click="showDocTypeSelectModal = false; selectedDocType = null" class="text-sm font-bold text-red-600 hover:text-red-800">Annuler</button>
                        <button type="button" :disabled="!selectedDocType" @click="selectFileAndUpload()" class="btn-burgundy disabled:opacity-50 disabled:cursor-not-allowed">
                            Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden input for document file upload -->
        <input type="file" id="dossier_upload_file" class="hidden" @change="uploadDossierFile($event)">

    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('studentsPage', (etudiants, parents, niveaux, classes, typesDocument, statuts, inscriptions, dossiers) => ({
                etudiants, parents, niveaux, classes, typesDocument, statuts, inscriptions, dossiers,
                
                searchQuery: '',
                filterNiveau: '',
                filterClasse: '',

                selectedStudent: null,
                photoPreviewUrl: null,
                
                // Document uploading steps
                showDocTypeSelectModal: false,
                selectedDocType: null,

                // Modal controllers
                showDetailsModal: false,
                showDossierModal: false,
                showStudentModal: false,
                isEditStudent: false,
                showQuickParentModal: false,
                showImportModal: false,

                // Forms
                studentForm: { id: null, nom: '', prenom: '', sexe: 'M', matricule: '', date_naissance: '', email: '', contact: '', nationalite: 'Ivoirienne', id_parent: '', url_photo: '' },
                parentForm: { nom: '', prenom: '', sexe: 'M', contact_1: '', contact_2: '', email: '', lien_parental: 'Père', profession: '', nationalite: 'Ivoirienne' },

                init() {
                    this.resetStudentForm();
                },

                resetStudentForm() {
                    const today = new Date().toISOString().substring(0,10);
                    const randMat = 'ETU-' + new Date().getFullYear() + '-' + Math.floor(1000 + Math.random() * 9000);
                    this.studentForm = { id: null, nom: '', prenom: '', sexe: 'M', matricule: randMat, date_naissance: today, email: '', contact: '', nationalite: 'Ivoirienne', id_parent: '', url_photo: '' };
                    this.photoPreviewUrl = null;
                },

                filteredEtudiants() {
                    const q = this.searchQuery.toLowerCase();
                    return this.etudiants.filter(e => {
                        const matchesSearch = !q || e.nom.toLowerCase().includes(q) || e.prenom.toLowerCase().includes(q) || e.matricule.toLowerCase().includes(q);
                        if (!matchesSearch) return false;

                        if (this.filterNiveau || this.filterClasse) {
                            const studentInscs = this.inscriptions.filter(i => i.id_etudiant == e.id);
                            if (studentInscs.length === 0) return false;

                            if (this.filterNiveau) {
                                const hasNiveau = studentInscs.some(i => i.id_niveau == this.filterNiveau);
                                if (!hasNiveau) return false;
                            }
                            if (this.filterClasse) {
                                const hasClasse = studentInscs.some(i => i.id_classe == this.filterClasse);
                                if (!hasClasse) return false;
                            }
                        }
                        return true;
                    });
                },

                resetFilters() {
                    this.filterNiveau = '';
                    this.filterClasse = '';
                },

                openDetailsModal(student) {
                    this.selectedStudent = student;
                    this.showDetailsModal = true;
                },

                selectedInscription() {
                    if (!this.selectedStudent) return null;
                    const ins = this.inscriptions.find(i => i.id_etudiant == this.selectedStudent.id);
                    if (!ins) return null;
                    const niv = this.niveaux.find(n => n.id == ins.id_niveau);
                    const cls = this.classes.find(c => c.id == ins.id_classe);
                    return {
                        niveau_lib: niv ? niv.libelle : '—',
                        classe_lib: cls ? cls.libelle : '—'
                    };
                },

                selectedParent() {
                    if (!this.selectedStudent || !this.selectedStudent.id_parent) return null;
                    return this.parents.find(p => p.id == this.selectedStudent.id_parent);
                },

                openDossierModal(student) {
                    this.selectedStudent = student;
                    this.showDossierModal = true;
                },

                selectedStudentDocs() {
                    if (!this.selectedStudent) return [];
                    return this.dossiers.filter(d => d.id_etudiant == this.selectedStudent.id);
                },

                getTypeDocLibelle(typeId) {
                    const t = this.typesDocument.find(x => x.id == typeId);
                    return t ? t.libelle : '—';
                },

                selectFileAndUpload() {
                    this.showDocTypeSelectModal = false;
                    document.getElementById('dossier_upload_file').click();
                },

                async uploadDossierFile(event) {
                    const file = event.target.files[0];
                    if (!file || !this.selectedStudent || !this.selectedDocType) return;

                    const fd = new FormData();
                    fd.append('id_etudiant', this.selectedStudent.id);
                    fd.append('id_type_document', this.selectedDocType);
                    fd.append('fichier', file);

                    try {
                        const r = await fetch('/etudiants/dossier', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: fd
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

                async deleteDoc(id) {
                    if (!confirm('Supprimer ce document justificatif ?')) return;
                    try {
                        const r = await fetch(`/etudiants/dossier/${id}`, {
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

                openStudentModal(student = null) {
                    this.isEditStudent = !!student;
                    if (student) {
                        this.studentForm = { ...student, id_parent: student.id_parent || '' };
                    } else {
                        this.resetStudentForm();
                    }
                    this.showStudentModal = true;
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

                async saveStudent() {
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

                    let url = '/etudiants';
                    if (this.isEditStudent) {
                        url = `/etudiants/${this.studentForm.id}`;
                        fd.append('_method', 'PUT');
                    }

                    try {
                        const r = await fetch(url, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: fd
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

                async deleteStudent(id) {
                    if (!confirm("Attention ! Supprimer cet étudiant effacera également toutes ses inscriptions, notes, échéanciers, factures et historiques de paiements. Confirmer ?")) return;
                    try {
                        const r = await fetch(`/etudiants/${id}`, {
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

                printDoc(student, type) {
                    alert(`Génération de PDF (${type}) en cours de développement...`);
                }
            }));
        });
    </script>
    @endpush

</x-app-layout>
