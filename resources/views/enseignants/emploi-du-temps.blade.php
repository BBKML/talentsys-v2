<x-app-layout title="Emploi du Temps">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none;transition:all .15s}
.f-input:focus{background:#fff;box-shadow:0 0 0 2px var(--primary)44}

/* Header / navigation semaine */
.edt-title{font-size:22px;font-weight:700;color:#1E293B}
.edt-subtitle{font-size:13px;color:#94A3B8;margin-top:2px}
.week-nav{display:flex;align-items:center;background:#fff;border:1px solid #E2E8F0;border-radius:8px;overflow:hidden}
.week-nav button{width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;background:transparent;border:none;cursor:pointer;color:#475569}
.week-nav button:hover{background:#F8FAFC}
.week-nav span{font-size:13px;font-weight:700;color:#1E293B;padding:0 6px;white-space:nowrap}

/* Toggle Classe / Salle / Enseignant */
.edt-toggle{display:inline-flex;background:#F1F5F9;border:1px solid #E2E8F0;border-radius:9px;padding:3px}
.edt-toggle button{display:inline-flex;align-items:center;gap:6px;padding:8px 13px;border-radius:7px;border:none;background:transparent;font-size:12px;font-weight:600;color:#475569;cursor:pointer;transition:all .15s}
.edt-toggle button.active{background:var(--primary);color:#fff}

/* Select natif stylé */
.edt-select{height:40px;padding:0 12px;border:1px solid #E2E8F0;border-radius:8px;background:#fff;font-size:13px;color:#334155;outline:none;min-width:170px}
.edt-select:focus{border-color:var(--primary)}

/* Boutons actions */
.edt-btn-outline{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:10px;border:1px solid #E2E8F0;background:#fff;font-size:13px;font-weight:600;color:#334155;cursor:pointer;transition:all .15s}
.edt-btn-outline:hover{background:#F8FAFC}
.edt-btn-primary{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:9999px;border:none;background:var(--primary);color:#fff;font-size:13px;font-weight:700;cursor:pointer;transition:all .15s}
.edt-btn-primary:hover{opacity:.92}
.edt-btn-primary:disabled{background:#E2E8F0;color:#94A3B8;cursor:not-allowed}

/* Grille */
.edt-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:90px 20px;color:#CBD5E1}
.edt-grid-wrap{overflow:auto}
.edt-grid{display:grid}
.edt-head-cell{background:#F8FAFC;border-bottom:1px solid #E2E8F0;border-right:1px solid #E2E8F0;padding:11px 8px;font-size:12px;font-weight:700;color:#1E293B;text-align:center;white-space:nowrap}
.edt-head-label{color:#94A3B8;font-weight:600;text-align:left}
.edt-head-cell.weekend{color:#F57C00}
.edt-hourcol{border-right:1px solid #E2E8F0}
.edt-hour-label{height:60px;box-sizing:border-box;display:flex;align-items:flex-start;padding:6px 8px;font-size:10.5px;color:#94A3B8;border-bottom:1px solid #F1F5F9;white-space:nowrap}
.edt-daycol{position:relative;border-right:1px solid #E2E8F0;width:160px}
.edt-daycol.weekend{background:rgba(245,158,11,.04)}
.edt-slot{position:absolute;left:0;right:0;height:60px;box-sizing:border-box;border-bottom:1px solid #F1F5F9;display:flex;align-items:center;justify-content:center;cursor:pointer}
.edt-plus{color:#CBD5E1;font-size:15px}
.edt-event{position:absolute;left:2px;right:2px;border-radius:8px;padding:4px 6px;overflow:hidden;cursor:pointer;box-sizing:border-box}
.edt-event-mat{font-size:11px;font-weight:700;line-height:1.25}
.edt-event-sub{font-size:10px;color:#94A3B8;display:flex;align-items:center;gap:3px;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

/* Print */
.print-only{display:none}
@media print{
    .print-only{display:block}
    body *{visibility:hidden}
    #edtPrintArea, #edtPrintArea *{visibility:visible}
    #edtPrintArea{position:absolute;left:0;top:0;width:100%}
}
</style>
@endpush

@php
$affJson = $affectations->map(fn($a) => [
    'id'         => $a->id,
    'id_classe'  => $a->id_classe,
    'label'      => ($a->enseignant ? $a->enseignant->nom.' '.$a->enseignant->prenom : '?').' — '.($a->matiere?->libelle ?? '?').' ('.($a->classe?->libelle ?? '?').')',
]);

$salleJson = $salles->map(fn($s) => ['id' => $s->id, 'label' => $s->code ?: ($s->libelle ?: '—')]);

$ensJson = $enseignants->map(fn($e) => ['id' => $e->id, 'label' => trim($e->nom.' '.$e->prenom)]);

$classesJson = $classes->map(fn($c) => ['id' => $c->id, 'libelle' => $c->libelle, 'id_niveau' => $c->id_niveau]);

$niveauxJson = $niveaux->map(fn($n) => ['id' => $n->id, 'libelle' => $n->libelle]);

$seancesJson = $seances->map(function ($s) {
    $aff = $s->affectation;
    $ens = $aff?->enseignant;
    $ensLabel = $ens ? (($ens->prenom ? mb_substr($ens->prenom, 0, 1).'. ' : '').$ens->nom) : null;
    $salleLabel = $s->salle ? ($s->salle->code ?: $s->salle->libelle) : null;
    return [
        'id'        => $s->id,
        'aff_id'    => $s->id_affectation_enseignant,
        'salle_id'  => $s->id_salle,
        'classe_id' => $aff?->id_classe,
        'ens_id'    => $aff?->id_enseignant,
        'debut'     => $s->date_heure_debut?->format('Y-m-d\TH:i:s'),
        'fin'       => $s->date_heure_fin?->format('Y-m-d\TH:i:s'),
        'motif'     => $s->motif_modification,
        'matiere'   => $aff?->matiere?->libelle ?? '—',
        'salle_label' => $salleLabel,
        'ens_label'   => $ensLabel,
    ];
});

$hasActiveYear = (bool) $anneeActive;
@endphp

<div x-data="edtPage({{ $seancesJson }}, {{ $affJson }}, {{ $salleJson }}, {{ $ensJson }}, {{ $classesJson }}, {{ $niveauxJson }}, {{ $hasActiveYear ? 'true' : 'false' }})" class="space-y-4">

    {{-- Ligne 1 : titre + navigation semaine --}}
    <div class="flex items-center gap-6 flex-wrap">
        <div>
            <h1 class="edt-title">Emploi du Temps</h1>
            <p class="edt-subtitle" x-text="subtitle()"></p>
        </div>
        <template x-if="selectedClasse">
            <div class="week-nav">
                <button type="button" @click="prevWeek()"><i class="ri-arrow-left-s-line"></i></button>
                <span x-text="weekLabel()"></span>
                <button type="button" @click="nextWeek()"><i class="ri-arrow-right-s-line"></i></button>
            </div>
        </template>
    </div>

    {{-- Ligne 2 : toggle + filtres --}}
    <div class="flex items-center justify-end flex-wrap gap-2.5">
        <div class="edt-toggle">
            <button type="button" @click="viewMode=0" :class="viewMode===0?'active':''"><i class="ri-book-2-line"></i> Classe</button>
            <button type="button" @click="viewMode=1" :class="viewMode===1?'active':''"><i class="ri-door-open-line"></i> Salle</button>
            <button type="button" @click="viewMode=2" :class="viewMode===2?'active':''"><i class="ri-user-line"></i> Enseignant</button>
        </div>

        <template x-if="viewMode===0">
            <div class="flex items-center gap-2.5 flex-wrap">
                <select class="edt-select" x-model="filterNiveau" @change="onNiveauChange()">
                    <option value="">Tous les niveaux</option>
                    <template x-for="n in niveaux" :key="n.id"><option :value="n.id" x-text="n.libelle"></option></template>
                </select>
                <select class="edt-select" x-model="selectedClasse">
                    <option value="">Choisir une classe</option>
                    <template x-for="c in classesDispo" :key="c.id"><option :value="c.id" x-text="c.libelle"></option></template>
                </select>
            </div>
        </template>
        <template x-if="viewMode===1">
            <select class="edt-select" x-model="selectedSalle">
                <option value="">Choisir une salle</option>
                <template x-for="s in salleOpts" :key="s.id"><option :value="s.id" x-text="s.label"></option></template>
            </select>
        </template>
        <template x-if="viewMode===2">
            <select class="edt-select" x-model="selectedEnseignant">
                <option value="">Choisir un enseignant</option>
                <template x-for="e in ensOpts" :key="e.id"><option :value="e.id" x-text="e.label"></option></template>
            </select>
        </template>
    </div>

    {{-- Ligne 3 : actions --}}
    <div class="flex items-center justify-end gap-2.5">
        <button type="button" @click="printGrid()" class="edt-btn-outline">
            <i class="ri-printer-line"></i> Imprimer
        </button>
        <button type="button" @click="openCreate()" :disabled="!canAddCours()" class="edt-btn-primary">
            <i class="ri-add-line"></i> Ajouter Cours
        </button>
    </div>

    {{-- État vide --}}
    <template x-if="isEmptySelection">
        <div class="edt-empty">
            <i class="ri-calendar-todo-line" style="font-size:56px"></i>
            <p class="mt-4 text-sm" style="color:#94A3B8" x-text="emptyMessage()"></p>
        </div>
    </template>

    {{-- Grille --}}
    <template x-if="!isEmptySelection">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100" id="edtPrintArea">
            <div class="print-only p-5 pb-0">
                <h2 class="text-base font-bold" style="color:#1E293B" x-text="'Emploi du Temps - ' + subtitle()"></h2>
                <p class="text-xs" style="color:#94A3B8" x-text="'Semaine du ' + fmtFull(weekStart) + ' au ' + fmtFull(dayAt(6))"></p>
            </div>
            <div class="edt-grid-wrap p-5">
                <div class="edt-grid" style="grid-template-columns:110px repeat(7,160px)">
                    <div class="edt-head-cell edt-head-label">Horaire</div>
                    <template x-for="i in 7" :key="'h'+i">
                        <div class="edt-head-cell" :class="(i-1)>=5?'weekend':''" x-text="fmtDay(i-1)"></div>
                    </template>

                    <div class="edt-hourcol">
                        <template x-for="(cr, hi) in creneaux" :key="'hr'+hi">
                            <div class="edt-hour-label" x-text="cr.libelle"></div>
                        </template>
                    </div>

                    <template x-for="i in 7" :key="'d'+i">
                        <div class="edt-daycol" :class="(i-1)>=5?'weekend':''" :style="'height:'+(creneaux.length*60)+'px'">
                            <template x-for="(cr, hi) in creneaux" :key="'s'+i+'-'+hi">
                                <div class="edt-slot" :style="'top:'+(hi*60)+'px'" @click="openSlotClick(i-1,hi)">
                                    <span class="edt-plus" x-show="!slotCovered(i-1,hi)"><i class="ri-add-line"></i></span>
                                </div>
                            </template>
                            <template x-for="ev in dayEvents(i-1)" :key="ev.id">
                                <div class="edt-event" :style="eventStyle(ev) + eventColorStyle(i-1)" @click.stop="showSlotMenu(ev)">
                                    <p class="edt-event-mat" :style="eventTextStyle(i-1)" x-text="ev.matiere"></p>
                                    <p class="edt-event-sub" x-show="ev.salle_label"><i class="ri-door-line"></i><span x-text="ev.salle_label"></span></p>
                                    <p class="edt-event-sub" x-show="ev.ens_label"><i class="ri-user-line"></i><span x-text="ev.ens_label"></span></p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    {{-- Popup Modifier / Supprimer --}}
    <template x-if="slotMenu">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.35)" @click.self="slotMenu=null">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden" style="width:260px">
                <button type="button" @click="const s=slotMenu; slotMenu=null; openEdit(s)" class="w-full flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 text-left">
                    <i class="ri-edit-2-line" style="color:#3b82f6"></i>
                    <span class="text-sm font-medium" style="color:#1E293B">Modifier</span>
                </button>
                <form :action="'/emploi-du-temps/'+slotMenu.id" method="POST" @submit.prevent="if(confirm('Supprimer ce cours ?')) $el.submit()" class="border-t border-slate-100">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full flex items-center gap-3 px-5 py-3.5 hover:bg-red-50 text-left">
                        <i class="ri-delete-bin-2-line" style="color:#ef4444"></i>
                        <span class="text-sm font-medium" style="color:#ef4444">Supprimer</span>
                    </button>
                </form>
            </div>
        </div>
    </template>

    {{-- Modal Ajouter / Modifier Cours --}}
    <template x-if="modal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg" style="max-height:90vh;overflow-y:auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="editing?'Modifier le Cours':'Ajouter un Cours'"></h2>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <form :action="editing?'/emploi-du-temps/'+form.id:'/emploi-du-temps'" method="POST" @submit="submitting=true" class="px-6 py-5 space-y-4">
                    @csrf
                    <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="date_heure_debut" :value="debutFull">
                    <input type="hidden" name="date_heure_fin" :value="finFull">

                    <div>
                        <label class="f-label">Affectation (Enseignant — Matière — Classe) <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(affOptsForModal().map(a=>({v:a.id,l:a.label})), form.aff_id, 'Sélectionner...')"
                             class="relative" @click.outside="open=false">
                            <input type="hidden" name="id_affectation_enseignant" :value="v">
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
                        <label class="f-label">Salle <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(salleOpts.map(s=>({v:s.id,l:s.label})), form.salle_id, 'Sélectionner salle...')"
                             class="relative" @click.outside="open=false">
                            <input type="hidden" name="id_salle" :value="v">
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
                        <label class="f-label">Date <span style="color:#EF4444">*</span></label>
                        <input type="date" x-model="form.date" class="f-input" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="f-label">Heure début <span style="color:#EF4444">*</span></label>
                            <input type="time" x-model="form.heure_debut" class="f-input" required>
                        </div>
                        <div>
                            <label class="f-label">Heure fin <span style="color:#EF4444">*</span></label>
                            <input type="time" x-model="form.heure_fin" class="f-input" required>
                        </div>
                    </div>

                    <div>
                        <label class="f-label">Motif de modification</label>
                        <textarea x-model="form.motif" name="motif_modification" rows="2" class="f-input" placeholder="Laisser vide si aucune modification"></textarea>
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
function edtPage(seancesData, affOpts, salleOpts, ensOpts, classesData, niveauxData, hasActiveYear){
    const FIRST_HOUR = 7, LAST_HOUR = 21, SLOT_H = 60;
    const JOURS = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
    const pad2 = n => String(n).padStart(2,'0');
    const creneaux = [];
    for (let h = FIRST_HOUR; h < LAST_HOUR; h++) {
        creneaux.push({ libelle: pad2(h)+'h00 – '+pad2(h+1)+'h00', heure_debut: pad2(h)+':00', heure_fin: pad2(h+1)+':00' });
    }

    return {
        seances: seancesData, affOpts, salleOpts, ensOpts, classesAll: classesData, niveaux: niveauxData,
        hasActiveYear, creneaux,
        filterNiveau: '', selectedClasse: '', viewMode: 0, selectedSalle: '', selectedEnseignant: '',
        weekStart: null,
        modal: false, editing: false, submitting: false, slotMenu: null,
        form: { id:'', aff_id:'', salle_id:'', date:'', heure_debut:'', heure_fin:'', motif:'' },
        primaryHex: 'var(--primary)', primaryRgb: {r:90,g:103,b:216},

        init(){
            this.weekStart = this.mondayOf(new Date());
            this.trySelectFirstClasse();
            const cssPrimary = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
            if (cssPrimary) {
                this.primaryHex = cssPrimary;
                this.primaryRgb = this.hexToRgb(cssPrimary);
            }
        },
        hexToRgb(hex){
            let h = (hex || '').replace('#', '').trim();
            if (h.length === 3) h = h.split('').map(c => c+c).join('');
            const num = parseInt(h, 16);
            if (isNaN(num) || h.length !== 6) return {r:90,g:103,b:216};
            return { r: (num>>16)&255, g: (num>>8)&255, b: num&255 };
        },
        eventColorStyle(i){
            if (i >= 5) return 'background:rgba(245,158,11,.10);border:1px solid rgba(245,158,11,.4);';
            const {r,g,b} = this.primaryRgb;
            return `background:rgba(${r},${g},${b},.10);border:1px solid rgba(${r},${g},${b},.35);`;
        },
        eventTextStyle(i){
            return i >= 5 ? 'color:#F57C00' : ('color:'+this.primaryHex);
        },
        mondayOf(d){
            const dt = new Date(d.getFullYear(), d.getMonth(), d.getDate());
            const day = dt.getDay();
            dt.setDate(dt.getDate() + (day===0 ? -6 : 1-day));
            return dt;
        },
        get classesDispo(){
            return this.filterNiveau ? this.classesAll.filter(c => String(c.id_niveau)===String(this.filterNiveau)) : this.classesAll;
        },
        trySelectFirstClasse(){
            const dispo = this.classesDispo;
            if (!this.selectedClasse && dispo.length) this.selectedClasse = dispo[0].id;
            else if (this.selectedClasse && dispo.length && !dispo.some(c => String(c.id)===String(this.selectedClasse))) this.selectedClasse = dispo[0].id;
        },
        onNiveauChange(){ this.selectedClasse=''; this.trySelectFirstClasse(); },
        get selectedClasseObj(){ return this.classesAll.find(c => String(c.id)===String(this.selectedClasse)) || null; },
        prevWeek(){ this.weekStart = new Date(this.weekStart.getTime() - 7*86400000); },
        nextWeek(){ this.weekStart = new Date(this.weekStart.getTime() + 7*86400000); },
        dayAt(i){ return new Date(this.weekStart.getTime() + i*86400000); },
        fmtDay(i){ const d=this.dayAt(i); return JOURS[i]+' '+pad2(d.getDate())+'/'+pad2(d.getMonth()+1); },
        fmtFull(d){ return pad2(d.getDate())+'/'+pad2(d.getMonth()+1)+'/'+d.getFullYear(); },
        weekLabel(){ return 'Sem. '+this.weekStart.getDate()+'/'+(this.weekStart.getMonth()+1)+'/'+this.weekStart.getFullYear(); },
        subtitle(){ const c=this.selectedClasseObj; return c ? 'Classe : '+c.libelle : 'Sélectionnez une classe'; },
        get isEmptySelection(){
            if (this.viewMode===0) return !this.selectedClasse;
            if (this.viewMode===1) return !this.selectedSalle;
            return !this.selectedEnseignant;
        },
        emptyMessage(){
            if (this.viewMode===1) return 'Sélectionnez une salle';
            if (this.viewMode===2) return 'Sélectionnez un enseignant';
            return 'Sélectionnez un niveau puis une classe';
        },
        canAddCours(){ return this.hasActiveYear && this.viewMode===0 && !!this.selectedClasse; },
        dayEvents(i){
            const day=this.dayAt(i), y=day.getFullYear(), m=day.getMonth(), d=day.getDate();
            return this.seances.filter(s => {
                if (!s.debut) return false;
                const deb=new Date(s.debut);
                if (deb.getFullYear()!==y||deb.getMonth()!==m||deb.getDate()!==d) return false;
                if (this.viewMode===0) return this.selectedClasse && String(s.classe_id)===String(this.selectedClasse);
                if (this.viewMode===1) return this.selectedSalle && String(s.salle_id)===String(this.selectedSalle);
                return this.selectedEnseignant && String(s.ens_id)===String(this.selectedEnseignant);
            });
        },
        slotCovered(i, hi){
            const h = FIRST_HOUR + hi;
            return this.dayEvents(i).some(s => {
                const deb=new Date(s.debut), fin=new Date(s.fin);
                const dm=deb.getHours()*60+deb.getMinutes(), fm=fin.getHours()*60+fin.getMinutes();
                return dm < (h+1)*60 && fm > h*60;
            });
        },
        eventStyle(s){
            const deb=new Date(s.debut), fin=new Date(s.fin);
            const startMin=deb.getHours()*60+deb.getMinutes(), endMin=fin.getHours()*60+fin.getMinutes();
            const firstMin=FIRST_HOUR*60, lastMin=LAST_HOUR*60;
            const cStart=Math.min(Math.max(startMin,firstMin),lastMin);
            const cEnd=Math.min(Math.max(endMin,firstMin),lastMin);
            const top=(cStart-firstMin)/60*SLOT_H;
            const height=Math.max((cEnd-cStart)/60*SLOT_H, 4);
            return 'top:'+top+'px;height:'+height+'px;';
        },
        isoDate(d){ return d.getFullYear()+'-'+pad2(d.getMonth()+1)+'-'+pad2(d.getDate()); },
        openSlotClick(i, hi){
            if (!this.hasActiveYear || this.viewMode!==0 || !this.selectedClasse) return;
            if (this.slotCovered(i, hi)) return;
            this.openCreate(this.dayAt(i), this.creneaux[hi]);
        },
        affOptsForModal(){
            if (!this.editing && this.selectedClasse) return this.affOpts.filter(a => String(a.id_classe)===String(this.selectedClasse));
            return this.affOpts;
        },
        openCreate(dayInit, creneauInit){
            this.editing=false; this.submitting=false; this.slotMenu=null;
            const affDispo = this.affOptsForModal();
            this.form = {
                id:'',
                aff_id: affDispo.length ? affDispo[0].id : '',
                salle_id: this.salleOpts.length ? this.salleOpts[0].id : '',
                date: dayInit ? this.isoDate(dayInit) : '',
                heure_debut: creneauInit ? creneauInit.heure_debut : '',
                heure_fin: creneauInit ? creneauInit.heure_fin : '',
                motif: ''
            };
            this.modal = true;
        },
        openEdit(row){
            this.editing=true; this.submitting=false; this.slotMenu=null;
            const deb = row.debut ? new Date(row.debut) : null;
            const fin = row.fin ? new Date(row.fin) : null;
            this.form = {
                id: row.id,
                aff_id: row.aff_id,
                salle_id: row.salle_id ?? '',
                date: deb ? this.isoDate(deb) : '',
                heure_debut: deb ? pad2(deb.getHours())+':'+pad2(deb.getMinutes()) : '',
                heure_fin: fin ? pad2(fin.getHours())+':'+pad2(fin.getMinutes()) : '',
                motif: row.motif || ''
            };
            this.modal = true;
        },
        get debutFull(){ return (this.form.date && this.form.heure_debut) ? this.form.date+'T'+this.form.heure_debut+':00' : ''; },
        get finFull(){ return (this.form.date && this.form.heure_fin) ? this.form.date+'T'+this.form.heure_fin+':00' : ''; },
        showSlotMenu(s){ if (!this.hasActiveYear) return; this.slotMenu = s; },
        printGrid(){ window.print(); },
    };
}
</script>
@endpush
</x-app-layout>
