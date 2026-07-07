<x-app-layout>
    <x-slot name="title">Documents & Attestations</x-slot>

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        .f-input {
            height: 40px; border: 1px solid #CBD5E1; border-radius: 8px;
            padding: 8px 12px; font-size: 13px; outline: none; transition: all 0.2s;
            background-color: #FFFFFF; color: #334155;
        }
        .f-input:focus { border-color: var(--primary); }
        
        .doc-card {
            background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px;
            padding: 24px; transition: all 0.2s; cursor: pointer; display: flex;
            align-items: flex-start; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .doc-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); border-color: var(--primary); }
        
        .toggle-btn {
            height: 40px; padding: 0 16px; display: inline-flex; align-items: center;
            font-size: 13px; font-weight: bold; border-radius: 8px; transition: all 0.2s;
            cursor: pointer;
        }
    </style>
    @endpush

    @php
        $etudiantsJson = $etudiants->map(fn($e) => ['id' => $e->id, 'nom' => $e->nom, 'prenom' => $e->prenom])->toJson();
        $inscriptionsJson = $inscriptions->map(fn($i) => ['id' => $i->id, 'id_etudiant' => $i->id_etudiant])->toJson();
        $niveauxJson = $niveaux->map(fn($n) => ['id' => $n->id, 'libelle' => $n->libelle])->toJson();
        $filieresJson = $filieres->map(fn($f) => ['id' => $f->id, 'libelle' => $f->libelle])->toJson();
    @endphp

    <div x-data="docsPage({{ $etudiantsJson }}, {{ $inscriptionsJson }}, {{ $niveauxJson }}, {{ $filieresJson }})" class="space-y-6 flex flex-col h-[85vh]">

        <!-- Header Row -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Documents & Attestations</h1>
                <p class="text-xs text-slate-500 mt-1">Génération de documents officiels</p>
            </div>

            <!-- Controls (Batch mode vs Individual) -->
            <div class="flex items-center gap-3">
                
                <!-- Toggle switches -->
                <div class="bg-white border border-slate-200 rounded-lg flex overflow-hidden shadow-sm p-1">
                    <button @click="isBatchMode = false" class="toggle-btn" :class="!isBatchMode ? 'bg-[var(--primary)]/10 text-[var(--primary)]' : 'text-slate-500 hover:bg-slate-50'">
                        Individuel
                    </button>
                    <button @click="isBatchMode = true" class="toggle-btn" :class="isBatchMode ? 'bg-[var(--primary)]/10 text-[var(--primary)]' : 'text-slate-500 hover:bg-slate-50'">
                        Par Classe
                    </button>
                </div>

                <!-- Student Dropdown -->
                <div x-show="!isBatchMode">
                    <select x-model="selectedStudent" class="f-input shadow-sm max-w-xs">
                        <option value="">Sélectionner étudiant...</option>
                        <template x-for="e in etudiants" :key="e.id">
                            <option :value="e.id" x-text="e.nom + ' ' + e.prenom"></option>
                        </template>
                    </select>
                </div>

                <!-- Class Filters -->
                <div x-show="isBatchMode" class="flex gap-2" style="display:none;">
                    <select x-model="selectedNiveau" class="f-input shadow-sm">
                        <option value="">Niveau...</option>
                        <template x-for="n in niveaux" :key="n.id">
                            <option :value="n.id" x-text="n.libelle"></option>
                        </template>
                    </select>
                    <select x-model="selectedFiliere" class="f-input shadow-sm">
                        <option value="">Filière...</option>
                        <template x-for="f in filieres" :key="f.id">
                            <option :value="f.id" x-text="f.libelle"></option>
                        </template>
                    </select>
                </div>

            </div>
        </div>

        <!-- Grid of 9 document cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 flex-1 overflow-y-auto pt-2">
            <template x-for="doc in docsList" :key="doc.type">
                <div class="doc-card" @click="generateDoc(doc.type)">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-[var(--primary)] flex items-center justify-center text-2xl shadow-sm">
                        <i :class="doc.icon"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm" x-text="doc.title"></h4>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed" x-text="doc.subtitle"></p>
                    </div>
                </div>
            </template>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('docsPage', (etudiants, inscriptions, niveaux, filieres) => ({
                etudiants, inscriptions, niveaux, filieres,
                isBatchMode: false,
                selectedStudent: '',
                selectedNiveau: '',
                selectedFiliere: '',

                docsList: [
                    { title: 'Bulletin de Notes', icon: 'ri-file-list-3-line', type: 'bulletin', subtitle: 'Résultats par matière avec CC et Examen' },
                    { title: "Fiche d'Inscription", icon: 'ri-user-add-line', type: 'fiche', subtitle: "Informations d'inscription complètes" },
                    { title: "Attestation d'Inscription", icon: 'ri-verified-badge-line', type: 'attestation', subtitle: 'Attestation officielle de scolarité' },
                    { title: 'Attestation de Réussite', icon: 'ri-award-line', type: 'reussite', subtitle: "Attestation de fin d'année réussie" },
                    { title: 'Relevé de Notes', icon: 'ri-survey-line', type: 'releve', subtitle: 'Tableau complet de toutes les notes' },
                    { title: 'Facture', icon: 'ri-bill-line', type: 'facture', subtitle: 'Facture de scolarité' },
                    { title: 'Reçu de Paiement', icon: 'ri-receipt-line', type: 'recu', subtitle: 'Reçu pour un paiement effectué' },
                    { title: 'Carte Étudiant', icon: 'ri-bank-card-line', type: 'carte', subtitle: "Carte d'identité étudiante" },
                    { title: 'Emploi du Temps', icon: 'ri-calendar-line', type: 'emploi', subtitle: 'Grille horaire de la classe' },
                ],

                generateDoc(type) {
                    let url = `/documents/generate?type=${type}`;
                    if (this.isBatchMode) {
                        if (!this.selectedNiveau || !this.selectedFiliere) {
                            alert('Veuillez sélectionner un Niveau et une Filière pour la génération par classe.');
                            return;
                        }
                        url += `&id_niveau=${this.selectedNiveau}&id_filiere=${this.selectedFiliere}`;
                    } else {
                        if (!this.selectedStudent) {
                            alert('Veuillez sélectionner un étudiant.');
                            return;
                        }
                        url += `&id_etudiant=${this.selectedStudent}`;
                    }
                    window.open(url, '_blank');
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
