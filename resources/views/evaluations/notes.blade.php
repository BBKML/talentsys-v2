<x-app-layout title="Notes">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none;transition:all .15s}
.f-input:focus{background:#fff;box-shadow:0 0 0 2px #5A67D844}
.f-select{width:100%;padding:10px 12px;background:#fff;border:1px solid #E2E8F0;border-radius:8px;font-size:13px;color:#1E293B;outline:none}
.f-select:focus{border-color:#5A67D8}

/* Filtre natif compact */
.flt-select{height:38px;padding:0 12px;border:1px solid #E2E8F0;border-radius:8px;background:#fff;font-size:13px;color:#334155;min-width:190px;outline:none}
.flt-select:focus{border-color:#5A67D8}

/* Table */
.f-table-container{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #E2E8F0;overflow:hidden}
.f-table-header th{padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.06em;background:#F8FAFC;border-bottom:1px solid #E2E8F0}
.f-table-row td{padding:12px 16px;font-size:13px;color:#334155;border-bottom:1px solid #F1F5F9}
.f-table-row:hover{background:#F8FAFC}
.f-avatar{width:32px;height:32px;border-radius:50%;background:rgba(90,103,216,.1);color:#5A67D8;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0}
.f-badge{display:inline-flex;padding:4px 10px;border-radius:8px;font-size:12px;font-weight:700}
.btn-outline-indigo{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:8px;font-size:12px;font-weight:600;color:#5A67D8;border:1.5px solid #5A67D8;background:transparent;cursor:pointer;transition:all .15s}
.btn-outline-indigo:hover{background:#EEF2FF}
.act-btn{width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;cursor:pointer;border:none;background:transparent}

/* Recherche déroulante */
.ss-drop{position:absolute;top:100%;left:0;right:0;margin-top:4px;max-height:220px;overflow-y:auto;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.12);border:1px solid #E2E8F0;z-index:30}
.ss-item{padding:9px 14px;font-size:13px;color:#334155;cursor:pointer}
.ss-item:hover{background:#F1F5F9}
.ss-sel{background:#EEF2FF;color:#5A67D8;font-weight:600}

/* Détail étudiant */
.detail-header{background:#5A67D8;border-radius:16px 16px 0 0;padding:18px 24px;display:flex;align-items:center;gap:14px}
.note-card{background:#fff;border-radius:10px;border:1px solid #E2E8F0;padding:12px 14px;margin-bottom:8px}
.chip{display:inline-flex;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700}

/* Saisie par classe */
.row-num{width:28px;height:28px;border-radius:50%;background:rgba(90,103,216,.1);color:#5A67D8;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px;flex-shrink:0}
</style>
@endpush

@php
$notesJson = $notes->map(fn($n) => [
    'id'             => $n->id,
    'id_inscription' => $n->id_inscription,
    'id_matiere'     => $n->id_matiere,
    'id_type_note'   => $n->id_type_note,
    'note'           => $n->note,
    'session'        => $n->session ?: 'Normale',
    'etu_id'         => $n->inscription?->id_etudiant,
    'classe_id'      => $n->inscription?->id_classe,
    'niveau_id'      => $n->inscription?->id_niveau,
    'matiere_libelle'=> $n->matiere?->libelle ?? '—',
    'decoupage_id'   => $n->matiere?->id_decoupage_annee,
    'type_libelle'   => $n->typeNote?->libelle ?? '—',
]);

$inscriptionsJson = $inscriptions->map(fn($i) => [
    'id'                 => $i->id,
    'numero_inscription' => $i->numero_inscription,
    'id_etudiant'        => $i->id_etudiant,
    'etu_nom'            => $i->etudiant?->nom ?? '?',
    'etu_prenom'         => $i->etudiant?->prenom ?? '',
    'id_classe'          => $i->id_classe,
    'id_niveau'          => $i->id_niveau,
    'id_filiere'         => $i->id_filiere,
    'label'              => ($i->numero_inscription ?: '?').' - '.($i->etudiant?->nom).' '.($i->etudiant?->prenom),
]);

$classesJson    = $classes->map(fn($c) => ['id' => $c->id, 'libelle' => $c->libelle, 'id_niveau' => $c->id_niveau, 'id_filiere' => $c->id_filiere]);
$niveauxJson    = $niveaux->map(fn($n) => ['id' => $n->id, 'libelle' => $n->libelle]);
$matieresJson   = $matieres->map(fn($m) => ['id' => $m->id, 'libelle' => $m->libelle, 'id_decoupage_annee' => $m->id_decoupage_annee, 'id_niveau' => $m->id_niveau, 'id_filiere' => $m->id_filiere]);
$typesNoteJson  = $typesNote->map(fn($t) => ['id' => $t->id, 'libelle' => $t->libelle]);
$decoupagesJson = $decoupages->map(fn($d) => ['id' => $d->id, 'libelle' => $d->libelle]);
@endphp

<div x-data="notesPage({{ $notesJson }}, {{ $inscriptionsJson }}, {{ $classesJson }}, {{ $niveauxJson }}, {{ $matieresJson }}, {{ $typesNoteJson }}, {{ $decoupagesJson }})" class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Notes</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8">
                <span x-text="filtered.length"></span> note(s) — <span x-text="Object.keys(grouped).length"></span> étudiant(s)
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="openBulk()" class="btn-outline-indigo" style="padding:9px 16px">
                <i class="ri-table-2"></i> Saisie par classe
            </button>
            <button @click="location.reload()" title="Actualiser les données" class="act-btn hover:bg-gray-100" style="color:#64748B;border:1.5px solid #E2E8F0;width:38px;height:38px">
                <i class="ri-refresh-line"></i>
            </button>
            <button @click="openCreate()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition" style="background:#5A67D8">
                <i class="ri-add-line text-base"></i> Saisir une Note
            </button>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="flex items-center flex-wrap gap-3">
        <select class="flt-select" x-model="filterNiveau">
            <option value="">Tous les niveaux</option>
            <template x-for="n in niveaux" :key="n.id"><option :value="n.id" x-text="n.libelle"></option></template>
        </select>
        <select class="flt-select" x-model="filterClasse">
            <option value="">Toutes les classes</option>
            <template x-for="c in classes" :key="c.id"><option :value="c.id" x-text="c.libelle"></option></template>
        </select>
        <select class="flt-select" x-model="filterSemestre">
            <option value="">Tous les semestres</option>
            <template x-for="d in decoupages" :key="d.id"><option :value="d.id" x-text="d.libelle"></option></template>
        </select>
        <div class="relative" style="min-width:220px" x-data="sSelect(matiereFilterOpts(), filterMatiere, 'Toutes les matières')" @click.outside="open=false">
            <input type="hidden" :value="v" x-effect="filterMatiere = v">
            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="flt-select" style="min-width:220px" :placeholder="ph" autocomplete="off">
            <div x-show="open" class="ss-drop">
                <div class="ss-item" style="color:#94A3B8" @click="v='';s='';open=false;filterMatiere=''">Toutes les matières</div>
                <template x-for="o in filtered" :key="o.v">
                    <div @click="select(o)" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                </template>
            </div>
        </div>
        <button x-show="filterNiveau||filterClasse||filterSemestre||filterMatiere" @click="filterNiveau='';filterClasse='';filterSemestre='';filterMatiere=''"
                class="text-xs font-semibold flex items-center gap-1" style="color:#64748B">
            <i class="ri-close-line"></i> Réinitialiser
        </button>
    </div>

    {{-- Table --}}
    <div class="f-table-container">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 flex-wrap gap-3">
            <div class="relative">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-300"></i>
                <input x-model="search" @input="page=1" type="text" placeholder="Rechercher un étudiant..."
                       class="pl-9 pr-4 py-2 rounded-lg text-sm border border-slate-200 bg-slate-50 outline-none focus:border-indigo-400 focus:bg-white transition-all duration-200"
                       style="min-width:240px">
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">Lignes/page :</span>
                <select x-model.number="perPage" @change="page=1" class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 outline-none bg-white">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <div x-show="!studentIds.length" class="py-16 text-center">
            <i class="ri-file-list-3-line text-4xl" style="color:#CBD5E1"></i>
            <p class="mt-3 text-sm font-semibold" style="color:#64748B">Aucune note</p>
        </div>

        <div class="overflow-x-auto" x-show="studentIds.length">
            <table class="w-full">
                <thead class="f-table-header">
                    <tr>
                        <th>Étudiant</th>
                        <th>Classe</th>
                        <th>Nb Notes</th>
                        <th>Moyenne</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="idEtu in pagedStudentIds" :key="idEtu">
                        <tr class="f-table-row">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="f-avatar" x-text="initial(idEtu)"></div>
                                    <span class="font-bold" x-text="studentName(idEtu)"></span>
                                </div>
                            </td>
                            <td x-text="studentClasse(idEtu)"></td>
                            <td>
                                <span class="f-badge" style="background:rgba(90,103,216,.1);color:#5A67D8" x-text="grouped[idEtu].length"></span>
                            </td>
                            <td>
                                <span class="f-badge" :style="moyEtudiant(grouped[idEtu])>=10?'background:rgba(22,163,74,.1);color:#15803d':'background:rgba(239,68,68,.1);color:#dc2626'"
                                      x-text="moyEtudiant(grouped[idEtu]).toFixed(2)+'/20'"></span>
                            </td>
                            <td style="text-align:right">
                                <button @click="showDetail(idEtu)" class="btn-outline-indigo">
                                    <i class="ri-eye-line"></i> Voir notes
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between px-5 py-3 border-t border-slate-100 flex-wrap gap-2" x-show="studentIds.length">
            <p class="text-xs" style="color:#94A3B8" x-text="studentIds.length===0?'0 résultat':(((page-1)*perPage+1)+'–'+Math.min(page*perPage,studentIds.length)+' sur '+studentIds.length+' résultat(s)')"></p>
            <div class="flex items-center gap-1">
                <button @click="page>1&&page--" :disabled="page===1" class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40">
                    <i class="ri-arrow-left-s-line text-sm" style="color:#64748B"></i>
                </button>
                <template x-for="p in pageButtons" :key="p.key">
                    <span>
                        <span x-show="p.ellipsis" class="px-1 text-xs text-slate-400">...</span>
                        <button x-show="!p.ellipsis" @click="page=p.n" class="w-7 h-7 rounded-lg text-xs font-bold"
                                :class="page===p.n?'text-white':'border border-slate-200 text-slate-500 hover:bg-slate-50'"
                                :style="page===p.n?'background:#5A67D8':''" x-text="p.n"></button>
                    </span>
                </template>
                <button @click="page<totalPages&&page++" :disabled="page===totalPages" class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40">
                    <i class="ri-arrow-right-s-line text-sm" style="color:#64748B"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Détail étudiant --}}
    <template x-if="detail">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)" @click.self="detail=null">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col" style="max-height:85vh">
                <div class="detail-header">
                    <div style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0" x-text="detail.etu.prenom.charAt(0).toUpperCase()"></div>
                    <div class="flex-1">
                        <p class="text-white font-bold text-base" x-text="detail.etu.nom+' '+detail.etu.prenom"></p>
                        <p class="text-xs" style="color:rgba(255,255,255,.8)" x-text="detail.notes.length+' note(s) — Moyenne : '+moyEtudiant(detail.notes).toFixed(2)+'/20'"></p>
                    </div>
                    <button @click="detail=null" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-white/10">
                        <i class="ri-close-line text-white text-lg"></i>
                    </button>
                </div>
                <div class="overflow-y-auto p-5" style="background:#F8FAFC">
                    <template x-for="n in detail.notes" :key="n.id">
                        <div class="note-card">
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <p class="font-bold text-sm" style="color:#1E293B" x-text="n.matiere_libelle"></p>
                                    <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                        <span class="chip" style="background:rgba(90,103,216,.1);color:#5A67D8" x-text="n.type_libelle"></span>
                                        <span class="chip" style="background:#F1F5F9;color:#64748B" x-text="n.session"></span>
                                        <span class="chip" x-show="decoupageLabel(n.decoupage_id)" style="background:rgba(249,115,22,.1);color:#c2410c" x-text="decoupageLabel(n.decoupage_id)"></span>
                                    </div>
                                </div>
                                <span class="f-badge" :style="n.note>=10?'background:rgba(22,163,74,.1);color:#15803d':'background:rgba(239,68,68,.1);color:#dc2626'"
                                      x-text="Number(n.note).toFixed(1)+'/20'"></span>
                                <button @click="const nn=n; detail=null; openEdit(nn)" class="act-btn hover:bg-indigo-50" style="color:#5A67D8"><i class="ri-edit-2-line text-[15px]"></i></button>
                                <button @click="const nn=n; deleteNote(nn)" class="act-btn hover:bg-red-50" style="color:#ef4444"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal Nouvelle/Modifier Note --}}
    <template x-if="modal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="editing?'Modifier Note':'Nouvelle Note'"></h2>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="f-label">Inscription <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(inscriptions.map(i=>({v:i.id,l:i.label})), form.id_inscription, 'Sélectionner...')" class="relative" @click.outside="open=false">
                            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" :placeholder="ph" autocomplete="off">
                            <div x-show="open" class="ss-drop">
                                <div x-show="!filtered.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucun résultat</div>
                                <template x-for="o in filtered" :key="o.v">
                                    <div @click="select(o); form.id_inscription=o.v" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="f-label">Matière <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(matieres.map(m=>({v:m.id,l:m.libelle})), form.id_matiere, 'Sélectionner...')" class="relative" @click.outside="open=false">
                            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" :placeholder="ph" autocomplete="off">
                            <div x-show="open" class="ss-drop">
                                <div x-show="!filtered.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucun résultat</div>
                                <template x-for="o in filtered" :key="o.v">
                                    <div @click="select(o); form.id_matiere=o.v" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="f-label">Type <span style="color:#EF4444">*</span></label>
                            <select x-model="form.id_type_note" class="f-select">
                                <option value="">Sélectionner...</option>
                                <template x-for="t in typesNote" :key="t.id"><option :value="t.id" x-text="t.libelle"></option></template>
                            </select>
                        </div>
                        <div>
                            <label class="f-label">Session</label>
                            <select x-model="form.session" class="f-select">
                                <option value="Normale">Normale</option>
                                <option value="Rattrapage">Rattrapage</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="f-label">Note (/20) <span style="color:#EF4444">*</span></label>
                        <input type="number" x-model="form.note" min="0" max="20" step="0.25" class="f-input">
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="modal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                        <button type="button" @click="submitForm()" :disabled="submitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:#5A67D8" x-text="submitting?'...':'Enregistrer'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal Saisie par classe --}}
    <template x-if="bulk.open">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl flex flex-col" style="width:720px;max-height:88vh">
                <div class="detail-header">
                    <i class="ri-table-2 text-white text-lg"></i>
                    <p class="text-white font-bold text-base flex-1">Saisie des notes par classe</p>
                    <button @click="bulk.open=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-white/10"><i class="ri-close-line text-white text-lg"></i></button>
                </div>

                <div class="p-4" style="background:#F8FAFC">
                    <div class="grid grid-cols-2 gap-3">
                        <select class="f-select" x-model="bulk.classeId" @change="bulk.matiereId='';rebuildBulkRows()">
                            <option value="">Classe *</option>
                            <template x-for="c in classes" :key="c.id"><option :value="c.id" x-text="c.libelle"></option></template>
                        </select>
                        <select class="f-select" x-model="bulk.matiereId" @change="rebuildBulkRows()">
                            <option value="">Matière *</option>
                            <template x-for="m in bulkMatieresFiltrees()" :key="m.id"><option :value="m.id" x-text="m.libelle"></option></template>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mt-2.5">
                        <select class="f-select" x-model="bulk.typeNoteId" @change="rebuildBulkRows()">
                            <option value="">Type de note *</option>
                            <template x-for="t in typesNote" :key="t.id"><option :value="t.id" x-text="t.libelle"></option></template>
                        </select>
                        <select class="f-select" x-model="bulk.session" @change="rebuildBulkRows()">
                            <option value="Normale">Normale</option>
                            <option value="Rattrapage">Rattrapage</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-gray-100"></div>

                <div class="flex-1 overflow-y-auto">
                    <template x-if="!bulk.classeId || !bulk.matiereId">
                        <div class="py-16 text-center">
                            <i class="ri-school-line text-4xl" style="color:#CBD5E1"></i>
                            <p class="mt-3 text-sm" style="color:#94A3B8">Sélectionnez une classe et une matière</p>
                        </div>
                    </template>
                    <template x-if="bulk.classeId && bulk.matiereId && !bulk.rows.length">
                        <div class="py-16 text-center">
                            <i class="ri-group-line text-4xl" style="color:#CBD5E1"></i>
                            <p class="mt-3 text-sm" style="color:#94A3B8">Aucun étudiant inscrit dans cette classe</p>
                        </div>
                    </template>
                    <template x-if="bulk.classeId && bulk.matiereId && bulk.rows.length">
                        <div>
                            <div class="flex items-center px-4 py-2" style="background:#F1F5F9">
                                <span style="width:40px"></span>
                                <span class="flex-1 text-xs font-bold" style="color:#94A3B8">ÉTUDIANT</span>
                                <span style="width:120px;text-align:center" class="text-xs font-bold" style="color:#94A3B8">NOTE /20</span>
                                <span style="width:60px;text-align:center" class="text-xs font-bold" style="color:#94A3B8">STATUT</span>
                            </div>
                            <div class="px-4 py-2 space-y-1.5">
                                <template x-for="(row,i) in bulk.rows" :key="row.id_inscription">
                                    <div class="flex items-center gap-3 px-3 py-2.5 bg-white border border-gray-200 rounded-lg">
                                        <div class="row-num" x-text="i+1"></div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-sm truncate" x-text="row.nom"></p>
                                            <p class="text-xs" style="color:#94A3B8" x-text="row.numero"></p>
                                        </div>
                                        <input type="number" min="0" max="20" step="0.25" x-model="row.note" placeholder="—"
                                               class="text-center font-bold rounded-lg border border-gray-200 focus:border-indigo-400 outline-none"
                                               style="width:120px;padding:8px;font-size:14px"
                                               :style="row.note!==''&&row.note!==null?(Number(row.note)>=10?'color:#15803d':'color:#dc2626'):''">
                                        <div style="width:60px;text-align:center">
                                            <span x-show="row.note!==''&&row.note!==null" class="chip"
                                                  :style="Number(row.note)>=10?'background:rgba(22,163,74,.1);color:#15803d':'background:rgba(239,68,68,.1);color:#dc2626'"
                                                  x-text="Number(row.note)>=10?'ADMIS':'AJOURNÉ'"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="border-t border-gray-100 px-5 py-3.5 flex items-center gap-4">
                    <template x-if="bulk.classeId && bulk.matiereId">
                        <div class="flex items-center gap-4 text-xs" style="color:#64748B">
                            <span><i class="ri-group-line"></i> <span x-text="bulk.rows.length"></span> étudiant(s)</span>
                            <span><i class="ri-edit-line"></i> <span x-text="bulk.rows.filter(r=>r.note!==''&&r.note!==null).length"></span> note(s) saisie(s)</span>
                        </div>
                    </template>
                    <div class="flex-1"></div>
                    <button @click="bulk.open=false" class="px-4 py-2 text-sm font-semibold" style="color:#ef4444">Annuler</button>
                    <button @click="saveBulk()" :disabled="!bulk.classeId||!bulk.matiereId||!bulk.typeNoteId||bulk.saving"
                            class="px-5 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:#5A67D8"
                            x-text="bulk.saving?'Enregistrement...':'Enregistrer tout'"></button>
                </div>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function notesPage(notesData, inscriptionsData, classesData, niveauxData, matieresData, typesNoteData, decoupagesData){
    return {
        notes: notesData, inscriptions: inscriptionsData, classes: classesData, niveaux: niveauxData,
        matieres: matieresData, typesNote: typesNoteData, decoupages: decoupagesData,
        filterNiveau:'', filterClasse:'', filterSemestre:'', filterMatiere:'', search:'',
        page:1, perPage:10,
        modal:false, editing:false, submitting:false,
        detail:null,
        form:{id:'', id_inscription:'', id_matiere:'', id_type_note:'', session:'Normale', note:''},
        bulk:{open:false, classeId:'', matiereId:'', typeNoteId:'', session:'Normale', rows:[], saving:false},

        matiereFilterOpts(){ return this.matieres.filter(m=>!this.filterSemestre||String(m.id_decoupage_annee)===String(this.filterSemestre)).map(m=>({v:m.id,l:m.libelle})); },

        get filtered(){
            const q = this.search.toLowerCase().trim();
            return this.notes.filter(n=>{
                if(this.filterMatiere && String(n.id_matiere)!==String(this.filterMatiere)) return false;
                if(this.filterSemestre && String(n.decoupage_id)!==String(this.filterSemestre)) return false;
                if(this.filterNiveau && String(n.niveau_id)!==String(this.filterNiveau)) return false;
                if(this.filterClasse && String(n.classe_id)!==String(this.filterClasse)) return false;
                if(q){
                    const etu = this.inscriptions.find(i=>i.id_etudiant===n.etu_id);
                    const nom = (etu? (etu.etu_nom+' '+etu.etu_prenom) : '').toLowerCase();
                    const mat = (n.matiere_libelle||'').toLowerCase();
                    if(!nom.includes(q) && !mat.includes(q)) return false;
                }
                return true;
            });
        },
        get grouped(){
            const g = {};
            for(const n of this.filtered){
                if(!n.etu_id) continue;
                (g[n.etu_id] ||= []).push(n);
            }
            return g;
        },
        get studentIds(){
            return Object.keys(this.grouped).map(Number).sort((a,b)=> this.studentName(a).localeCompare(this.studentName(b)));
        },
        get totalPages(){ return Math.max(1, Math.ceil(this.studentIds.length/this.perPage)); },
        get pagedStudentIds(){
            if(this.page>this.totalPages) this.page=this.totalPages;
            const start=(this.page-1)*this.perPage;
            return this.studentIds.slice(start, start+this.perPage);
        },
        get pageButtons(){
            const total=this.totalPages, current=this.page-1;
            const btn=i=>({n:i+1,key:'p'+i,ellipsis:false}), ell=k=>({key:k,ellipsis:true});
            if(total<=7) return Array.from({length:total},(_,i)=>btn(i));
            const r=[btn(0)];
            if(current>2) r.push(ell('e1'));
            const from=Math.min(Math.max(current-1,1),total-2), to=Math.min(Math.max(current+1,1),total-2);
            for(let i=from;i<=to;i++) r.push(btn(i));
            if(current<total-3) r.push(ell('e2'));
            r.push(btn(total-1));
            return r;
        },

        inscriptionOf(idEtu){ return this.inscriptions.find(i=>i.id_etudiant===idEtu); },
        studentName(idEtu){ const i=this.inscriptionOf(idEtu); return i? (i.etu_nom+' '+i.etu_prenom) : '—'; },
        studentClasse(idEtu){ const i=this.inscriptionOf(idEtu); if(!i) return '—'; const c=this.classes.find(c=>c.id===i.id_classe); return c?c.libelle:'—'; },
        initial(idEtu){ const i=this.inscriptionOf(idEtu); return i&&i.etu_prenom? i.etu_prenom.charAt(0).toUpperCase() : '?'; },
        moyEtudiant(notesArr){ if(!notesArr.length) return 0; return notesArr.reduce((s,n)=>s+Number(n.note||0),0)/notesArr.length; },
        decoupageLabel(id){ const d=this.decoupages.find(d=>d.id===id); return d?d.libelle:''; },

        showDetail(idEtu){
            const i = this.inscriptionOf(idEtu);
            const insIds = this.inscriptions.filter(x=>x.id_etudiant===idEtu).map(x=>x.id);
            const list = this.notes.filter(n=>insIds.includes(n.id_inscription))
                .slice()
                .sort((a,b)=> (a.matiere_libelle||'').localeCompare(b.matiere_libelle||'') || (a.type_libelle||'').localeCompare(b.type_libelle||''));
            this.detail = { etu: { nom: i?.etu_nom||'—', prenom: i?.etu_prenom||'' }, notes: list };
        },

        openCreate(){
            this.editing=false; this.submitting=false;
            this.form={id:'', id_inscription:'', id_matiere:'', id_type_note:this.typesNote[0]?.id||'', session:'Normale', note:''};
            this.modal=true;
        },
        openEdit(n){
            this.editing=true; this.submitting=false;
            this.form={id:n.id, id_inscription:n.id_inscription, id_matiere:n.id_matiere, id_type_note:n.id_type_note, session:n.session, note:n.note};
            this.modal=true;
        },
        submitForm(){
            if(!this.form.id_inscription || !this.form.id_matiere || !this.form.id_type_note || this.form.note===''){
                this.toast('Veuillez remplir tous les champs obligatoires', 'error'); return;
            }
            this.submitting=true;
            const url = this.editing ? '/notes/'+this.form.id : '/notes';
            const method = this.editing ? 'PUT' : 'POST';
            fetch(url, {
                method, headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
                body: JSON.stringify(this.form)
            }).then(async res=>{
                if(!res.ok){ const err=await res.json(); throw new Error(err.message||'Erreur'); }
                const result = await res.json();
                const saved = result.data;
                const flat = {
                    id: saved.id, id_inscription: saved.id_inscription, id_matiere: saved.id_matiere,
                    id_type_note: saved.id_type_note, note: saved.note, session: saved.session,
                    etu_id: this.inscriptions.find(i=>i.id===saved.id_inscription)?.id_etudiant,
                    classe_id: this.inscriptions.find(i=>i.id===saved.id_inscription)?.id_classe,
                    niveau_id: this.inscriptions.find(i=>i.id===saved.id_inscription)?.id_niveau,
                    matiere_libelle: this.matieres.find(m=>m.id===saved.id_matiere)?.libelle||'—',
                    decoupage_id: this.matieres.find(m=>m.id===saved.id_matiere)?.id_decoupage_annee,
                    type_libelle: this.typesNote.find(t=>t.id===saved.id_type_note)?.libelle||'—',
                };
                if(this.editing){
                    const idx=this.notes.findIndex(n=>n.id===flat.id);
                    if(idx!==-1) this.notes[idx]=flat;
                } else {
                    this.notes.unshift(flat);
                }
                this.modal=false; this.submitting=false;
                this.toast(this.editing?'Note modifiée':'Note enregistrée','success');
            }).catch(err=>{ this.submitting=false; this.toast(err.message,'error'); });
        },
        deleteNote(n){
            if(!confirm('Supprimer cette note ?')) return;
            fetch('/notes/'+n.id, { method:'DELETE', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'} })
                .then(async res=>{
                    if(!res.ok) throw new Error('Erreur lors de la suppression');
                    this.notes = this.notes.filter(x=>x.id!==n.id);
                    if(this.detail) this.detail.notes = this.detail.notes.filter(x=>x.id!==n.id);
                    this.toast('Note supprimée','success');
                }).catch(err=>this.toast(err.message,'error'));
        },

        openBulk(){
            this.bulk = { open:true, classeId:'', matiereId:'', typeNoteId:'', session:'Normale', rows:[], saving:false };
        },
        bulkMatieresFiltrees(){
            if(!this.bulk.classeId) return this.matieres;
            const c = this.classes.find(c=>String(c.id)===String(this.bulk.classeId));
            if(!c) return this.matieres;
            return this.matieres.filter(m=>String(m.id_niveau)===String(c.id_niveau) && String(m.id_filiere)===String(c.id_filiere));
        },
        rebuildBulkRows(){
            if(!this.bulk.classeId){ this.bulk.rows=[]; return; }
            const insList = this.inscriptions.filter(i=>String(i.id_classe)===String(this.bulk.classeId))
                .slice().sort((a,b)=>(a.etu_nom||'').localeCompare(b.etu_nom||''));
            this.bulk.rows = insList.map(i=>{
                let note='';
                if(this.bulk.matiereId){
                    const existing = this.notes.find(n=>n.id_inscription===i.id && String(n.id_matiere)===String(this.bulk.matiereId) && n.session===this.bulk.session);
                    if(existing) note = existing.note;
                }
                return { id_inscription:i.id, nom:i.etu_nom+' '+i.etu_prenom, numero:i.numero_inscription, note };
            });
        },
        saveBulk(){
            if(!this.bulk.classeId||!this.bulk.matiereId||!this.bulk.typeNoteId) return;
            this.bulk.saving=true;
            const payload = {
                id_matiere: this.bulk.matiereId, id_type_note: this.bulk.typeNoteId, session: this.bulk.session,
                notes: this.bulk.rows.map(r=>({ id_inscription: r.id_inscription, note: r.note===''?null:r.note })),
            };
            fetch('/notes/bulk', {
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
                body: JSON.stringify(payload)
            }).then(async res=>{
                if(!res.ok){ const err=await res.json(); throw new Error(err.message||'Erreur'); }
                location.reload();
            }).catch(err=>{ this.bulk.saving=false; this.toast(err.message,'error'); });
        },

        toast(message, type='info'){
            const colors={ success:{bg:'rgba(22,163,74,.95)'}, error:{bg:'rgba(239,68,68,.95)'}, warning:{bg:'rgba(245,158,11,.95)'}, info:{bg:'rgba(90,103,216,.95)'} };
            const style=colors[type]||colors.info;
            const t=document.createElement('div');
            t.style.cssText=`position:fixed;bottom:24px;left:50%;transform:translateX(-50%);padding:12px 24px;border-radius:12px;font-size:13px;font-weight:500;background:${style.bg};color:#fff;z-index:9999;box-shadow:0 8px 32px rgba(0,0,0,.12);max-width:90%`;
            t.textContent=message;
            document.body.appendChild(t);
            setTimeout(()=>{ t.style.opacity='0'; t.style.transition='all .3s ease'; setTimeout(()=>t.remove(),300); },3500);
        },
    };
}
</script>
@endpush
</x-app-layout>
