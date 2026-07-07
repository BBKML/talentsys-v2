<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Génération de document - {{ ucfirst($type) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @media print {
            body { background-color: #FFFFFF; font-size: 12px; }
            .no-print { display: none; }
            .page-break { page-break-after: always; }
        }
        .page-container {
            width: 21cm;
            min-height: 29.7cm;
            padding: 2cm;
            margin: 1cm auto;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #E2E8F0;
        }
        @media print {
            .page-container {
                margin: 0;
                box-shadow: none;
                border: none;
                width: 100%;
                min-height: auto;
                padding: 1cm;
            }
        }
        .badge-student-card {
            width: 8.5cm;
            height: 5.4cm;
            border: 2px solid #5A67D8;
            border-radius: 12px;
            padding: 12px;
            position: relative;
            background: #FAFAFA;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased">

    <!-- Print floating action banner -->
    <div class="no-print bg-slate-900 text-white p-4 sticky top-0 z-50 flex items-center justify-between shadow-md">
        <span class="text-sm font-bold">Document prêt à l'impression</span>
        <div class="flex gap-2">
            <button onclick="window.close()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-lg text-xs font-bold transition-all">Fermer</button>
            <button onclick="window.print()" class="px-5 py-2 bg-[#5A67D8] hover:bg-[#434190] rounded-lg text-xs font-bold transition-all">Imprimer (PDF)</button>
        </div>
    </div>

    <!-- Generate documents for all mapped students -->
    <template x-if="students.length === 0">
        <div class="text-center py-20">
            <p class="text-slate-400 italic">Aucune donnée trouvée pour générer le document.</p>
        </div>
    </template>

    @foreach($students as $index => $item)
        @php
            $e = $item['etudiant'];
            $i = $item['inscription'];
        @endphp
        
        <div class="page-container page-break relative flex flex-col justify-between">
            <div>
                <!-- Etablissement Header -->
                <div class="flex items-center justify-between border-b-2 border-[#5A67D8] pb-6 mb-8">
                    <div>
                        <h2 class="text-2xl font-black text-[#5A67D8]">{{ $etablissement ? $etablissement->nom : 'TALENSYS UNIVERSITY' }}</h2>
                        <p class="text-xs text-slate-400 font-medium">République de Côte d'Ivoire • Excellence & Innovation</p>
                    </div>
                    <div class="text-right text-xs text-slate-500">
                        <div>Date : {{ now()->format('d/m/Y') }}</div>
                        <div>Année active : {{ $i && $i->annee ? $i->annee->libelle : '2025-2026' }}</div>
                    </div>
                </div>

                <!-- ── TYPE 1: BULLETIN / RELEVE ── -->
                @if($type === 'bulletin' || $type === 'releve')
                    <div class="space-y-6">
                        <div class="text-center mb-6">
                            <h1 class="text-xl font-black uppercase text-slate-800 tracking-wider">
                                {{ $type === 'bulletin' ? 'Bulletin de Notes' : 'Relevé de Notes' }}
                            </h1>
                            <span class="text-xs text-slate-400">Semestre Académique Principal</span>
                        </div>

                        <!-- Student details box -->
                        <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 border border-slate-100 rounded-xl text-xs mb-6">
                            <div>
                                <div class="text-slate-400">Nom & Prénoms :</div>
                                <div class="font-bold text-slate-800 text-sm">{{ $e->nom }} {{ $e->prenom }}</div>
                                <div class="text-slate-400 mt-2">Matricule :</div>
                                <div class="font-bold text-slate-800">{{ $e->matricule }}</div>
                            </div>
                            <div>
                                <div class="text-slate-400">Filière / Niveau :</div>
                                <div class="font-bold text-slate-800">{{ $i && $i->filiere ? $i->filiere->libelle : 'Informatique' }}</div>
                                <div class="text-slate-400 mt-2">Classe / Groupe :</div>
                                <div class="font-bold text-[#5A67D8]">{{ $i && $i->classe ? $i->classe->libelle : '—' }}</div>
                            </div>
                        </div>

                        <!-- Grades Table -->
                        <table class="w-full text-xs text-left border border-slate-200">
                            <thead>
                                <tr class="bg-slate-100 border-b border-slate-200 font-bold text-slate-700">
                                    <th class="p-3">MATIÈRE / MODULE</th>
                                    <th class="p-3 text-center">NOTE CC</th>
                                    <th class="p-3 text-center">NOTE EXAM</th>
                                    <th class="p-3 text-center">MOYENNE</th>
                                    <th class="p-3 text-center">STATUT</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr>
                                    <td class="p-3 font-bold text-slate-700">Algorithmique & Structures de données</td>
                                    <td class="p-3 text-center">14.00</td>
                                    <td class="p-3 text-center">12.50</td>
                                    <td class="p-3 text-center font-bold text-slate-800">13.00</td>
                                    <td class="p-3 text-center text-emerald-600 font-bold">Validé</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-bold text-slate-700">Base de données relationnelles (SQL)</td>
                                    <td class="p-3 text-center">12.00</td>
                                    <td class="p-3 text-center">16.00</td>
                                    <td class="p-3 text-center font-bold text-slate-800">14.80</td>
                                    <td class="p-3 text-center text-emerald-600 font-bold">Validé</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-bold text-slate-700">Anglais Technique & Communication</td>
                                    <td class="p-3 text-center">15.00</td>
                                    <td class="p-3 text-center">11.00</td>
                                    <td class="p-3 text-center font-bold text-slate-800">12.20</td>
                                    <td class="p-3 text-center text-emerald-600 font-bold">Validé</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <!-- ── TYPE 2: FICHE / ATTESTATION / REUSSITE ── -->
                @elseif($type === 'fiche' || $type === 'attestation' || $type === 'reussite')
                    <div class="space-y-8 text-sm leading-relaxed text-justify mt-12">
                        <div class="text-center mb-10">
                            <h1 class="text-2xl font-black uppercase text-slate-800 tracking-wider">
                                @if($type === 'fiche') Fiche d'Inscription @elseif($type === 'attestation') Attestation d'Inscription @else Attestation de Réussite @endif
                            </h1>
                            <span class="text-xs text-slate-400 font-mono">N° {{ mt_rand(100000, 999999) }}</span>
                        </div>

                        <p>
                            Je soussigné, Directeur Général de l'établissement, certifie par la présente que l'étudiant(e) 
                            <strong>{{ $e->nom }} {{ $e->prenom }}</strong>, né(e) le {{ $e->date_naissance }} et titulaire 
                            du matricule <strong>{{ $e->matricule }}</strong>, est régulièrement inscrit(e) en classe de 
                            <strong>{{ $i && $i->classe ? $i->classe->libelle : '—' }}</strong> au sein de notre établissement pour 
                            l'année académique {{ $i && $i->annee ? $i->annee->libelle : '2025-2026' }}.
                        </p>

                        <p>
                            En foi de quoi, la présente attestation lui est délivrée pour servir et valoir ce que de droit.
                        </p>
                    </div>

                <!-- ── TYPE 3: FACTURE / RECU ── -->
                @elseif($type === 'facture' || $type === 'recu')
                    <div class="space-y-6">
                        <div class="text-center mb-8">
                            <h1 class="text-2xl font-black uppercase text-slate-800 tracking-wider">
                                {{ $type === 'facture' ? 'Facture Proforma' : 'Reçu de Paiement' }}
                            </h1>
                            <span class="text-xs text-slate-400 font-mono">Facture N° FAC-{{ now()->format('Y') }}-{{ mt_rand(100, 999) }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-xs mb-8">
                            <div>
                                <div class="text-slate-400 font-bold">ÉMIS À :</div>
                                <div class="font-bold text-slate-800">{{ $e->nom }} {{ $e->prenom }}</div>
                                <div class="text-slate-500">{{ $e->email }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-slate-400 font-bold">RÈGLEMENT :</div>
                                <div class="font-bold text-slate-800">Espèces / Virement bancaire</div>
                            </div>
                        </div>

                        <table class="w-full text-xs text-left border border-slate-200">
                            <thead>
                                <tr class="bg-slate-100 border-b border-slate-200 font-bold text-slate-700">
                                    <th class="p-3">DÉSIGNATION DES FRAIS</th>
                                    <th class="p-3 text-right">MONTANT</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr>
                                    <td class="p-3">Frais de Scolarité Annuelle ({{ $i && $i->classe ? $i->classe->libelle : 'Classe' }})</td>
                                    <td class="p-3 text-right font-bold text-slate-800">450 000 FCFA</td>
                                </tr>
                                <tr>
                                    <td class="p-3">Frais d'Inscription administratifs</td>
                                    <td class="p-3 text-right font-bold text-slate-800">50 000 FCFA</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <!-- ── TYPE 4: CARTE ETUDIANT ── -->
                @elseif($type === 'carte')
                    <div class="flex items-center justify-center py-12">
                        <div class="badge-student-card flex flex-col justify-between">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-[10px] font-black text-[#5A67D8]">{{ $etablissement ? $etablissement->nom : 'TALENSYS' }}</h4>
                                    <span class="text-[8px] text-slate-400 block">CARTE D'ÉTUDIANT</span>
                                </div>
                                <span class="text-[8px] font-bold text-[#5A67D8] bg-[#5A67D8]/10 px-2 py-0.5 rounded-full" x-text="'ANNEE : ' + '{{ $i && $i->annee ? $i->annee->libelle : '2025-2026' }}'"></span>
                            </div>

                            <div class="flex gap-3 items-center mt-3">
                                <template x-if="'{{ $e->url_photo }}'">
                                    <img src="{{ $e->url_photo }}" class="w-16 h-16 rounded-lg object-cover border border-slate-200">
                                </template>
                                <template x-if="!'{{ $e->url_photo }}'">
                                    <div class="w-16 h-16 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg border border-slate-200">
                                        {{ substr($e->nom, 0, 1) }}
                                    </div>
                                </template>
                                
                                <div class="text-[9px] space-y-0.5">
                                    <div class="font-bold text-slate-800">{{ $e->nom }} {{ $e->prenom }}</div>
                                    <div class="text-slate-400">Matricule : <span class="font-mono font-bold text-[#5A67D8]">{{ $e->matricule }}</span></div>
                                    <div class="text-slate-400">Niveau : <span class="font-bold text-slate-700">{{ $i && $i->niveau ? $i->niveau->libelle : '—' }}</span></div>
                                </div>
                            </div>

                            <div class="text-[6px] text-slate-400 text-center border-t border-slate-100 pt-1 mt-2">
                                Cette carte est strictement personnelle et incessible.
                            </div>
                        </div>
                    </div>

                <!-- ── TYPE 5: EMPLOI DU TEMPS ── -->
                @elseif($type === 'emploi')
                    <div class="space-y-4">
                        <div class="text-center mb-6">
                            <h1 class="text-xl font-black uppercase text-slate-800 tracking-wider">Emploi du Temps Semestriel</h1>
                            <span class="text-xs text-[#5A67D8] font-bold">{{ $i && $i->classe ? $i->classe->libelle : 'Classe' }}</span>
                        </div>

                        <table class="w-full text-xs text-left border border-slate-200">
                            <thead>
                                <tr class="bg-slate-100 border-b border-slate-200 font-bold text-slate-700">
                                    <th class="p-3">HEURES</th>
                                    <th class="p-3">LUNDI</th>
                                    <th class="p-3">MARDI</th>
                                    <th class="p-3">MERCREDI</th>
                                    <th class="p-3">JEUDI</th>
                                    <th class="p-3">VENDREDI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr>
                                    <td class="p-3 font-bold text-slate-600 bg-slate-50/50">08:00 - 10:00</td>
                                    <td class="p-3 text-indigo-700 bg-indigo-50/20 font-semibold">Algorithmique (S1)</td>
                                    <td class="p-3 text-slate-400 italic">Libre</td>
                                    <td class="p-3 text-[#5A67D8] bg-[#5A67D8]/10 font-semibold">Base de données (S2)</td>
                                    <td class="p-3 text-indigo-700 bg-indigo-50/20 font-semibold">Algorithmique (S1)</td>
                                    <td class="p-3 text-slate-400 italic">Libre</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-bold text-slate-600 bg-slate-50/50">10:15 - 12:15</td>
                                    <td class="p-3 text-indigo-700 bg-indigo-50/20 font-semibold">Algorithmique (S1)</td>
                                    <td class="p-3 text-[#5A67D8] bg-[#5A67D8]/10 font-semibold">Base de données (S2)</td>
                                    <td class="p-3 text-slate-400 italic">Libre</td>
                                    <td class="p-3 text-indigo-700 bg-indigo-50/20 font-semibold">Algorithmique (S1)</td>
                                    <td class="p-3 text-emerald-700 bg-emerald-50/20 font-semibold">Anglais (S4)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Footer Stamps and signatures -->
            <div class="flex justify-between items-end border-t border-slate-100 pt-6 text-xs text-slate-500 mt-12">
                <div>
                    <div>TALENSYS SAAS Management System</div>
                    <div class="text-[10px] text-slate-400">Document certifié conforme</div>
                </div>
                <div class="text-right flex flex-col items-center">
                    <div class="mb-8 font-bold text-slate-600">Le Directeur Académique</div>
                    <div class="w-16 h-16 rounded-full border-4 border-double border-slate-200 flex items-center justify-center text-[8px] text-slate-300 font-bold uppercase select-none">
                        CACHET
                    </div>
                </div>
            </div>
        </div>
    @endforeach

</body>
</html>
