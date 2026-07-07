<x-app-layout title="Affectations Enseignants">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none;transition:all .15s}
.f-input:focus{background:#fff;box-shadow:0 0 0 2px var(--primary)44}
.tbl-th{font-size:11px;font-weight:600;color:#94A3B8;text-transform:uppercase;letter-spacing:.07em;padding:11px 16px;text-align:left}
.tbl-td{padding:13px 16px;font-size:13px;color:#475569}
.act-btn{width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;cursor:pointer;border:none;background:transparent}
.prog-bar{height:6px;border-radius:3px;background:#E2E8F0;overflow:hidden}
.prog-fill{height:100%;border-radius:3px;background:var(--primary);transition:width .3s}
.prog-fill.complete{background:#16A34A}
.prog-fill.warn{background:#F59E0B}

/* Recherche déroulante (select searchable) */
.ss-drop{position:absolute;top:100%;left:0;right:0;margin-top:4px;max-height:220px;overflow-y:auto;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.12);border:1px solid #E2E8F0;z-index:30}
.ss-item{padding:9px 14px;font-size:13px;color:#334155;cursor:pointer}
.ss-item:hover{background:#F1F5F9}
.ss-sel{background:color-mix(in srgb, var(--primary) 8%, white);color:var(--primary);font-weight:600}

/* Bouton outline "Voir détails" */
.btn-outline-indigo{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:8px;font-size:12px;font-weight:600;color:var(--primary);border:1.5px solid var(--primary);background:transparent;cursor:pointer;transition:all .15s}
.btn-outline-indigo:hover{background:color-mix(in srgb, var(--primary) 8%, white)}
.btn-outline-indigo:disabled{opacity:.4;cursor:not-allowed}

/* Modal détail enseignant */
.detail-header{background:var(--primary);border-radius:16px 16px 0 0;padding:18px 24px;display:flex;align-items:center;gap:14px}
.detail-avatar{width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0;overflow:hidden}
.detail-avatar img{width:100%;height:100%;object-fit:cover}
.aff-card{background:#fff;border-radius:12px;border:1px solid #E2E8F0;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.03);margin-bottom:12px}
.aff-card.complete{border-color:#BBF7D0}
.aff-icon-box{width:36px;height:36px;border-radius:10px;background:color-mix(in srgb, var(--primary) 10%, transparent);display:flex;align-items:center;justify-content:center;color:var(--primary);flex-shrink:0}
.badge-cls{display:inline-flex;padding:2px 10px;border-radius:999px;font-size:11px;font-weight:600;background:rgba(13,148,136,.12);color:#0d9488}
.badge-complete{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#16A34A;margin-top:6px}
</style>
@endpush

@php
$ensOpts     = $enseignants->map(fn($e) => ['v' => $e->id, 'l' => $e->prenom.' '.$e->nom.' ('.$e->grade.')']);
$matOpts     = $matieres->map(fn($m) => ['v' => $m->id, 'l' => $m->libelle]);
$classeOpts  = $classes->map(fn($c) => ['v' => $c->id, 'l' => $c->libelle]);
$groupedJson = $grouped->map(fn($g) => [
    'enseignant_id'  => $g['enseignant']?->id,
    'nom'            => $g['enseignant'] ? $g['enseignant']->prenom.' '.$g['enseignant']->nom : '—',
    'grade'          => $g['enseignant']?->grade ?? '',
    'url_photo'      => $g['enseignant']?->url_photo ?? '',
    'affectations'   => $g['affectations'],
    'total_quota'    => $g['total_quota'],
    'total_done'     => $g['total_done'],
]);
@endphp

<div x-data="affPage({{ $groupedJson }}, {{ $ensOpts }}, {{ $matOpts }}, {{ $classeOpts }})" class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Affectations Enseignants</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8">
                {{ $anneeActive ? 'Année scolaire : '.$anneeActive->libelle : 'Aucune année active' }}
                — <span x-text="grouped.length"></span> enseignant(s)
            </p>
        </div>
        <button @click="openCreate()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition" style="background:var(--primary)">
            <i class="ri-add-line text-base"></i> Nouvelle Affectation
        </button>
    </div>

    {{-- Tableau groupé --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div x-show="!grouped.length" class="py-16 text-center">
            <i class="ri-user-settings-line text-4xl" style="color:#CBD5E1"></i>
            <p class="mt-3 text-sm font-semibold" style="color:#64748B">Aucune affectation</p>
            <p class="text-xs mt-1" style="color:#94A3B8">Cliquez sur "Nouvelle Affectation" pour commencer.</p>
        </div>

        <div x-show="grouped.length">
            <table class="w-full">
                <thead class="border-b border-gray-100">
                    <tr>
                        <th class="tbl-th">Enseignant</th>
                        <th class="tbl-th">Affectations</th>
                        <th class="tbl-th">Heures totales</th>
                        <th class="tbl-th" style="min-width:170px">Progression</th>
                        <th class="tbl-th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(g, gi) in grouped" :key="gi">
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="tbl-td">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 overflow-hidden"
                                         style="background:color-mix(in srgb, var(--primary) 10%, transparent);color:var(--primary)">
                                        <template x-if="g.url_photo">
                                            <img :src="g.url_photo" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!g.url_photo">
                                            <span x-text="g.nom.split(' ').map(w=>w.charAt(0)).slice(0,2).join('').toUpperCase()"></span>
                                        </template>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-sm" style="color:#1E293B" x-text="g.nom"></p>
                                        <p class="text-xs" style="color:#94A3B8" x-text="g.grade"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="tbl-td">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:color-mix(in srgb, var(--primary) 8%, white);color:var(--primary)"
                                      x-text="g.affectations.length+' matière(s)'"></span>
                            </td>
                            <td class="tbl-td font-semibold"
                                :style="'color:'+(isComplete(g)?'#15803d':'#1E293B')"
                                x-text="g.total_quota>0 ? (g.total_done+'h / '+g.total_quota+'h') : '—'"></td>
                            <td class="tbl-td">
                                <template x-if="g.total_quota>0">
                                    <div class="flex items-center gap-2">
                                        <div class="prog-bar flex-1">
                                            <div class="prog-fill" :class="progressClass(g)" :style="'width:'+progressPct(g)+'%'"></div>
                                        </div>
                                        <span class="text-[11px] font-bold" :style="'color:'+(isComplete(g)?'#15803d':'var(--primary)')" x-text="progressPct(g)+'%'"></span>
                                    </div>
                                </template>
                                <template x-if="g.total_quota<=0">
                                    <span class="text-xs" style="color:#CBD5E1">—</span>
                                </template>
                            </td>
                            <td class="tbl-td">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="showDetail(gi)" class="btn-outline-indigo">
                                        <i class="ri-eye-line"></i> Voir détails
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal détail enseignant --}}
    <template x-if="detailModal !== null">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)" @click.self="detailModal=null">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col" style="max-height:85vh">
                <template x-if="detailGroup">
                    <div class="detail-header">
                        <div class="detail-avatar">
                            <template x-if="detailGroup.url_photo"><img :src="detailGroup.url_photo"></template>
                            <template x-if="!detailGroup.url_photo"><span x-text="detailGroup.nom.split(' ').map(w=>w.charAt(0)).slice(0,2).join('').toUpperCase()"></span></template>
                        </div>
                        <div class="flex-1">
                            <p class="text-white font-bold text-base" x-text="detailGroup.nom"></p>
                            <p class="text-xs" style="color:rgba(255,255,255,.75)" x-text="detailGroup.affectations.length+' affectation(s) — '+detailGroup.grade"></p>
                        </div>
                        <button @click="detailModal=null" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-white/10">
                            <i class="ri-close-line text-white text-lg"></i>
                        </button>
                    </div>
                </template>

                <div class="overflow-y-auto p-5" style="background:#F8FAFC">
                    <template x-if="detailGroup">
                        <template x-for="a in detailGroup.affectations" :key="a.id">
                            <div class="aff-card" :class="isAffComplete(a) ? 'complete' : ''">
                                <div class="flex items-start gap-3">
                                    <div class="aff-icon-box"><i class="ri-book-2-line"></i></div>
                                    <div class="flex-1">
                                        <p class="font-bold text-sm" style="color:#1E293B" x-text="a.matiere"></p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="badge-cls" x-text="a.classe"></span>
                                            <span x-show="a.montant_horaire>0" class="text-[11px]" style="color:#94A3B8"
                                                  x-text="Number(a.montant_horaire).toLocaleString('fr-FR')+' FCFA/h'"></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button @click="const ensId=detailGroup.enseignant_id; detailModal=null; openEditAff(a, ensId)" class="act-btn hover:bg-primary/5" style="color:var(--primary)" title="Modifier">
                                            <i class="ri-edit-2-line text-[15px]"></i>
                                        </button>
                                        <form :action="'/affectations/'+a.id" method="POST" style="display:inline" @submit.prevent="if(confirm('Supprimer cette affectation ?')) $el.submit()">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="act-btn hover:bg-red-50" style="color:#ef4444" title="Supprimer"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                                        </form>
                                    </div>
                                </div>

                                <template x-if="a.nombre_heure > 0">
                                    <div class="mt-3">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="text-xs font-bold" :style="'color:'+(isAffComplete(a)?'#15803d':'var(--primary)')">
                                                <span x-text="a.heures_done+'h'"></span>
                                                <span class="font-normal" style="color:#94A3B8" x-text="' / '+a.nombre_heure+'h'"></span>
                                            </span>
                                            <span class="text-xs font-bold" :style="'color:'+(isAffComplete(a)?'#15803d':'var(--primary)')" x-text="affPct(a)+'%'"></span>
                                        </div>
                                        <div class="prog-bar">
                                            <div class="prog-fill" :class="affProgressClass(a)" :style="'width:'+affPct(a)+'%'"></div>
                                        </div>
                                        <div class="badge-complete" x-show="isAffComplete(a)">
                                            <i class="ri-checkbox-circle-fill"></i> Quota atteint
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!(a.nombre_heure > 0)">
                                    <p class="text-[11px] italic mt-2" style="color:#CBD5E1">Aucun quota défini</p>
                                </template>
                            </div>
                        </template>
                    </template>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal création / édition --}}
    <template x-if="modal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="editing?'Modifier Affectation':'Nouvelle Affectation'"></h2>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <form :action="editing?'/affectations/'+form.id:'/affectations'" method="POST" @submit="submitting=true" class="px-6 py-5 space-y-4">
                    @csrf
                    <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="f-label">Enseignant <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(ensOpts, form.id_enseignant, 'Sélectionner enseignant...')"
                             class="relative" @click.outside="open=false">
                            <input type="hidden" name="id_enseignant" :value="v">
                            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" :placeholder="ph" autocomplete="off">
                            <div x-show="open" class="ss-drop">
                                <div x-show="!filtered.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucun résultat</div>
                                <template x-for="o in filtered" :key="o.v">
                                    <div @click="select(o)" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="f-label">Matière <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(matOpts, form.id_matiere, 'Sélectionner matière...')"
                             class="relative" @click.outside="open=false">
                            <input type="hidden" name="id_matiere" :value="v">
                            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" :placeholder="ph" autocomplete="off">
                            <div x-show="open" class="ss-drop">
                                <div x-show="!filtered.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucun résultat</div>
                                <template x-for="o in filtered" :key="o.v">
                                    <div @click="select(o)" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="f-label">Classe <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(classeOpts, form.id_classe, 'Sélectionner classe...')"
                             class="relative" @click.outside="open=false">
                            <input type="hidden" name="id_classe" :value="v">
                            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" :placeholder="ph" autocomplete="off">
                            <div x-show="open" class="ss-drop">
                                <div x-show="!filtered.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucun résultat</div>
                                <template x-for="o in filtered" :key="o.v">
                                    <div @click="select(o)" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="f-label">Nombre d'heures <span style="color:#EF4444">*</span></label>
                            <input type="number" name="nombre_heure" :value="form.nombre_heure" class="f-input" min="1" step="0.5" required>
                        </div>
                        <div>
                            <label class="f-label">Montant horaire (FCFA/h) <span style="color:#EF4444">*</span></label>
                            <input type="number" name="montant_horaire" :value="form.montant_horaire" class="f-input" min="0" required>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="modal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                        <button type="submit" :disabled="submitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:var(--primary)" x-text="submitting?'...':'Enregistrer'"></button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function affPage(groupedData, ensOpts, matOpts, classeOpts){
    return {
        grouped: groupedData,
        ensOpts, matOpts, classeOpts,
        modal: false, editing: false, submitting: false,
        detailModal: null,
        form: {id:'', id_enseignant:'', id_matiere:'', id_classe:'', nombre_heure:'', montant_horaire:''},

        get detailGroup(){
            return this.detailModal !== null ? this.grouped[this.detailModal] : null;
        },

        showDetail(gi){ this.detailModal = gi; },

        progressPct(g){
            return g.total_quota > 0 ? Math.min(100, Math.round(g.total_done / g.total_quota * 100)) : 0;
        },
        isComplete(g){
            return g.total_quota > 0 && g.total_done >= g.total_quota;
        },
        progressClass(g){
            if (this.isComplete(g)) return 'complete';
            return this.progressPct(g) > 70 ? 'warn' : '';
        },

        affPct(a){
            return a.nombre_heure > 0 ? Math.min(100, Math.round(a.heures_done / a.nombre_heure * 100)) : 0;
        },
        isAffComplete(a){
            return a.nombre_heure > 0 && a.heures_done >= a.nombre_heure;
        },
        affProgressClass(a){
            if (this.isAffComplete(a)) return 'complete';
            return this.affPct(a) > 70 ? 'warn' : '';
        },

        openCreate(ensId){
            this.editing=false; this.submitting=false;
            this.form={id:'', id_enseignant: ensId||'', id_matiere:'', id_classe:'', nombre_heure:'', montant_horaire:''};
            this.modal=true;
        },
        openEditAff(a, ensId){
            this.editing=true; this.submitting=false;
            this.form={
                id: a.id,
                id_enseignant: ensId,
                id_matiere: a.id_matiere || '',
                id_classe: a.id_classe || '',
                nombre_heure: a.nombre_heure,
                montant_horaire: a.montant_horaire
            };
            this.modal=true;
        },
    }
}

// Composant "select" avec recherche (Enseignant / Matière / Classe)
function sSelect(opts, initial, placeholder){
    return {
        opts: opts || [],
        v: initial ? String(initial) : '',
        s: '',
        ph: placeholder,
        open: false,
        init(){
            if (this.v) {
                const found = this.opts.find(o => String(o.v) === this.v);
                if (found) this.s = found.l;
            }
        },
        get filtered(){
            const q = (this.s || '').toLowerCase().trim();
            if (!q) return this.opts;
            return this.opts.filter(o => o.l.toLowerCase().includes(q));
        },
        select(o){
            this.v = String(o.v);
            this.s = o.l;
            this.open = false;
        }
    }
}
</script>
@endpush
</x-app-layout>