<x-app-layout title="Évaluations — Avancé">
@push('styles')
<style>
.f-select{width:100%;padding:10px 12px;background:#fff;border:1px solid #E2E8F0;border-radius:8px;font-size:13px;color:#1E293B;outline:none}
.f-select:focus{border-color:#5A67D8}
.flt-select{height:38px;padding:0 12px;border:1px solid #E2E8F0;border-radius:8px;background:#fff;font-size:13px;color:#334155;min-width:190px;outline:none}
.f-table-container{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #E2E8F0;overflow:hidden}
.f-table-header th{padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.06em;background:#F8FAFC;border-bottom:1px solid #E2E8F0}
.f-table-row td{padding:12px 16px;font-size:13px;color:#334155;border-bottom:1px solid #F1F5F9;vertical-align:middle}
.f-table-row:hover{background:#F8FAFC}
.f-badge{display:inline-flex;padding:4px 10px;border-radius:8px;font-size:12px;font-weight:700}
.ss-drop{position:absolute;top:100%;left:0;right:0;margin-top:4px;max-height:220px;overflow-y:auto;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.12);border:1px solid #E2E8F0;z-index:30}
.ss-item{padding:9px 14px;font-size:13px;color:#334155;cursor:pointer}
.ss-item:hover{background:#F1F5F9}
.ss-sel{background:#EEF2FF;color:#5A67D8;font-weight:600}

.av-tabs{display:inline-flex;gap:4px;background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.05);padding:6px}
.av-tab{padding:9px 16px;border-radius:8px;font-size:13px;font-weight:700;color:#64748B;background:transparent;border:none;cursor:pointer;white-space:nowrap}
.av-tab.active{color:#5A67D8;background:rgba(90,103,216,.1)}
.av-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06);padding:20px}
.stat-card{flex:1;background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06);padding:18px;display:flex;align-items:center;gap:14px}
.stat-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.bar-wrap{display:flex;align-items:flex-end;gap:20px;height:200px;padding:0 10px}
.bar-col{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%}
.bar-fill{width:100%;max-width:56px;border-radius:6px 6px 0 0;transition:height .3s;position:relative}
.bar-val{font-size:11px;color:#64748B;margin-bottom:4px}
.bar-label{font-size:11px;color:#94A3B8;margin-top:8px}
.mini-stat{padding:8px 14px;border-radius:8px;display:flex;align-items:center;gap:6px;font-size:11px}
</style>
@endpush

@php
$niveauxJson  = $niveaux->map(fn($n) => ['id'=>$n->id,'libelle'=>$n->libelle,'ordre'=>$n->ordre]);
$classesJson  = $classes->map(fn($c) => ['id'=>$c->id,'libelle'=>$c->libelle,'id_niveau'=>$c->id_niveau,'id_filiere'=>$c->id_filiere]);
$matieresJson = $matieres->map(fn($m) => ['id'=>$m->id,'libelle'=>$m->libelle,'id_ue'=>$m->id_ue,'id_niveau'=>$m->id_niveau,'id_filiere'=>$m->id_filiere,'coefficient'=>$m->coefficient,'id_decoupage_annee'=>$m->id_decoupage_annee]);
$typesNoteJson= $typesNote->map(fn($t) => ['id'=>$t->id,'libelle'=>$t->libelle,'type_systeme'=>$t->type_systeme,'pourcentage'=>$t->pourcentage]);
$uesJson      = $ues->map(fn($u) => ['id'=>$u->id,'libelle'=>$u->libelle,'type_ue'=>$u->type_ue,'credit'=>$u->credit,'code'=>$u->code??null]);
$decoupagesJson = $decoupages->map(fn($d) => ['id'=>$d->id,'libelle'=>$d->libelle]);
$inscriptionsJson = $inscriptions->map(fn($i) => [
    'id'=>$i->id,'id_etudiant'=>$i->id_etudiant,'etu_nom'=>$i->etudiant?->nom??'?','etu_prenom'=>$i->etudiant?->prenom??'',
    'etu_matricule'=>$i->etudiant?->matricule??'','id_classe'=>$i->id_classe,'id_niveau'=>$i->id_niveau,'id_filiere'=>$i->id_filiere,'id_annee_scolaire'=>$i->id_annee_scolaire,
]);
$notesJson = $notes->map(fn($n) => ['id_inscription'=>$n->id_inscription,'id_matiere'=>$n->id_matiere,'id_type_note'=>$n->id_type_note,'note'=>$n->note]);
$moyennesSavedJson = $moyennesSaved->map(fn($m) => ['id_inscription'=>$m->id_inscription,'id_matiere'=>$m->id_matiere,'moyenne'=>$m->moyenne]);
$creditsJson = $creditsEtudiant->map(fn($c) => ['id_inscription'=>$c->id_inscription,'id_ue'=>$c->id_ue,'credits_obtenus'=>$c->credits_obtenus,'valide'=>(bool)$c->valide]);
$rattrapageJson = $sessionsRattrapage->map(fn($r) => [
    'id'=>$r->id,'id_classe'=>$r->id_classe,'classe_libelle'=>$r->classe?->libelle??'—','id_matiere'=>$r->id_matiere,
    'matiere_libelle'=>$r->matiere?->libelle??'—','date_debut'=>$r->date_debut,'date_fin'=>$r->date_fin,
]);
$hasActiveYear = (bool) $anneeActive;
@endphp

<div x-data="avancePage({{ $niveauxJson }}, {{ $classesJson }}, {{ $matieresJson }}, {{ $typesNoteJson }}, {{ $uesJson }}, {{ $inscriptionsJson }}, {{ $notesJson }}, {{ $moyennesSavedJson }}, {{ $creditsJson }}, {{ $rattrapageJson }}, {{ $hasActiveYear ? 'true' : 'false' }}, {{ $isBTS ? 'true' : 'false' }}, {{ $decoupagesJson }})" class="space-y-4">

    <div>
        <h1 class="text-xl font-bold" style="color:#1E293B">Évaluations</h1>
        <p class="text-sm mt-0.5" style="color:#94A3B8">Statistiques, notes et résultats académiques</p>
    </div>

    <div class="av-tabs">
        <button @click="tab=0" class="av-tab" :class="tab===0?'active':''">Vue d'ensemble</button>
        <button @click="tab=1" class="av-tab" :class="tab===1?'active':''">Types d'Éval.</button>
        <button @click="tab=2" class="av-tab" :class="tab===2?'active':''">Moyennes</button>
        <button @click="tab=3" class="av-tab" :class="tab===3?'active':''">Crédits</button>
        <button @click="tab=4" class="av-tab" :class="tab===4?'active':''">Rattrapage</button>
    </div>



    <div class="flex items-center flex-wrap gap-3">
        <select class="flt-select" x-model="filterNiveau" @change="filterClasse=''">
            <option value="">Tous les niveaux</option>
            <template x-for="n in niveaux" :key="n.id"><option :value="n.id" x-text="n.libelle"></option></template>
        </select>
        <select class="flt-select" x-model="filterClasse">
            <option value="">Toutes les classes</option>
            <template x-for="c in classesDispo" :key="c.id"><option :value="c.id" x-text="c.libelle"></option></template>
        </select>
        <button x-show="filterNiveau||filterClasse" @click="filterNiveau='';filterClasse=''" class="text-xs font-semibold flex items-center gap-1" style="color:#64748B">
            <i class="ri-close-line"></i> Réinitialiser
        </button>
    </div>

    {{-- ═══ TAB 0 : Vue d'ensemble ═══ --}}
    <template x-if="tab===0">
        <div class="space-y-5">
            <div class="flex gap-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(90,103,216,.1);color:#5A67D8"><i class="ri-file-list-3-line"></i></div>
                    <div><p class="text-[11px] font-bold" style="color:#94A3B8">NOTES SAISIES</p><p class="text-2xl font-bold" style="color:#5A67D8" x-text="ovStats().total"></p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(22,163,74,.1);color:#16a34a"><i class="ri-check-line"></i></div>
                    <div><p class="text-[11px] font-bold" style="color:#94A3B8">RÉUSSIES</p><p class="text-2xl font-bold" style="color:#16a34a" x-text="ovStats().reussies"></p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(249,115,22,.1);color:#f97316"><i class="ri-line-chart-line"></i></div>
                    <div><p class="text-[11px] font-bold" style="color:#94A3B8">TAUX RÉUSSITE</p><p class="text-2xl font-bold" style="color:#f97316" x-text="ovStats().taux+'%'"></p></div>
                </div>
            </div>

            <div class="av-card">
                <p class="font-bold mb-4" style="color:#1E293B">Distribution des Notes</p>
                <div class="bar-wrap">
                    <template x-for="b in ovBars()" :key="b.label">
                        <div class="bar-col">
                            <span class="bar-val" x-text="b.value"></span>
                            <div class="bar-fill" :style="'height:'+b.pct+'%;background:'+b.color"></div>
                            <span class="bar-label" x-text="b.label"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="av-card">
                <p class="font-bold mb-4" style="color:#1E293B">Moyenne par Matière</p>
                <template x-for="row in ovMoyParMatiere()" :key="row.id">
                    <div class="flex items-center gap-3 mb-3">
                        <div style="width:160px" class="text-sm truncate" x-text="row.libelle"></div>
                        <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="h-full rounded-full" :style="'width:'+(row.moy/20*100)+'%;background:'+(row.moy>=10?'#16a34a':'#ef4444')"></div>
                        </div>
                        <div class="text-sm font-bold" style="width:40px;text-align:right" :style="'color:'+(row.moy>=10?'#16a34a':'#ef4444')" x-text="row.moy.toFixed(1)"></div>
                    </div>
                </template>
                <p x-show="!ovMoyParMatiere().length" class="text-sm text-center py-6" style="color:#94A3B8">Aucune note pour cette sélection</p>
            </div>
        </div>
    </template>

    {{-- ═══ TAB 1 : Types d'Éval. ═══ --}}
    <template x-if="tab===1">
        <div class="f-table-container">
            <table class="w-full">
                <thead class="f-table-header"><tr><th>Libellé</th><th>Type Système</th><th>Pourcentage</th></tr></thead>
                <tbody>
                    <template x-for="t in typesNote" :key="t.id">
                        <tr class="f-table-row">
                            <td class="font-bold" x-text="t.libelle"></td>
                            <td><span class="f-badge" style="background:rgba(90,103,216,.1);color:#5A67D8" x-text="t.type_systeme||'—'"></span></td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div style="width:120px" class="bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-full rounded-full" style="background:#5A67D8" :style="'width:'+(t.pourcentage||0)+'%'"></div>
                                    </div>
                                    <span class="text-xs font-bold" style="color:#5A67D8" x-text="(t.pourcentage||0)+'%'"></span>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>

    {{-- ═══ TAB 2 : Moyennes ═══ --}}
    <template x-if="tab===2">
        <div class="space-y-3">
            <div class="flex justify-end">
                <button @click="calculerMoyennesEtCredits()" :disabled="!hasActiveYear||!filterClasse||calculatingMoy"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-50" style="background:#5A67D8">
                    <i class="ri-calculator-line" x-show="!calculatingMoy"></i>
                    <span x-show="calculatingMoy" style="width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;display:inline-block;animation:spin .7s linear infinite"></span>
                    <span x-text="calculatingMoy?'Calcul...':(isBTS?'Calculer & Sauvegarder':'Calculer (moyennes + crédits)')"></span>
                </button>
            </div>
            <div class="f-table-container" x-show="moyRows().length">
                <table class="w-full">
                    <thead class="f-table-header"><tr><th>Étudiant</th><th>Matière</th><th>Moyenne</th><th>Mention</th><th>État</th></tr></thead>
                    <tbody>
                        <template x-for="row in moyRows()" :key="row.key">
                            <tr class="f-table-row">
                                <td class="font-bold" x-text="row.etuNom"></td>
                                <td style="font-size:12px" x-text="row.matiere"></td>
                                <td><span class="f-badge" :style="row.moy>=10?'background:rgba(22,163,74,.1);color:#16a34a':'background:rgba(239,68,68,.1);color:#ef4444'" x-text="row.moy.toFixed(2)"></span></td>
                                <td style="color:#94A3B8" x-text="row.mention"></td>
                                <td>
                                    <span class="flex items-center gap-1" :style="row.saved?'color:#16a34a':'color:#f97316'">
                                        <i :class="row.saved?'ri-cloud-line':'ri-cloud-off-line'"></i>
                                        <span style="font-size:11px" x-text="row.saved?'Sauvegardée':'Non sauvegardée'"></span>
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="av-card text-center py-14" x-show="!moyRows().length">
                <i class="ri-functions" style="font-size:56px;color:#CBD5E1"></i>
                <p class="mt-4 text-sm" style="color:#94A3B8" x-text="!filterClasse?'Sélectionnez une classe pour voir les moyennes':'Aucune note saisie pour cette classe'"></p>
            </div>
        </div>
    </template>

    {{-- ═══ TAB 3 : Crédits ═══ --}}
    <template x-if="tab===3">
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div class="mini-stat" style="background:rgba(22,163,74,.08);color:#16a34a"><b x-text="credGlobal().obt"></b> Crédits obtenus</div>
                <div class="mini-stat" style="background:rgba(249,115,22,.08);color:#f97316"><b x-text="credGlobal().prev"></b> Crédits prévus</div>
                <div class="mini-stat" style="background:rgba(90,103,216,.08);color:#5A67D8"><b x-text="credGlobal().tout"></b> Tout validé</div>
                <div class="flex-1"></div>
                <button @click="recalculerCredits()" :disabled="isBTS||!hasActiveYear||!filterClasse||calculatingCred"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border disabled:opacity-40" style="color:#0d9488;border-color:#0d9488">
                    <i class="ri-refresh-line"></i> <span x-text="calculatingCred?'Traitement...':'Recalculer crédits'"></span>
                </button>
            </div>
            <div class="f-table-container" x-show="credRows().length">
                <table class="w-full">
                    <thead class="f-table-header"><tr><th>Étudiant</th><th>Crédits obtenus</th><th>Progression</th><th>UEs validées</th><th>Statut</th><th>Détails</th></tr></thead>
                    <tbody>
                        <template x-for="row in credRows()" :key="row.idIns">
                            <tr class="f-table-row">
                                <td><p class="font-bold" x-text="row.nom"></p><p style="font-size:11px;color:#5A67D8" x-text="row.matricule"></p></td>
                                <td x-show="row.nbTot===0" style="color:#94A3B8">—</td>
                                <td x-show="row.nbTot>0"><b :style="row.toutVal?'color:#16a34a':'color:#5A67D8'" x-text="row.obt"></b> <span style="color:#94A3B8">/ <span x-text="row.prev"></span></span></td>
                                <td x-show="row.nbTot===0" style="color:#94A3B8">—</td>
                                <td x-show="row.nbTot>0">
                                    <div style="width:120px" class="bg-gray-100 rounded-full h-2 overflow-hidden">
                                        <div class="h-full rounded-full" :style="'width:'+(row.pct*100)+'%;background:'+(row.pct>=1?'#16a34a':'#5A67D8')"></div>
                                    </div>
                                    <span style="font-size:10px;color:#94A3B8" x-text="(row.pct*100).toFixed(0)+'%'"></span>
                                </td>
                                <td x-show="row.nbTot===0" style="color:#94A3B8">—</td>
                                <td x-show="row.nbTot>0"><b :style="row.nbVal===row.nbTot?'color:#16a34a':'color:#f97316'" x-text="row.nbVal"></b> <span style="color:#94A3B8">/ <span x-text="row.nbTot"></span></span></td>
                                <td>
                                    <span class="f-badge" :style="row.nbTot===0?'background:#F1F5F9;color:#94A3B8':(row.toutVal?'background:rgba(22,163,74,.1);color:#16a34a':'background:rgba(239,68,68,.1);color:#ef4444')"
                                          x-text="row.nbTot===0?'En attente':(row.toutVal?'Tout validé':'Incomplet')"></span>
                                </td>
                                <td>
                                    <button @click="showCreditsDetail(row.idIns)" class="btn-outline-indigo" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;color:#5A67D8;border:1.5px solid #5A67D8;background:transparent">
                                        <i class="ri-list-check-2"></i> Voir détails
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="av-card text-center py-14" x-show="!credRows().length">
                <i class="ri-book-open-line" style="font-size:56px;color:#CBD5E1"></i>
                <p class="mt-4 text-sm" style="color:#94A3B8">Sélectionnez une classe pour voir les crédits</p>
            </div>
        </div>
    </template>

    {{-- ═══ TAB 4 : Rattrapage ═══ --}}
    <template x-if="tab===4">
        <div class="space-y-3">
            <div class="flex justify-end">
                <button @click="openRattrapageCreate()" :disabled="!hasActiveYear"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-50" style="background:#5A67D8">
                    <i class="ri-add-line"></i> Nouvelle Session
                </button>
            </div>
            <div class="f-table-container">
                <table class="w-full">
                    <thead class="f-table-header"><tr><th>Classe</th><th>Matière</th><th>Date Début</th><th>Date Fin</th><th style="text-align:right">Actions</th></tr></thead>
                    <tbody>
                        <template x-for="r in rattrapageFiltered()" :key="r.id">
                            <tr class="f-table-row">
                                <td><span class="f-badge" style="background:rgba(90,103,216,.1);color:#5A67D8" x-text="r.classe_libelle"></span></td>
                                <td class="font-bold" x-text="r.matiere_libelle"></td>
                                <td x-text="r.date_debut||'—'"></td>
                                <td x-text="r.date_fin||'—'"></td>
                                <td style="text-align:right">
                                    <button @click="openRattrapageEdit(r)" class="act-btn hover:bg-indigo-50" style="color:#5A67D8;width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;border:none;background:transparent"><i class="ri-edit-2-line text-[15px]"></i></button>
                                    <button @click="deleteRattrapage(r)" class="act-btn hover:bg-red-50" style="color:#ef4444;width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;border:none;background:transparent"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <p x-show="!rattrapageFiltered().length" class="text-center py-10 text-sm" style="color:#94A3B8">Aucune session de rattrapage</p>
            </div>
        </div>
    </template>

    {{-- Modal Crédits détail --}}
    <template x-if="creditsDetail">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)" @click.self="creditsDetail=null">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col" style="max-height:85vh">
                <div style="background:#5A67D8;border-radius:16px 16px 0 0" class="px-6 py-4 flex items-center gap-3">
                    <i class="ri-wallet-3-line text-white"></i>
                    <div class="flex-1">
                        <p class="text-white font-bold" x-text="creditsDetail.nom"></p>
                        <p class="text-xs" style="color:rgba(255,255,255,.75)" x-text="creditsDetail.matricule"></p>
                    </div>
                    <button @click="creditsDetail=null" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-white/10"><i class="ri-close-line text-white text-lg"></i></button>
                </div>
                <div class="overflow-y-auto p-4 space-y-2" style="background:#F8FAFC">
                    <template x-for="u in creditsDetail.ues" :key="u.key">
                        <div class="flex items-center gap-3 p-3 rounded-lg" :style="'background:'+(u.pending?'rgba(148,163,184,.06)':(u.val?'rgba(22,163,74,.06)':'rgba(239,68,68,.05)'))+';border:1px solid '+(u.pending?'#E2E8F0':(u.val?'#bbf7d0':'#fecaca'))">
                            <i :class="u.pending?'ri-time-line':(u.val?'ri-checkbox-circle-line':'ri-close-circle-line')" :style="'color:'+(u.pending?'#94A3B8':(u.val?'#16a34a':'#ef4444'))"></i>
                            <div class="flex-1"><p class="font-semibold text-sm" x-text="u.libelle"></p></div>
                            <div class="text-right">
                                <p class="text-sm font-bold" :style="'color:'+(u.moy>=10?'#16a34a':(u.moy>0?'#ef4444':'#94A3B8'))" x-text="u.moy>0?u.moy.toFixed(2):'-'"></p>
                                <p style="font-size:10px;color:#94A3B8">Moy.</p>
                            </div>
                            <div class="text-right" style="width:60px">
                                <p class="text-sm font-bold" :style="'color:'+(u.pending?'#94A3B8':(u.val?'#16a34a':'#ef4444'))" x-text="(u.pending?0:u.cred)+'/'+u.prev"></p>
                                <p style="font-size:10px;color:#94A3B8">Crédits</p>
                            </div>
                            <span class="f-badge" style="font-size:10px" :style="'background:'+(u.pending?'#F1F5F9':(u.val?'rgba(22,163,74,.1)':'rgba(239,68,68,.1)'))+';color:'+(u.pending?'#94A3B8':(u.val?'#16a34a':'#ef4444'))"
                                  x-text="u.pending?'Non généré':(u.val?'Validé':'Non validé')"></span>
                        </div>
                    </template>
                    <p x-show="!creditsDetail.ues.length" class="text-center py-8 text-sm" style="color:#94A3B8">Aucune UE associée à ce niveau/filière.</p>
                </div>
                <div class="px-6 py-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs" style="color:#94A3B8" x-text="creditsDetail.footer"></span>
                    <button @click="creditsDetail=null" class="text-sm font-semibold" style="color:#64748B">Fermer</button>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal Rattrapage --}}
    <template x-if="rattModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="rattEditing?'Modifier Session Rattrapage':'Nouvelle Session Rattrapage'"></h2>
                    <button @click="rattModal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="f-label" style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block">Classe <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(classes.map(c=>({v:c.id,l:c.libelle})), rattForm.id_classe, 'Sélectionner...')" class="relative" @click.outside="open=false">
                            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" style="width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px" :placeholder="ph" autocomplete="off">
                            <div x-show="open" class="ss-drop">
                                <template x-for="o in filtered" :key="o.v"><div @click="select(o); rattForm.id_classe=o.v" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div></template>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="f-label" style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block">Matière <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(matieres.map(m=>({v:m.id,l:m.libelle})), rattForm.id_matiere, 'Sélectionner...')" class="relative" @click.outside="open=false">
                            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" style="width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px" :placeholder="ph" autocomplete="off">
                            <div x-show="open" class="ss-drop">
                                <template x-for="o in filtered" :key="o.v"><div @click="select(o); rattForm.id_matiere=o.v" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div></template>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="f-label" style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block">Date début</label><input type="date" x-model="rattForm.date_debut" class="f-select"></div>
                        <div><label class="f-label" style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block">Date fin</label><input type="date" x-model="rattForm.date_fin" class="f-select"></div>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="rattModal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                        <button type="button" @click="saveRattrapage()" :disabled="rattSubmitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:#5A67D8" x-text="rattSubmitting?'...':'Enregistrer'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>

</div>

<style>@keyframes spin{to{transform:rotate(360deg)}}</style>

@push('scripts')
<script>
function avancePage(niveauxData, classesData, matieresData, typesNoteData, uesData, inscriptionsData, notesData, moyennesSavedData, creditsData, rattrapageData, hasActiveYear, isBTS, decoupagesData){
    return {
        niveaux: niveauxData, classes: classesData, matieres: matieresData, typesNote: typesNoteData, ues: uesData,
        inscriptions: inscriptionsData, notes: notesData, moyennesSaved: moyennesSavedData, credits: creditsData,
        rattrapage: rattrapageData, hasActiveYear: hasActiveYear, isBTS: isBTS,
        decoupages: decoupagesData || [],
        tab: 0, filterNiveau:'', filterClasse:'',
        calculatingMoy:false, calculatingCred:false,
        creditsDetail:null,
        rattModal:false, rattEditing:false, rattSubmitting:false,
        rattForm:{id:'',id_classe:'',id_matiere:'',date_debut:'',date_fin:''},

        get classesDispo(){ return this.filterNiveau ? this.classes.filter(c=>String(c.id_niveau)===String(this.filterNiveau)) : this.classes; },

        get filteredInscriptions(){
            return this.inscriptions.filter(i=>{
                if(this.filterNiveau && String(i.id_niveau)!==String(this.filterNiveau)) return false;
                if(this.filterClasse && String(i.id_classe)!==String(this.filterClasse)) return false;
                return true;
            });
        },
        get filteredNotes(){
            const ids = new Set(this.filteredInscriptions.map(i=>i.id));
            return this.notes.filter(n=>ids.has(n.id_inscription));
        },

        // ── Tab 0 ──
        ovStats(){
            const notes=this.filteredNotes, total=notes.length;
            const reussies = notes.filter(n=>Number(n.note)>=10).length;
            const taux = total>0 ? Math.round(reussies/total*100) : 0;
            return {total, reussies, taux};
        },
        ovBars(){
            const notes=this.filteredNotes;
            const c = [
                {label:'0-5', color:'#ef4444', value: notes.filter(n=>Number(n.note)<5).length},
                {label:'5-10', color:'#f97316', value: notes.filter(n=>Number(n.note)>=5&&Number(n.note)<10).length},
                {label:'10-15', color:'#3b82f6', value: notes.filter(n=>Number(n.note)>=10&&Number(n.note)<15).length},
                {label:'15-20', color:'#16a34a', value: notes.filter(n=>Number(n.note)>=15).length},
            ];
            const max = Math.max(1, ...c.map(x=>x.value));
            return c.map(x=>({...x, pct: x.value/max*100}));
        },
        ovMoyParMatiere(){
            const notes=this.filteredNotes, out=[];
            for(const mat of this.matieres){
                const notesM = notes.filter(n=>n.id_matiere===mat.id);
                if(!notesM.length) continue;
                const moy = notesM.reduce((s,n)=>s+Number(n.note||0),0)/notesM.length;
                out.push({id:mat.id, libelle:mat.libelle, moy});
            }
            return out;
        },

        // ── Tab 2 : Moyennes ──
        calcMoyenne(idIns, idMat){
            const notesM = this.notes.filter(n=>n.id_inscription===idIns && n.id_matiere===idMat);
            if(!notesM.length) return -1;
            const byType = {};
            for(const n of notesM){ const t=n.id_type_note||0; (byType[t] ||= []).push(Number(n.note)||0); }
            let sumPoids=0, sumNote=0;
            for(const key of Object.keys(byType)){
                const vals=byType[key], avg=vals.reduce((s,v)=>s+v,0)/vals.length;
                if(Number(key)===0){ sumPoids+=1; sumNote+=avg; }
                else { const tn=this.typesNote.find(t=>t.id===Number(key)); const pct=tn?(Number(tn.pourcentage)||1):1; sumPoids+=pct; sumNote+=avg*pct; }
            }
            return sumPoids===0?0:sumNote/sumPoids;
        },
        mentionOf(moy){ return moy>=16?'Très Bien':moy>=14?'Bien':moy>=12?'Assez Bien':moy>=10?'Passable':'Insuffisant'; },
        moyRows(){
            const out=[];
            for(const ins of this.filteredInscriptions){
                for(const mat of this.matieres){
                    const calc = this.calcMoyenne(ins.id, mat.id);
                    if(calc<0) continue;
                    const saved = this.moyennesSaved.find(m=>m.id_inscription===ins.id && m.id_matiere===mat.id);
                    const moy = saved ? Number(saved.moyenne) : calc;
                    out.push({key:ins.id+'-'+mat.id, etuNom:ins.etu_nom+' '+ins.etu_prenom, matiere:mat.libelle, moy, mention:this.mentionOf(moy), saved:!!saved});
                }
            }
            return out;
        },
        calculerMoyennesEtCredits(){
            if(!this.hasActiveYear||!this.filterClasse) return;
            this.calculatingMoy=true;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            fetch('/moyennes/classe/'+this.filterClasse, {method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}})
                .then(async res=>{
                    if(!res.ok) throw new Error('Erreur calcul moyennes');
                    const r = await res.json();
                    if(!this.isBTS){
                        const res2 = await fetch('/moyennes/classe/'+this.filterClasse+'/credits', {method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}});
                        if(!res2.ok) throw new Error('Erreur génération crédits');
                    }
                    this.toast(r.count===0?'Aucune note trouvée pour cette classe':(r.count+' moyenne(s) calculée(s)'+(!this.isBTS?' • crédits mis à jour':'')), r.count===0?'warning':'success');
                    setTimeout(()=>location.reload(), 900);
                }).catch(err=>{ this.calculatingMoy=false; this.toast(err.message,'error'); });
        },

        // ── Tab 3 : Crédits ──
        ueIdsFor(ins){
            return [...new Set(this.matieres.filter(m=>String(m.id_filiere)===String(ins.id_filiere) && String(m.id_niveau)===String(ins.id_niveau) && m.id_ue).map(m=>m.id_ue))];
        },
        creditsResume(ins){
            const credits = this.credits.filter(c=>c.id_inscription===ins.id);
            const obt = credits.reduce((s,c)=>s+(Number(c.credits_obtenus)||0),0);
            const ueIds = this.ueIdsFor(ins);
            const prev = ueIds.reduce((s,id)=>{ const ue=this.ues.find(u=>u.id===id); return s+(ue?Number(ue.credit)||0:0); },0);
            return {obt, prev, credits, nbTot: credits.length, nbVal: credits.filter(c=>c.valide).length};
        },
        credGlobal(){
            let obt=0, prev=0, tout=0;
            for(const ins of this.filteredInscriptions){
                const r = this.creditsResume(ins);
                obt+=r.obt; prev+=r.prev;
                if(r.prev>0 && r.obt>=r.prev) tout++;
            }
            return {obt, prev, tout};
        },
        credRows(){
            return this.filteredInscriptions.map(ins=>{
                const r = this.creditsResume(ins);
                const pct = r.prev>0 ? Math.min(1, r.obt/r.prev) : 0;
                return { idIns: ins.id, nom: ins.etu_nom+' '+ins.etu_prenom, matricule: ins.etu_matricule,
                    obt:r.obt, prev:r.prev, pct, nbVal:r.nbVal, nbTot:r.nbTot, toutVal: r.prev>0 && r.obt>=r.prev };
            });
        },
        calcMoyUe(idIns, idUe){
            const ins = this.inscriptions.find(i=>i.id===idIns);
            if(!ins) return 0;
            const mats = this.matieres.filter(m=>m.id_ue===idUe && String(m.id_filiere)===String(ins.id_filiere) && String(m.id_niveau)===String(ins.id_niveau));
            if(!mats.length) return 0;
            let sumP=0, sumN=0;
            for(const mat of mats){
                const moy = this.moyennesSaved.find(m=>m.id_inscription===idIns && m.id_matiere===mat.id);
                if(!moy) continue;
                const coef = Number(mat.coefficient)||1;
                sumP += coef; sumN += Number(moy.moyenne)*coef;
            }
            return sumP>0 ? sumN/sumP : 0;
        },
        showCreditsDetail(idIns){
            const ins = this.inscriptions.find(i=>i.id===idIns);
            if(!ins) return;
            const credits = this.credits.filter(c=>c.id_inscription===idIns);
            const ueIds = this.ueIdsFor(ins);
            const rows = ueIds.map(ueId=>{
                const ue = this.ues.find(u=>u.id===ueId);
                const rec = credits.find(c=>c.id_ue===ueId);
                const pending = !rec;
                return { key:ueId, libelle: ue?ue.libelle:'UE inconnue', moy: this.calcMoyUe(idIns, ueId),
                    cred: rec?Number(rec.credits_obtenus)||0:0, prev: ue?Number(ue.credit)||0:0, val: rec?!!rec.valide:false, pending };
            });
            const r = this.creditsResume(ins);
            this.creditsDetail = { nom: ins.etu_nom+' '+ins.etu_prenom, matricule: ins.etu_matricule, ues: rows,
                footer: r.obt+' / '+r.prev+' crédits  •  '+r.nbVal+' / '+ueIds.length+' UEs validées' };
        },
        recalculerCredits(){
            if(this.isBTS||!this.hasActiveYear||!this.filterClasse) return;
            this.calculatingCred = true;
            fetch('/moyennes/classe/'+this.filterClasse+'/credits', {method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}})
                .then(async res=>{
                    if(!res.ok) throw new Error('Erreur');
                    const r = await res.json();
                    this.toast(r.count+' crédit(s) UE attribués','success');
                    setTimeout(()=>location.reload(), 900);
                }).catch(err=>{ this.calculatingCred=false; this.toast(err.message,'error'); });
        },

        // ── Tab 4 : Rattrapage ──
        rattrapageFiltered(){
            return this.rattrapage.filter(r=>{
                if(this.filterClasse) return String(r.id_classe)===String(this.filterClasse);
                if(this.filterNiveau){ const c=this.classes.find(c=>c.id===r.id_classe); return c && String(c.id_niveau)===String(this.filterNiveau); }
                return true;
            });
        },
        openRattrapageCreate(){ this.rattEditing=false; this.rattSubmitting=false; this.rattForm={id:'',id_classe:'',id_matiere:'',date_debut:'',date_fin:''}; this.rattModal=true; },
        openRattrapageEdit(r){ this.rattEditing=true; this.rattSubmitting=false; this.rattForm={id:r.id,id_classe:r.id_classe,id_matiere:r.id_matiere,date_debut:r.date_debut||'',date_fin:r.date_fin||''}; this.rattModal=true; },
        saveRattrapage(){
            if(!this.rattForm.id_classe||!this.rattForm.id_matiere) return this.toast('Classe et matière requises','error');
            this.rattSubmitting=true;
            const url = this.rattEditing ? '/sessions-rattrapage/'+this.rattForm.id : '/sessions-rattrapage';
            const method = this.rattEditing ? 'PUT' : 'POST';
            fetch(url, {method, headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}, body:JSON.stringify(this.rattForm)})
                .then(async res=>{
                    if(!res.ok){ const e=await res.json(); throw new Error(e.message||'Erreur'); }
                    const result = await res.json();
                    const cls = this.classes.find(c=>c.id===result.data.id_classe);
                    const mat = this.matieres.find(m=>m.id===result.data.id_matiere);
                    const flat = {id:result.data.id, id_classe:result.data.id_classe, classe_libelle: cls?cls.libelle:'—', id_matiere:result.data.id_matiere, matiere_libelle: mat?mat.libelle:'—', date_debut:result.data.date_debut, date_fin:result.data.date_fin};
                    if(this.rattEditing){ const idx=this.rattrapage.findIndex(x=>x.id===flat.id); if(idx!==-1) this.rattrapage[idx]=flat; }
                    else this.rattrapage.unshift(flat);
                    this.rattModal=false; this.rattSubmitting=false;
                    this.toast(this.rattEditing?'Session modifiée':'Session créée','success');
                }).catch(err=>{ this.rattSubmitting=false; this.toast(err.message,'error'); });
        },
        deleteRattrapage(r){
            if(!confirm('Supprimer cette session de rattrapage ?')) return;
            fetch('/sessions-rattrapage/'+r.id, {method:'DELETE', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); this.rattrapage=this.rattrapage.filter(x=>x.id!==r.id); this.toast('Session supprimée','success'); })
                .catch(err=>this.toast(err.message,'error'));
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
