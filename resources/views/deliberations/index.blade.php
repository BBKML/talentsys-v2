<x-app-layout>
    <x-slot name="title">Délibérations - TalentSys ERP</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Délibérations</h2>
    </x-slot>

    @push('styles')
    <style>
        .av-card { background:#fff; border-radius:16px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.02); border:1px solid #F1F5F9; }
        .f-select { border:1.5px solid #E2E8F0; border-radius:10px; padding:10px 14px; font-size:13px; color:#1E293B; font-weight:500; outline:none; transition:all .2s ease; width:100%; appearance:none; background:#fff url('data:image/svg+xml;utf8,<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="%2394A3B8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>') no-repeat right 12px center; background-size:16px; }
        .f-select:focus { border-color:#5A67D8; box-shadow:0 0 0 3px rgba(90,103,216,.1); }
        .f-table-container { background:#fff; border-radius:16px; border:1px solid #F1F5F9; box-shadow:0 2px 10px rgba(0,0,0,0.02); overflow:hidden; }
        .f-table-header th { background:#F8FAFC; padding:14px 16px; font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.5px; text-align:left; border-bottom:1px solid #F1F5F9; }
        .f-table-row td { padding:14px 16px; font-size:13px; color:#1E293B; border-bottom:1px solid #F8FAFC; vertical-align:middle; }
        .f-table-row:hover td { background:#F8FAFC; }
        .f-table-row:last-child td { border-bottom:none; }
    </style>
    @endpush

    @php
    $niveauxJson  = $niveaux->map(fn($n) => ['id'=>$n->id,'libelle'=>$n->libelle,'ordre'=>$n->ordre]);
    $classesJson  = $classes->map(fn($c) => ['id'=>$c->id,'libelle'=>$c->libelle,'id_niveau'=>$c->id_niveau,'id_filiere'=>$c->id_filiere]);
    $matieresJson = $matieres->map(fn($m) => ['id'=>$m->id,'libelle'=>$m->libelle,'id_ue'=>$m->id_ue,'id_niveau'=>$m->id_niveau,'id_filiere'=>$m->id_filiere,'coefficient'=>$m->coefficient,'id_decoupage_annee'=>$m->id_decoupage_annee]);
    $uesJson      = $ues->map(fn($u) => ['id'=>$u->id,'libelle'=>$u->libelle,'type_ue'=>$u->type_ue,'credit'=>$u->credit,'code'=>$u->code??null]);
    $filieresJson = $filieres->map(fn($f) => ['id'=>$f->id,'libelle'=>$f->libelle]);
    $decoupagesJson = $decoupages->map(fn($d) => ['id'=>$d->id,'libelle'=>$d->libelle]);
    $anneesScolairesJson = $anneesScolaires->map(fn($a) => ['id'=>$a->id,'libelle'=>$a->libelle,'active'=>(bool)$a->active,'date_debut'=>$a->date_debut]);
    $inscriptionsJson = $inscriptions->map(fn($i) => [
        'id'=>$i->id,'id_etudiant'=>$i->id_etudiant,'etu_nom'=>$i->etudiant?->nom??'?','etu_prenom'=>$i->etudiant?->prenom??'',
        'etu_matricule'=>$i->etudiant?->matricule??'','id_classe'=>$i->id_classe,'id_niveau'=>$i->id_niveau,'id_filiere'=>$i->id_filiere,'id_annee_scolaire'=>$i->id_annee_scolaire,
    ]);
    $toutesInscriptionsJson = $toutesInscriptions->map(fn($i) => ['id'=>$i->id,'id_etudiant'=>$i->id_etudiant,'id_annee_scolaire'=>$i->id_annee_scolaire]);
    $notesJson = $notes->map(fn($n) => ['id_inscription'=>$n->id_inscription,'id_matiere'=>$n->id_matiere,'id_type_note'=>$n->id_type_note,'note'=>$n->note]);
    $moyennesSavedJson = $moyennesSaved->map(fn($m) => ['id_inscription'=>$m->id_inscription,'id_matiere'=>$m->id_matiere,'moyenne'=>$m->moyenne]);
    $creditsEtudiantJson = $creditsEtudiant->map(fn($c) => ['id_inscription'=>$c->id_inscription,'id_ue'=>$c->id_ue,'credits_obtenus'=>$c->credits_obtenus,'valide'=>(bool)$c->valide]);
    $deliberationsJson = $deliberations->map(fn($d) => ['id'=>$d->id,'id_inscription'=>$d->id_inscription,'decision'=>$d->decision,'moyenne'=>$d->moyenne,'mention'=>$d->mention]);
    $hasActiveYear = (bool) $anneeActive;
    $activeAnneeId = $anneeActive?->id;
    @endphp

    <div x-data="deliberationsPage({{ $niveauxJson }}, {{ $classesJson }}, {{ $matieresJson }}, {{ $uesJson }}, {{ $inscriptionsJson }}, {{ $notesJson }}, {{ $moyennesSavedJson }}, {{ $creditsEtudiantJson }}, {{ $hasActiveYear ? 'true' : 'false' }}, {{ $isBTS ? 'true' : 'false' }}, {{ $deliberationsJson }}, {{ $filieresJson }}, {{ $decoupagesJson }}, {{ $anneesScolairesJson }}, {{ $toutesInscriptionsJson }}, {{ $activeAnneeId ?? 'null' }})" class="space-y-4">
        
        {{-- En-tête --}}
        <div class="flex items-center flex-wrap gap-3 justify-between">
            <div>
                <h1 class="text-xl font-bold" style="color:#1E293B">Délibérations</h1>
                <p class="text-sm mt-0.5" style="color:#94A3B8">Résultats et décisions finales</p>
            </div>
            
            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                    <select class="f-select" style="min-width:210px; background-color:#F8FAFC;" x-model="delibClasse" @change="delibClasseChange()">
                        <option value="">Sélectionner une classe…</option>
                        <template x-for="c in classes" :key="c.id"><option :value="c.id" x-text="c.libelle"></option></template>
                    </select>
                    <span x-show="delibCalculating" class="flex items-center gap-1 text-xs" style="color:#5A67D8">
                        <span style="width:14px;height:14px;border:2px solid #5A67D8;border-top-color:transparent;border-radius:50%;display:inline-block;animation:spin .7s linear infinite"></span>
                        Calcul…
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="delibRefresh()" :disabled="!delibClasse" class="flex items-center gap-1 px-3 py-2.5 rounded-lg text-sm font-semibold border border-gray-200 hover:bg-gray-50 disabled:opacity-40" style="color:#64748B">
                        <i class="ri-refresh-line"></i>
                    </button>
                    <button @click="openPromoClasse()" :disabled="!hasActiveYear||!delibClasse||delibPromoting" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-50" style="background:#16a34a">
                        <span x-show="delibPromoting" style="width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;display:inline-block;animation:spin .7s linear infinite"></span>
                        <i class="ri-arrow-up-circle-line" x-show="!delibPromoting"></i>
                        <span x-text="delibPromoting?'Inscription…':'Inscrire année suivante'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Chips stats --}}
        <template x-if="delibClasse && delibRows().length">
            <div class="flex gap-3 flex-wrap">
                <template x-for="chip in delibChips()" :key="chip.label">
                    <div class="flex items-center gap-2 px-4 py-3 rounded-xl flex-1 min-w-[150px]" :style="'background:'+chip.bg+';border:1px solid '+chip.border">
                        <i :class="chip.icon" :style="'color:'+chip.color"></i>
                        <div>
                            <p class="text-[11px] font-bold" :style="'color:'+chip.color+'cc'" x-text="chip.label"></p>
                            <p class="text-lg font-bold leading-none" :style="'color:'+chip.color" x-text="chip.value"></p>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Tableau --}}
        <div x-show="!delibClasse || !delibRows().length" class="av-card text-center py-16">
            <i class="ri-award-line" style="font-size:64px;color:#CBD5E1"></i>
            <p class="mt-4 text-sm" style="color:#94A3B8" x-text="!delibClasse ? 'Sélectionnez une classe pour commencer' : 'Aucun étudiant inscrit dans cette classe'"></p>
        </div>

        <div x-show="delibClasse && delibRows().length" class="f-table-container">
            <div class="p-3 border-b border-gray-100 flex justify-between items-center">
                <div class="relative w-full max-w-[320px]">
                    <i class="ri-search-line absolute left-3 top-2.5 text-gray-400"></i>
                    <input type="text" x-model="delibSearch" placeholder="Rechercher un étudiant…" class="f-select pl-9" style="padding:8px 12px 8px 36px; background-color:#F8FAFC">
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500">Lignes/page :</span>
                    <select class="f-select py-1 px-2 pr-8 text-xs" style="width:70px">
                        <option>10</option>
                        <option>20</option>
                        <option>50</option>
                    </select>
                </div>
            </div>
            <div style="overflow-x:auto">
                <table class="w-full" style="min-width:900px">
                    <thead class="f-table-header">
                        <tr>
                            <th>MATRICULE</th>
                            <th>ÉTUDIANT</th>
                            <th>NOTES (mat.)</th>
                            <th>MOYENNE</th>
                            <th>MENTION</th>
                            <th>ACTIONS</th>
                            <th>DÉCISION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in delibRowsFiltered()" :key="row.idIns">
                            <tr class="f-table-row">
                                {{-- Matricule --}}
                                <td><span class="font-bold" style="color:#5A67D8;font-size:12px" x-text="row.matricule||'—'"></span></td>

                                {{-- Étudiant --}}
                                <td class="font-bold text-sm" x-text="row.nom"></td>

                                {{-- Notes --}}
                                <td class="text-xs" style="color:#94A3B8" x-text="row.nbMat+' matière(s)'"></td>

                                {{-- Moyenne --}}
                                <td>
                                    <template x-if="row.moy > 0">
                                        <span class="inline-flex px-2 py-1 rounded-md" :style="row.moy>=10?'background:rgba(22,163,74,.1);color:#16a34a':'background:rgba(239,68,68,.1);color:#ef4444'" style="font-size:13px;font-weight:700" x-text="row.moy.toFixed(2)"></span>
                                    </template>
                                    <span x-show="row.moy === 0" style="color:#94A3B8">—</span>
                                </td>

                                {{-- Mention --}}
                                <td style="color:#94A3B8;font-size:12px" x-text="row.mention"></td>

                                {{-- Actions --}}
                                <td>
                                    <div class="flex items-center gap-1">
                                        {{-- Relevé --}}
                                        <button @click="toast('Génération PDF non disponible en web — utilisez l\'export','info')" title="Relevé de notes" class="w-[28px] h-[28px] rounded-md inline-flex items-center justify-center border-none" style="background:rgba(90,103,216,.08);color:#5A67D8">
                                            <i class="ri-printer-line text-[14px]"></i>
                                        </button>
                                        {{-- Voir UEs (LMD seulement) --}}
                                        <button x-show="!isBTS" @click="openUEDetails(row.idIns)" title="Voir UEs validées" class="w-[28px] h-[28px] rounded-md inline-flex items-center justify-center border-none" style="background:rgba(13,148,136,.08);color:#0d9488">
                                            <i class="ri-list-check-2 text-[14px]"></i>
                                        </button>
                                        {{-- Avancer / Répecher / Validé --}}
                                        <template x-if="row.dejaInscrit">
                                            <span class="flex items-center gap-1 px-2 py-1 rounded-md ml-1" style="background:rgba(22,163,74,.1);color:#16a34a;font-size:11px;font-weight:700">
                                                <i class="ri-checkbox-circle-line"></i> Validé
                                            </span>
                                        </template>
                                        <template x-if="!row.dejaInscrit && hasActiveYear">
                                            <button @click="row.eligible ? openPromoEtu(row.idIns) : openForcePassage(row.idIns)" :title="row.eligible ? 'Avancer au niveau suivant' : 'Forcer le passage (décision jury)'"
                                                    class="flex items-center gap-1 px-2 py-1 rounded-md border-none ml-1 cursor-pointer transition-opacity hover:opacity-80" style="font-size:11px;font-weight:700;"
                                                    :style="row.eligible ? 'background:rgba(22,163,74,.1);color:#16a34a' : 'background:rgba(249,115,22,.1);color:#ea580c'">
                                                <i :class="row.eligible ? 'ri-arrow-up-line' : 'ri-alert-line'"></i>
                                                <span x-text="row.eligible ? 'Avancer' : 'Répecher'"></span>
                                            </button>
                                        </template>
                                    </div>
                                </td>

                                {{-- Décision --}}
                                <td>
                                    {{-- Décision sauvegardée --}}
                                    <template x-if="row.decision">
                                        <div class="flex items-center gap-1">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border border-transparent" :style="decisionStyle(row.decision)" x-text="row.decision.length>15 ? row.decision.substring(0,13)+'…' : row.decision"></span>
                                            <button x-show="hasActiveYear" @click="openDecisionMenu(row.idIns)" class="w-6 h-6 rounded flex items-center justify-center hover:bg-gray-100 border-none bg-transparent cursor-pointer">
                                                <i class="ri-pencil-line" style="font-size:13px;color:#94A3B8"></i>
                                            </button>
                                        </div>
                                    </template>
                                    {{-- Tout validé → Valider ADMIS --}}
                                    <template x-if="!row.decision && row.toutValide">
                                        <button @click="hasActiveYear && saveDecision(row.idIns)" :disabled="!hasActiveYear"
                                                class="flex items-center gap-1 px-2.5 py-1 rounded-full border border-green-300 bg-green-50 text-green-600 cursor-pointer" style="font-size:10px;font-weight:700;">
                                            <i class="ri-checkbox-circle-line text-[11px]"></i> Valider ADMIS
                                        </button>
                                    </template>
                                    {{-- Pas de décision --}}
                                    <template x-if="!row.decision && !row.toutValide">
                                        <button @click="hasActiveYear && openDecisionMenu(row.idIns)" :disabled="!hasActiveYear"
                                                class="flex items-center gap-1 px-2.5 py-1 rounded-full border border-gray-300 bg-gray-50 text-gray-400 cursor-pointer" style="font-size:10px;font-weight:700;">
                                            <i class="ri-gavel-line text-[11px]"></i> <span x-text="hasActiveYear?'Décider':'Non décidé'"></span>
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination Footer (Factice visuel pour correspondre à la maquette) --}}
            <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-500"><span x-text="delibRowsFiltered().length ? '1-'+delibRowsFiltered().length : '0'"></span> sur <span x-text="delibRowsFiltered().length"></span> résultat(s)</p>
                <div class="flex gap-1">
                    <button class="w-7 h-7 flex items-center justify-center rounded-md border border-gray-200 text-gray-400 bg-white"><i class="ri-arrow-left-s-line"></i></button>
                    <button class="w-7 h-7 flex items-center justify-center rounded-md border border-red-800 text-white font-medium text-xs bg-red-800">1</button>
                    <button class="w-7 h-7 flex items-center justify-center rounded-md border border-gray-200 text-gray-400 bg-white"><i class="ri-arrow-right-s-line"></i></button>
                </div>
            </div>
        </div>

        {{-- ── Modals (Décision, UEs, Avancer, Forcer, Inscrire Classe) ──────────────────────────────────── --}}

        {{-- Modal Décision Jury --}}
        <template x-if="decisionMenuIns !== null">
            <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.5)" @click.self="decisionMenuIns=null">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                        <i class="ri-gavel-line" style="color:#5A67D8;font-size:18px"></i>
                        <h2 class="font-bold" style="color:#1E293B">Décision du jury</h2>
                        <button @click="decisionMenuIns=null" class="ml-auto w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100 border-none bg-transparent cursor-pointer"><i class="ri-close-line" style="color:#64748B"></i></button>
                    </div>
                    <div class="px-4 py-3 space-y-1">
                        <template x-for="opt in decisionOptions" :key="opt.value">
                            <button @click="saveDecision(decisionMenuIns, opt.value); decisionMenuIns=null"
                                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-50 border-none bg-transparent cursor-pointer text-left transition-colors">
                                <i :class="opt.icon" :style="'color:'+opt.color+';font-size:18px'"></i>
                                <span class="font-semibold text-sm" :style="'color:'+opt.color" x-text="opt.value"></span>
                            </button>
                        </template>
                    </div>
                    <div class="px-6 py-3 border-t border-gray-100 text-right">
                        <button @click="decisionMenuIns=null" class="text-sm font-semibold border-none bg-transparent cursor-pointer text-gray-500 hover:text-gray-700 transition-colors">Annuler</button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Modal Voir UEs (LMD) --}}
        <template x-if="ueDetailModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.5)" @click.self="ueDetailModal=null">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col" style="max-height:85vh">
                    <div style="background:#5A67D8;border-radius:16px 16px 0 0" class="px-6 py-4 flex items-center gap-3">
                        <i class="ri-bank-line text-white text-lg"></i>
                        <div class="flex-1">
                            <p class="text-white font-bold" x-text="ueDetailModal.nom"></p>
                            <p class="text-xs text-white/75" x-text="'Validation des UEs — '+ueDetailModal.matricule"></p>
                        </div>
                        <button @click="ueDetailModal=null" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-white/10 border-none bg-transparent cursor-pointer"><i class="ri-close-line text-white text-lg"></i></button>
                    </div>
                    <div class="overflow-y-auto p-4 space-y-3" style="background:#F8FAFC">
                        <template x-for="sem in ueDetailModal.semestres" :key="sem.label">
                            <div>
                                <div class="flex items-center gap-2 px-3 py-2 rounded-lg mb-2" style="background:rgba(90,103,216,.07)">
                                    <i class="ri-calendar-2-line" style="color:#5A67D8;font-size:14px"></i>
                                    <span class="font-bold text-sm" style="color:#5A67D8" x-text="sem.label"></span>
                                    <span class="ml-auto text-[11px]" style="color:#94A3B8" x-text="sem.validees+'/'+sem.total+' UEs  •  '+sem.credObt+'/'+sem.credTotal+' crédits'"></span>
                                </div>
                                <template x-for="ue in sem.ues" :key="ue.ueId">
                                    <div class="flex items-center gap-3 p-3 rounded-lg mb-1"
                                         :style="'background:'+(ue.pending?'rgba(148,163,184,.05)':(ue.valide?'rgba(22,163,74,.06)':'rgba(239,68,68,.05)'))+';border:1px solid '+(ue.pending?'#E2E8F0':(ue.valide?'#bbf7d0':(ue.isFond?'#f87171':'#fca5a5')))">
                                        <i :class="ue.pending?'ri-time-line':(ue.valide?'ri-checkbox-circle-line':'ri-close-circle-line')"
                                           :style="'color:'+(ue.pending?'#94A3B8':(ue.valide?'#16a34a':'#ef4444'))"></i>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-sm" style="color:#1E293B" x-text="ue.libelle"></span>
                                                <span x-show="ue.typeUe" class="px-1.5 py-0.5 rounded text-[10px] font-bold"
                                                      :style="ue.isFond?'background:#EEF2FF;color:#4F46E5':ue.typeUe==='Transversale'?'background:#F0FDFA;color:#0D9488':'background:#FAF5FF;color:#7C3AED'" x-text="ue.typeUe"></span>
                                            </div>
                                            <p x-show="ue.code" class="text-[11px] font-mono" style="color:#94A3B8" x-text="ue.code"></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs font-bold" :style="'color:'+(ue.pending?'#94A3B8':(ue.valide?'#16a34a':'#ef4444'))" x-text="(ue.pending?'—/':ue.credObt+'/')+ue.credTotal+' cr.'"></p>
                                            <p class="text-[10px]" :style="'color:'+(ue.pending?'#94A3B8':(ue.valide?'#16a34a':(ue.isFond?'#ef4444':'#f97316')))" x-text="ue.pending?'Non généré':(ue.valide?'Validée':(ue.isFond?'NON VALIDÉE ⚠':'Non validée'))"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <p x-show="!ueDetailModal.semestres.length" class="text-center py-8 text-sm" style="color:#94A3B8">Aucune UE configurée pour ce niveau/filière.</p>
                    </div>
                    <div class="px-6 py-3 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-1 text-xs text-green-600"><span class="w-2.5 h-2.5 rounded-full bg-green-600 inline-block"></span>Validée</span>
                            <span class="flex items-center gap-1 text-xs text-red-500"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span>Non validée</span>
                            <span class="flex items-center gap-1 text-xs text-slate-400"><span class="w-2.5 h-2.5 rounded-full bg-slate-400 inline-block"></span>Non généré</span>
                        </div>
                        <button @click="ueDetailModal=null" class="text-sm font-semibold text-slate-500 border-none bg-transparent cursor-pointer hover:text-slate-700 transition-colors">Fermer</button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Modal Avancer Étudiant --}}
        <template x-if="promoEtuModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.5)" @click.self="promoEtuModal=null">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <h2 class="font-bold" style="color:#1E293B" x-text="'Avancer '+promoEtuForm.nomEtu"></h2>
                        <button @click="promoEtuModal=null" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100 border-none bg-transparent cursor-pointer"><i class="ri-close-line" style="color:#64748B"></i></button>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div class="flex items-center gap-2 p-3 rounded-lg bg-green-50 border border-green-200">
                            <i class="ri-user-line text-green-600"></i>
                            <span class="text-sm text-green-700" x-text="promoEtuForm.nomEtu+' sera réinscrit(e) au niveau suivant.'"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="f-label block text-xs font-semibold text-slate-600 mb-1.5">Année cible <span class="text-red-500">*</span></label>
                                <select class="f-select" x-model="promoEtuForm.id_annee_scolaire">
                                    <template x-for="a in anneesScolaires" :key="a.id"><option :value="a.id" x-text="a.libelle"></option></template>
                                </select>
                            </div>
                            <div>
                                <label class="f-label block text-xs font-semibold text-slate-600 mb-1.5">Filière <span class="text-red-500">*</span></label>
                                <select class="f-select" x-model="promoEtuForm.id_filiere">
                                    <template x-for="f in filieres" :key="f.id"><option :value="f.id" x-text="f.libelle"></option></template>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="f-label block text-xs font-semibold text-slate-600 mb-1.5">Niveau cible <span class="text-red-500">*</span></label>
                                <select class="f-select" x-model="promoEtuForm.id_niveau">
                                    <template x-for="n in niveaux" :key="n.id"><option :value="n.id" x-text="n.libelle"></option></template>
                                </select>
                            </div>
                            <div>
                                <label class="f-label block text-xs font-semibold text-slate-600 mb-1.5">Classe cible <span class="text-red-500">*</span></label>
                                <select class="f-select" x-model="promoEtuForm.id_classe">
                                    <option value="">Choisir…</option>
                                    <template x-for="c in classesPourNiveau(promoEtuForm.id_niveau, promoEtuForm.id_filiere)" :key="c.id"><option :value="c.id" x-text="c.libelle"></option></template>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 mt-2">
                            <button @click="promoEtuModal=null" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50 text-slate-500">Annuler</button>
                            <button @click="submitPromoEtu()" :disabled="promoEtuSubmitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold bg-green-600 hover:bg-green-700 disabled:opacity-60 transition-colors" x-text="promoEtuSubmitting?'Inscription…':'Inscrire'"></button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Modal Forcer Passage (Jury) --}}
        <template x-if="forceModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.5)" @click.self="forceModal=null">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <h2 class="font-bold text-slate-800" x-text="'Forcer le passage — '+forceForm.nomEtu"></h2>
                        <button @click="forceModal=null" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100 border-none bg-transparent cursor-pointer"><i class="ri-close-line text-slate-500"></i></button>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div class="flex items-center gap-2 p-3 rounded-lg bg-orange-50 border border-orange-200">
                            <i class="ri-gavel-line text-orange-600"></i>
                            <span class="text-sm font-semibold text-orange-700" x-text="'Passage forcé par décision jury pour '+forceForm.nomEtu+'.'" ></span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="f-label block text-xs font-semibold text-slate-600 mb-1.5">Année cible <span class="text-red-500">*</span></label>
                                <select class="f-select" x-model="forceForm.id_annee_scolaire">
                                    <template x-for="a in anneesScolaires" :key="a.id"><option :value="a.id" x-text="a.libelle"></option></template>
                                </select>
                            </div>
                            <div>
                                <label class="f-label block text-xs font-semibold text-slate-600 mb-1.5">Filière <span class="text-red-500">*</span></label>
                                <select class="f-select" x-model="forceForm.id_filiere">
                                    <template x-for="f in filieres" :key="f.id"><option :value="f.id" x-text="f.libelle"></option></template>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="f-label block text-xs font-semibold text-slate-600 mb-1.5">Niveau cible <span class="text-red-500">*</span></label>
                                <select class="f-select" x-model="forceForm.id_niveau">
                                    <template x-for="n in niveaux" :key="n.id"><option :value="n.id" x-text="n.libelle"></option></template>
                                </select>
                            </div>
                            <div>
                                <label class="f-label block text-xs font-semibold text-slate-600 mb-1.5">Classe cible <span class="text-red-500">*</span></label>
                                <select class="f-select" x-model="forceForm.id_classe">
                                    <option value="">Choisir…</option>
                                    <template x-for="c in classesPourNiveau(forceForm.id_niveau, forceForm.id_filiere)" :key="c.id"><option :value="c.id" x-text="c.libelle"></option></template>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 mt-2">
                            <button @click="forceModal=null" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50 text-slate-500">Annuler</button>
                            <button @click="submitForcePassage()" :disabled="forceSubmitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold bg-orange-600 hover:bg-orange-700 disabled:opacity-60 transition-colors" x-text="forceSubmitting?'Inscription…':'Confirmer décision'"></button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Modal Inscrire Classe Entière --}}
        <template x-if="promoClasseModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.5)" @click.self="promoClasseModal=null">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <h2 class="font-bold text-slate-800">Inscrire tous les étudiants — Année suivante</h2>
                        <button @click="promoClasseModal=null" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100 border-none bg-transparent cursor-pointer"><i class="ri-close-line text-slate-500"></i></button>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div class="p-4 rounded-xl space-y-2 bg-blue-50 border border-blue-200">
                            <p class="text-sm font-bold text-blue-700">Résumé de la délibération</p>
                            <div class="flex items-center gap-2 text-xs text-green-700"><i class="ri-checkbox-circle-line"></i><span x-text="promoClasseResume().admis+' Admis → niveau suivant'"></span></div>
                            <div class="flex items-center gap-2 text-xs text-orange-600"><i class="ri-time-line"></i><span x-text="promoClasseResume().ajournes+' Ajourné(s) / Redoublant(s) → même niveau'"></span></div>
                            <div class="flex items-center gap-2 text-xs text-red-600"><i class="ri-close-circle-line"></i><span x-text="promoClasseResume().exclus+' Définitivement ajourné(s) — exclus'"></span></div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="f-label block text-xs font-semibold text-slate-600 mb-1.5">Année cible <span class="text-red-500">*</span></label>
                                <select class="f-select" x-model="promoClasseForm.id_annee_scolaire">
                                    <template x-for="a in anneesScolaires" :key="a.id"><option :value="a.id" x-text="a.libelle"></option></template>
                                </select>
                            </div>
                            <div>
                                <label class="f-label block text-xs font-semibold text-slate-600 mb-1.5">Filière <span class="text-red-500">*</span></label>
                                <select class="f-select" x-model="promoClasseForm.id_filiere">
                                    <template x-for="f in filieres" :key="f.id"><option :value="f.id" x-text="f.libelle"></option></template>
                                </select>
                            </div>
                        </div>
                        <div class="p-3 rounded-lg border border-slate-200 bg-slate-50">
                            <p class="text-xs font-bold mb-2 text-green-600">Admis (<span x-text="promoClasseResume().admis"></span>)</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="f-label block text-[11px] font-semibold text-slate-600 mb-1">Niveau cible <span class="text-red-500">*</span></label>
                                    <select class="f-select text-xs py-1.5" x-model="promoClasseForm.id_niveau_admis">
                                        <template x-for="n in niveaux" :key="n.id"><option :value="n.id" x-text="n.libelle"></option></template>
                                    </select>
                                </div>
                                <div>
                                    <label class="f-label block text-[11px] font-semibold text-slate-600 mb-1">Classe cible <span class="text-red-500">*</span></label>
                                    <select class="f-select text-xs py-1.5" x-model="promoClasseForm.id_classe_admis">
                                        <option value="">Choisir…</option>
                                        <template x-for="c in classesPourNiveau(promoClasseForm.id_niveau_admis, promoClasseForm.id_filiere)" :key="c.id"><option :value="c.id" x-text="c.libelle"></option></template>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 rounded-lg border border-orange-200 bg-orange-50">
                            <p class="text-xs font-bold mb-2 text-orange-600">Ajournés / Redoublants (<span x-text="promoClasseResume().ajournes"></span>)</p>
                            <div>
                                <label class="f-label block text-[11px] font-semibold text-slate-600 mb-1">Classe (redoublants)</label>
                                <select class="f-select text-xs py-1.5" x-model="promoClasseForm.id_classe_redoublants">
                                    <option value="">Même classe actuelle</option>
                                    <template x-for="c in classes" :key="c.id"><option :value="c.id" x-text="c.libelle"></option></template>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 mt-2">
                            <button @click="promoClasseModal=null" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50 text-slate-500">Annuler</button>
                            <button @click="submitPromoClasse()" :disabled="promoClasseSubmitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold bg-green-600 hover:bg-green-700 disabled:opacity-60 transition-colors" x-text="promoClasseSubmitting?'Inscription…':'Inscrire la classe'"></button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <style>@keyframes spin{to{transform:rotate(360deg)}}</style>

    @push('scripts')
    <script>
    function deliberationsPage(niveauxData, classesData, matieresData, uesData, inscriptionsData, notesData, moyennesSavedData, creditsData, hasActiveYear, isBTS, deliberationsData, filieresData, decoupagesData, anneesScolairesData, toutesInsData, activeAnneeId) {
        return {
            niveaux: niveauxData||[], classes: classesData||[], matieres: matieresData||[], ues: uesData||[],
            inscriptions: inscriptionsData||[], notes: notesData||[], moyennesSaved: moyennesSavedData||[], credits: creditsData||[],
            hasActiveYear, isBTS,
            deliberations: deliberationsData || [], filieres: filieresData || [], decoupages: decoupagesData || [],
            anneesScolaires: anneesScolairesData || [], toutesIns: toutesInsData || [], activeAnneeId,

            delibClasse:'', delibSearch:'', delibCalculating:false, delibPromoting:false,
            decisionMenuIns:null, ueDetailModal:null,
            promoEtuModal:null, promoEtuSubmitting:false,
            promoEtuForm:{idIns:'',nomEtu:'',id_annee_scolaire:'',id_filiere:'',id_niveau:'',id_classe:''},
            forceModal:null, forceSubmitting:false,
            forceForm:{idIns:'',nomEtu:'',id_annee_scolaire:'',id_filiere:'',id_niveau:'',id_classe:''},
            promoClasseModal:null, promoClasseSubmitting:false,
            promoClasseForm:{id_annee_scolaire:'',id_filiere:'',id_niveau_admis:'',id_classe_admis:'',id_classe_redoublants:''},
            decisionOptions:[
                {value:'ADMIS',              icon:'ri-checkbox-circle-line', color:'#16a34a'},
                {value:'AJOURNÉ',           icon:'ri-time-line',            color:'#f97316'},
                {value:'DÉFINITIVEMENT AJOURNÉ', icon:'ri-close-circle-line', color:'#ef4444'},
                {value:'INCOMPLET',          icon:'ri-git-branch-line',      color:'#5A67D8'},
            ],

            calcMoyenne(idIns, idMat){
                const notesM = this.notes.filter(n=>n.id_inscription===idIns && n.id_matiere===idMat);
                if(!notesM.length) return -1;
                const byType = {};
                for(const n of notesM){ const t=n.id_type_note||0; (byType[t] ||= []).push(Number(n.note)||0); }
                let sumPoids=0, sumNote=0;
                for(const key of Object.keys(byType)){
                    const vals=byType[key], avg=vals.reduce((s,v)=>s+v,0)/vals.length;
                    if(Number(key)===0){ sumPoids+=1; sumNote+=avg; }
                    else { sumPoids+=1; sumNote+=avg; } // Simplifié ici car TypesNote pas chargé, mais les moyennes sont prioritaires
                }
                return sumPoids===0?0:sumNote/sumPoids;
            },

            delibInsForClasse(){
                return this.inscriptions.filter(i => String(i.id_classe)===String(this.delibClasse));
            },

            delibMoyGeneral(idIns){
                const ins = this.inscriptions.find(i => i.id===idIns);
                if(!ins) return 0;
                const mats = this.matieres.filter(m => String(m.id_filiere)===String(ins.id_filiere) && String(m.id_niveau)===String(ins.id_niveau));
                if(!mats.length) return 0;
                let sumP=0, sumN=0;
                for(const mat of mats){
                    const saved = this.moyennesSaved.find(m => m.id_inscription===idIns && m.id_matiere===mat.id);
                    if(saved){
                        const coef = Number(mat.coefficient)||1;
                        sumP += coef; sumN += Number(saved.moyenne)*coef;
                    } else {
                        const moy = this.calcMoyenne(idIns, mat.id);
                        if(moy < 0) continue;
                        const coef = Number(mat.coefficient)||1;
                        sumP += coef; sumN += moy*coef;
                    }
                }
                return sumP > 0 ? sumN/sumP : 0;
            },

            delibNbMatieres(idIns){
                const ns = this.notes.filter(n => n.id_inscription===idIns);
                return new Set(ns.map(n => n.id_matiere)).size;
            },

            delibEligible(idIns){
                const ins = this.inscriptions.find(i => i.id===idIns);
                if(!ins) return false;
                if(this.isBTS){
                    return this.delibMoyGeneral(idIns) >= 10;
                } else {
                    const ueIds = [...new Set(this.matieres.filter(m => String(m.id_filiere)===String(ins.id_filiere) && String(m.id_niveau)===String(ins.id_niveau) && m.id_ue).map(m => m.id_ue))];
                    const credits = this.credits.filter(c => c.id_inscription===idIns);
                    const fondUes = ueIds.filter(ueId => this.ues.find(u => u.id===ueId)?.type_ue === 'Fondamentale');
                    if(fondUes.length === 0) return credits.length > 0 && credits.every(c => c.valide);
                    return fondUes.every(ueId => credits.find(c => c.id_ue===ueId)?.valide);
                }
            },

            delibToutValide(idIns){
                const ins = this.inscriptions.find(i => i.id===idIns);
                if(!ins) return false;
                const ueIds = [...new Set(this.matieres.filter(m => String(m.id_filiere)===String(ins.id_filiere) && String(m.id_niveau)===String(ins.id_niveau) && m.id_ue).map(m => m.id_ue))];
                if(ueIds.length === 0) return false;
                const credits = this.credits.filter(c => c.id_inscription===idIns);
                return ueIds.every(ueId => credits.find(c => c.id_ue===ueId)?.valide);
            },

            delibDejaInscrit(idEtu){
                if(!this.activeAnneeId) return false;
                return this.toutesIns.some(i => i.id_etudiant===idEtu && i.id_annee_scolaire!==this.activeAnneeId);
            },

            delibRows(){
                const ins = this.delibInsForClasse();
                return ins.map(i => {
                    const moy = this.delibMoyGeneral(i.id);
                    const mention = moy >= 16?'Très Bien':moy >= 14?'Bien':moy >= 12?'Assez Bien':moy >= 10?'Passable':moy > 0?'Insuffisant':'—';
                    const delib = this.deliberations.find(d => d.id_inscription===i.id);
                    return {
                        idIns: i.id, idEtu: i.id_etudiant,
                        nom: i.etu_nom+' '+i.etu_prenom, matricule: i.etu_matricule,
                        nbMat: this.delibNbMatieres(i.id), moy, mention,
                        eligible: this.delibEligible(i.id),
                        toutValide: this.delibToutValide(i.id),
                        dejaInscrit: this.delibDejaInscrit(i.id_etudiant),
                        decision: delib ? delib.decision : null,
                        idFiliere: i.id_filiere, idNiveau: i.id_niveau,
                    };
                });
            },

            delibRowsFiltered(){
                const rows = this.delibRows();
                if(!this.delibSearch) return rows;
                const q = this.delibSearch.toLowerCase();
                return rows.filter(r => r.nom.toLowerCase().includes(q) || (r.matricule&&r.matricule.toLowerCase().includes(q)));
            },

            delibChips(){
                const rows = this.delibRows();
                const total = rows.length;
                const avecNotes = rows.filter(r => r.nbMat>0).length;
                const avecMoy = rows.filter(r => r.moy>0).length;
                const admis = rows.filter(r => r.decision==='ADMIS'||(r.eligible&&!r.decision&&r.toutValide)).length;
                const ajournes = rows.filter(r => r.decision&&r.decision!=='ADMIS').length;
                return [
                    {label:'Inscrits',     value:total,    icon:'ri-group-fill',      color:'#5A67D8', bg:'rgba(90,103,216,.06)',  border:'rgba(90,103,216,.15)'},
                    {label:'Notes saisies',value:avecNotes,icon:'ri-file-list-3-fill', color:'#3b82f6', bg:'rgba(59,130,246,.06)', border:'rgba(59,130,246,.15)'},
                    {label:'Moy. calculées',value:avecMoy, icon:'ri-functions',        color:'#f97316', bg:'rgba(249,115,22,.06)', border:'rgba(249,115,22,.15)'},
                    {label:'Admis',        value:admis,    icon:'ri-checkbox-circle-fill',color:'#16a34a',bg:'rgba(22,163,74,.06)',  border:'rgba(22,163,74,.15)'},
                    {label:'Ajournés',    value:ajournes, icon:'ri-close-circle-fill', color:'#ef4444', bg:'rgba(239,68,68,.06)',  border:'rgba(239,68,68,.15)'},
                ];
            },

            delibClasseChange(){ this.delibSearch = ''; },
            delibRefresh(){ location.reload(); },

            decisionStyle(dec){
                if(dec==='ADMIS') return 'background:rgba(22,163,74,.1);color:#16a34a';
                if(dec==='AJOURNÉ') return 'background:rgba(249,115,22,.1);color:#ea580c';
                if(dec==='DÉFINITIVEMENT AJOURNÉ') return 'background:rgba(239,68,68,.1);color:#dc2626';
                if(dec==='INCOMPLET') return 'background:rgba(90,103,216,.1);color:#5A67D8';
                return 'background:#F1F5F9;color:#94A3B8';
            },

            openDecisionMenu(idIns){ this.decisionMenuIns = idIns; },

            saveDecision(idIns, decision='ADMIS'){
                const row = this.delibRows().find(r => r.idIns===idIns);
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const moy = row ? row.moy : null;
                const mention = row && row.moy>0 ? row.mention : null;
                fetch('/deliberations/decision',{
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    body:JSON.stringify({id_inscription:idIns, decision, moyenne:moy>0?moy:null, mention})
                }).then(async res => {
                    if(!res.ok){ const e=await res.json(); throw new Error(e.message||'Erreur'); }
                    const r = await res.json();
                    const existing = this.deliberations.find(d => d.id_inscription===idIns);
                    if(existing){ existing.decision=decision; existing.moyenne=moy||null; existing.mention=mention; }
                    else this.deliberations.push({id:r.data.id, id_inscription:idIns, decision, moyenne:moy||null, mention});
                    this.toast('Décision « '+decision+' » enregistrée','success');
                }).catch(err => this.toast(err.message,'error'));
            },

            openUEDetails(idIns){
                const ins = this.inscriptions.find(i => i.id===idIns);
                if(!ins) return;
                const credits = this.credits.filter(c => c.id_inscription===idIns);
                const decoupagesIns = this.decoupages;

                const mats = this.matieres.filter(m => String(m.id_filiere)===String(ins.id_filiere) && String(m.id_niveau)===String(ins.id_niveau) && m.id_ue);
                const ueIds = [...new Set(mats.map(m => m.id_ue))];

                const byDecoupage = {};
                for(const ueId of ueIds){
                    const matsUe = mats.filter(m => m.id_ue===ueId);
                    const decoupageId = matsUe[0]?.id_decoupage_annee;
                    if(!byDecoupage[decoupageId||'__none__']) byDecoupage[decoupageId||'__none__'] = [];
                    const ue = this.ues.find(u => u.id===ueId);
                    const rec = credits.find(c => c.id_ue===ueId);
                    const pending = !rec;
                    byDecoupage[decoupageId||'__none__'].push({
                        ueId, libelle: ue?.libelle||'UE inconnue', code: ue?.code||null,
                        typeUe: ue?.type_ue||null, isFond: ue?.type_ue==='Fondamentale',
                        valide: rec ? !!rec.valide : false, pending,
                        credObt: rec ? Number(rec.credits_obtenus)||0 : 0,
                        credTotal: ue ? Number(ue.credit)||0 : 0,
                    });
                }

                const semestres = [];
                for(const [decId, ues] of Object.entries(byDecoupage)){
                    const dec = this.decoupages.find(d => String(d.id)===String(decId));
                    const label = dec ? dec.libelle : 'Non assigné';
                    const validees = ues.filter(u => u.valide).length;
                    const credObt = ues.reduce((s,u)=>s+u.credObt,0);
                    const credTotal = ues.reduce((s,u)=>s+u.credTotal,0);
                    semestres.push({label, ues, total:ues.length, validees, credObt, credTotal});
                }

                this.ueDetailModal = { nom: ins.etu_nom+' '+ins.etu_prenom, matricule: ins.etu_matricule||'—', semestres };
            },

            classesPourNiveau(idNiveau, idFiliere){
                if(!idNiveau) return this.classes;
                return this.classes.filter(c => String(c.id_niveau)===String(idNiveau));
            },

            prochainAnnee(){
                const nonActives = this.anneesScolaires.filter(a => !a.active);
                if(nonActives.length) return nonActives[nonActives.length-1].id;
                return this.anneesScolaires.length ? this.anneesScolaires[this.anneesScolaires.length-1].id : '';
            },

            openPromoEtu(idIns){
                const row = this.delibRows().find(r => r.idIns===idIns);
                if(!row) return;
                const ins = this.inscriptions.find(i => i.id===idIns);
                this.promoEtuForm = {
                    idIns, nomEtu: row.nom,
                    id_annee_scolaire: this.prochainAnnee(),
                    id_filiere: ins?.id_filiere||'',
                    id_niveau: ins?.id_niveau||'',
                    id_classe: '',
                };
                this.promoEtuSubmitting = false;
                this.promoEtuModal = true;
            },

            submitPromoEtu(){
                const f = this.promoEtuForm;
                if(!f.id_annee_scolaire||!f.id_filiere||!f.id_niveau||!f.id_classe) return this.toast('Veuillez remplir tous les champs obligatoires','error');
                this.promoEtuSubmitting = true;
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                fetch('/deliberations/promouvoir',{
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    body:JSON.stringify({id_inscription:f.idIns, id_annee_scolaire:f.id_annee_scolaire, id_filiere:f.id_filiere, id_niveau:f.id_niveau, id_classe:f.id_classe})
                }).then(async res => {
                    if(!res.ok){ const e=await res.json(); throw new Error(e.message||'Erreur'); }
                    const r = await res.json();
                    const ins = this.inscriptions.find(i => i.id===f.idIns);
                    if(ins) this.toutesIns.push({id:r.data.id, id_etudiant:ins.id_etudiant, id_annee_scolaire:Number(f.id_annee_scolaire)});
                    this.promoEtuModal = null; this.promoEtuSubmitting = false;
                    this.toast(r.message,'success');
                }).catch(err => { this.promoEtuSubmitting=false; this.toast(err.message,'error'); });
            },

            openForcePassage(idIns){
                const row = this.delibRows().find(r => r.idIns===idIns);
                if(!row) return;
                const ins = this.inscriptions.find(i => i.id===idIns);
                this.forceForm = {
                    idIns, nomEtu: row.nom,
                    id_annee_scolaire: this.prochainAnnee(),
                    id_filiere: ins?.id_filiere||'',
                    id_niveau: ins?.id_niveau||'',
                    id_classe: '',
                };
                this.forceSubmitting = false;
                this.forceModal = true;
            },

            submitForcePassage(){
                const f = this.forceForm;
                if(!f.id_annee_scolaire||!f.id_filiere||!f.id_niveau||!f.id_classe) return this.toast('Veuillez remplir tous les champs obligatoires','error');
                this.saveDecision(f.idIns, 'ADMIS');
                this.forceSubmitting = true;
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                fetch('/deliberations/promouvoir',{
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    body:JSON.stringify({id_inscription:f.idIns, id_annee_scolaire:f.id_annee_scolaire, id_filiere:f.id_filiere, id_niveau:f.id_niveau, id_classe:f.id_classe, type_inscription:'Admis par jury'})
                }).then(async res => {
                    if(!res.ok){ const e=await res.json(); throw new Error(e.message||'Erreur'); }
                    const r = await res.json();
                    const ins = this.inscriptions.find(i => i.id===f.idIns);
                    if(ins) this.toutesIns.push({id:r.data.id, id_etudiant:ins.id_etudiant, id_annee_scolaire:Number(f.id_annee_scolaire)});
                    this.forceModal = null; this.forceSubmitting = false;
                    this.toast('Passage forcé par jury confirmé • '+r.message,'success');
                }).catch(err => { this.forceSubmitting=false; this.toast(err.message,'error'); });
            },

            openPromoClasse(){
                const ins = this.delibInsForClasse();
                if(!ins.length) return this.toast('Aucun étudiant dans cette classe','warning');
                const first = ins[0];
                this.promoClasseForm = {
                    id_annee_scolaire: this.prochainAnnee(),
                    id_filiere: first.id_filiere||'',
                    id_niveau_admis: first.id_niveau||'',
                    id_classe_admis: '',
                    id_classe_redoublants: '',
                };
                this.promoClasseSubmitting = false;
                this.promoClasseModal = true;
            },

            promoClasseResume(){
                const rows = this.delibRows();
                const admis   = rows.filter(r => r.decision==='ADMIS' || (r.eligible&&!r.decision)).length;
                const exclus   = rows.filter(r => r.decision==='DÉFINITIVEMENT AJOURNÉ').length;
                const ajournes = rows.length - admis - exclus;
                return {admis, ajournes, exclus};
            },

            submitPromoClasse(){
                const f = this.promoClasseForm;
                if(!f.id_annee_scolaire||!f.id_filiere||!f.id_niveau_admis||!f.id_classe_admis) return this.toast('Veuillez remplir tous les champs obligatoires','error');
                this.promoClasseSubmitting = true;
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                fetch('/deliberations/promouvoir-classe',{
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    body:JSON.stringify({id_classe:this.delibClasse, id_annee_scolaire:f.id_annee_scolaire, id_filiere:f.id_filiere, id_niveau_admis:f.id_niveau_admis, id_classe_admis:f.id_classe_admis, id_classe_redoublants:f.id_classe_redoublants||null})
                }).then(async res => {
                    if(!res.ok){ const e=await res.json(); throw new Error(e.message||'Erreur'); }
                    const r = await res.json();
                    this.promoClasseModal = null; this.promoClasseSubmitting = false;
                    this.toast(r.message,'success');
                    setTimeout(()=>location.reload(),1200);
                }).catch(err => { this.promoClasseSubmitting=false; this.toast(err.message,'error'); });
            },

            toast(message, type='info'){
                const colors={ success:{bg:'rgba(22,163,74,.95)'}, error:{bg:'rgba(239,68,68,.95)'}, warning:{bg:'rgba(245,158,11,.95)'}, info:{bg:'rgba(90,103,216,.95)'} };
                const style=colors[type]||colors.info;
                const t=document.createElement('div');
                t.style.cssText=`position:fixed;bottom:24px;left:50%;transform:translateX(-50%);padding:12px 24px;border-radius:12px;font-size:13px;font-weight:500;background:${style.bg};color:#fff;z-index:9999;box-shadow:0 8px 32px rgba(0,0,0,.12);max-width:90%`;
                t.textContent=message; document.body.appendChild(t);
                setTimeout(()=>{ t.style.opacity='0'; t.style.transition='all .3s ease'; setTimeout(()=>t.remove(),300); },3500);
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
