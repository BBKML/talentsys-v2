<x-app-layout title="Moyennes">
@push('styles')
<style>
.f-select{width:100%;padding:10px 12px;background:#fff;border:1px solid #E2E8F0;border-radius:8px;font-size:13px;color:#1E293B;outline:none}
.f-select:focus{border-color:#5A67D8}
.flt-select{height:38px;padding:0 12px;border:1px solid #E2E8F0;border-radius:8px;background:#fff;font-size:13px;color:#334155;min-width:190px;outline:none}
.flt-select:focus{border-color:#5A67D8}
.f-table-container{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #E2E8F0;overflow:hidden}
.f-table-header th{padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.06em;background:#F8FAFC;border-bottom:1px solid #E2E8F0}
.f-table-row td{padding:12px 16px;font-size:13px;color:#334155;border-bottom:1px solid #F1F5F9;vertical-align:middle}
.f-table-row:hover{background:#F8FAFC}
.f-badge{display:inline-flex;padding:4px 10px;border-radius:8px;font-size:13px;font-weight:700}
.ss-drop{position:absolute;top:100%;left:0;right:0;margin-top:4px;max-height:220px;overflow-y:auto;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.12);border:1px solid #E2E8F0;z-index:30}
.ss-item{padding:9px 14px;font-size:13px;color:#334155;cursor:pointer}
.ss-item:hover{background:#F1F5F9}
.ss-sel{background:#EEF2FF;color:#5A67D8;font-weight:600}
.moy-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:90px 20px;text-align:center}
</style>
@endpush

@php
$classesJson      = $classes->map(fn($c) => ['id' => $c->id, 'libelle' => $c->libelle, 'id_niveau' => $c->id_niveau]);
$niveauxJson      = $niveaux->map(fn($n) => ['id' => $n->id, 'libelle' => $n->libelle]);
$matieresJson     = $matieres->map(fn($m) => ['id' => $m->id, 'libelle' => $m->libelle, 'id_decoupage_annee' => $m->id_decoupage_annee]);
$typesNoteJson    = $typesNote->map(fn($t) => ['id' => $t->id, 'libelle' => $t->libelle, 'pourcentage' => $t->pourcentage]);
$decoupagesJson   = $decoupages->map(fn($d) => ['id' => $d->id, 'libelle' => $d->libelle]);
$inscriptionsJson = $inscriptions->map(fn($i) => [
    'id' => $i->id, 'numero_inscription' => $i->numero_inscription, 'id_etudiant' => $i->id_etudiant,
    'etu_nom' => $i->etudiant?->nom ?? '?', 'etu_prenom' => $i->etudiant?->prenom ?? '', 'id_classe' => $i->id_classe,
]);
$notesJson = $notes->map(fn($n) => [
    'id_inscription' => $n->id_inscription, 'id_matiere' => $n->id_matiere,
    'id_type_note' => $n->id_type_note, 'note' => $n->note, 'session' => $n->session,
]);
$moyennesSavedJson = $moyennesSaved->map(fn($m) => ['id_inscription' => $m->id_inscription, 'id_matiere' => $m->id_matiere, 'moyenne' => $m->moyenne]);
$hasActiveYear = (bool) $anneeActive;
@endphp

<div x-data="moyennesPage({{ $classesJson }}, {{ $niveauxJson }}, {{ $matieresJson }}, {{ $typesNoteJson }}, {{ $decoupagesJson }}, {{ $inscriptionsJson }}, {{ $notesJson }}, {{ $moyennesSavedJson }}, {{ $hasActiveYear ? 'true' : 'false' }})" class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Moyennes</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8"><span x-text="rows.length"></span> ligne(s) — calculées depuis les notes</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="location.reload()" title="Actualiser les données" class="act-btn hover:bg-gray-100" style="color:#64748B;border:1.5px solid #E2E8F0;width:38px;height:38px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center">
                <i class="ri-refresh-line"></i>
            </button>
            <button @click="calculer()" :disabled="!hasActiveYear || !filterClasse || calculating"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition disabled:opacity-50"
                    style="background:#5A67D8">
                <i class="ri-calculator-line text-base" x-show="!calculating"></i>
                <span x-show="calculating" style="width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;display:inline-block;animation:spin .7s linear infinite"></span>
                <span x-text="calculating?'Calcul...':'Calculer & Sauvegarder'"></span>
            </button>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="flex items-center flex-wrap gap-3">
        <select class="flt-select" x-model="filterNiveau" @change="filterClasse=''">
            <option value="">Tous niveaux</option>
            <template x-for="n in niveaux" :key="n.id"><option :value="n.id" x-text="n.libelle"></option></template>
        </select>
        <select class="flt-select" x-model="filterClasse">
            <option value="">Sélectionner une classe</option>
            <template x-for="c in classesFiltrees" :key="c.id"><option :value="c.id" x-text="c.libelle"></option></template>
        </select>
        <select class="flt-select" x-model="filterSemestre" @change="filterMatiere=''">
            <option value="">Tous les semestres</option>
            <template x-for="d in decoupages" :key="d.id"><option :value="d.id" x-text="d.libelle"></option></template>
        </select>
        <div class="relative" style="min-width:220px" x-data="sSelect(matieresDisponibles().map(m=>({v:m.id,l:m.libelle})), filterMatiere, 'Toutes les matières')" @click.outside="open=false">
            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="flt-select" style="min-width:220px" :placeholder="ph" autocomplete="off">
            <div x-show="open" class="ss-drop">
                <div class="ss-item" style="color:#94A3B8" @click="v='';s='';open=false;filterMatiere=''">Toutes les matières</div>
                <template x-for="o in filtered" :key="o.v">
                    <div @click="select(o); filterMatiere=o.v" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                </template>
            </div>
        </div>
        <button x-show="filterNiveau||filterClasse||filterSemestre||filterMatiere" @click="filterNiveau='';filterClasse='';filterSemestre='';filterMatiere=''"
                class="text-xs font-semibold flex items-center gap-1" style="color:#64748B">
            <i class="ri-close-line"></i> Réinitialiser
        </button>
    </div>

    {{-- État vide : pas de classe --}}
    <template x-if="!filterClasse">
        <div class="moy-empty">
            <i class="ri-function-line" style="font-size:56px;color:#CBD5E1"></i>
            <p class="mt-4 text-sm font-medium" style="color:#64748B">Sélectionnez une classe pour voir les moyennes</p>
            <p class="mt-1 text-xs" style="color:#94A3B8">Les moyennes sont calculées automatiquement depuis les notes saisies</p>
        </div>
    </template>

    {{-- État vide : classe sans notes --}}
    <template x-if="filterClasse && !rows.length">
        <div class="moy-empty">
            <i class="ri-file-list-3-line" style="font-size:56px;color:#CBD5E1"></i>
            <p class="mt-4 text-sm font-medium" style="color:#64748B">Aucune note saisie pour cette classe</p>
            <p class="mt-1 text-xs" style="color:#94A3B8">Saisissez d'abord les notes dans l'onglet "Notes"</p>
        </div>
    </template>

    {{-- Table --}}
    <template x-if="filterClasse && rows.length">
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

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="f-table-header">
                        <tr>
                            <th>Étudiant</th>
                            <th>Matière</th>
                            <th>Détail Notes (pondéré)</th>
                            <th>Moyenne</th>
                            <th>Résultat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in pagedRows" :key="row.key">
                            <tr class="f-table-row">
                                <td>
                                    <p class="font-bold" x-text="row.etuNom"></p>
                                    <p style="font-size:10px;color:#94A3B8" x-text="row.numero"></p>
                                </td>
                                <td x-text="row.matiere" style="font-size:12px"></td>
                                <td style="font-size:11px;color:#64748B" x-text="row.detail"></td>
                                <td>
                                    <span class="f-badge" :style="row.isPass?'background:rgba(22,163,74,.1);color:#16a34a':'background:rgba(239,68,68,.1);color:#ef4444'"
                                          x-text="row.moyVal.toFixed(2)"></span>
                                </td>
                                <td>
                                    <span class="f-badge" style="font-size:12px" :style="row.isPass?'background:rgba(22,163,74,.1);color:#16a34a':'background:rgba(239,68,68,.1);color:#ef4444'"
                                          x-text="row.isPass?'Admis':'Ajourné'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between px-5 py-3 border-t border-slate-100 flex-wrap gap-2">
                <p class="text-xs" style="color:#94A3B8" x-text="rows.length===0?'0 résultat':(((page-1)*perPage+1)+'–'+Math.min(page*perPage,rows.length)+' sur '+rows.length+' résultat(s)')"></p>
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
    </template>
</div>

<style>@keyframes spin{to{transform:rotate(360deg)}}</style>

@push('scripts')
<script>
function moyennesPage(classesData, niveauxData, matieresData, typesNoteData, decoupagesData, inscriptionsData, notesData, moyennesSavedData, hasActiveYear){
    return {
        classes: classesData, niveaux: niveauxData, matieres: matieresData, typesNote: typesNoteData,
        decoupages: decoupagesData, inscriptions: inscriptionsData, notes: notesData, moyennesSaved: moyennesSavedData,
        hasActiveYear,
        filterNiveau:'', filterClasse:'', filterSemestre:'', filterMatiere:'', search:'',
        calculating:false, page:1, perPage:10,

        init(){
            if(this.classes.length) this.filterClasse = this.classes[0].id;
        },

        get classesFiltrees(){
            return this.filterNiveau ? this.classes.filter(c=>String(c.id_niveau)===String(this.filterNiveau)) : this.classes;
        },
        matieresDisponibles(){
            return this.filterSemestre ? this.matieres.filter(m=>String(m.id_decoupage_annee)===String(this.filterSemestre)) : this.matieres;
        },

        calcMoyenne(idIns, idMat){
            const notesM = this.notes.filter(n=>n.id_inscription===idIns && n.id_matiere===idMat);
            if(!notesM.length) return -1;
            const byType = {};
            for(const n of notesM){
                const idType = n.id_type_note || 0;
                (byType[idType] ||= []).push(Number(n.note)||0);
            }
            let sumPoids=0, sumNote=0;
            for(const key of Object.keys(byType)){
                const vals = byType[key];
                const avg = vals.reduce((s,v)=>s+v,0)/vals.length;
                if(Number(key)===0){ sumPoids+=1; sumNote+=avg; }
                else {
                    const tn = this.typesNote.find(t=>t.id===Number(key));
                    const pct = tn ? (Number(tn.pourcentage)||1) : 1;
                    sumPoids += pct; sumNote += avg*pct;
                }
            }
            return sumPoids===0 ? 0 : sumNote/sumPoids;
        },
        notesDetail(idIns, idMat){
            const notesM = this.notes.filter(n=>n.id_inscription===idIns && n.id_matiere===idMat);
            if(!notesM.length) return '—';
            const byType = {};
            for(const n of notesM){
                const idType = n.id_type_note || 0;
                (byType[idType] ||= []).push(Number(n.note)||0);
            }
            const parts = [];
            for(const key of Object.keys(byType)){
                const vals = byType[key];
                const tn = Number(key)===0 ? null : this.typesNote.find(t=>t.id===Number(key));
                const label = tn ? tn.libelle : 'Note';
                const avg = vals.reduce((s,v)=>s+v,0)/vals.length;
                const pct = tn ? tn.pourcentage : null;
                parts.push(pct!=null ? `${label}(${pct}%): ${avg.toFixed(1)}` : `${label}: ${avg.toFixed(1)}`);
            }
            return parts.join('  |  ');
        },

        get rows(){
            if(!this.filterClasse) return [];
            let inscs = this.inscriptions.filter(i=>String(i.id_classe)===String(this.filterClasse));
            if(this.search.trim()){
                const q = this.search.toLowerCase();
                inscs = inscs.filter(i=>{
                    const full = (i.etu_nom+' '+i.etu_prenom).toLowerCase();
                    const rev = (i.etu_prenom+' '+i.etu_nom).toLowerCase();
                    return full.includes(q) || rev.includes(q);
                });
            }
            const matieresFiltrees = this.matieres.filter(m=>{
                if(this.filterSemestre && String(m.id_decoupage_annee)!==String(this.filterSemestre)) return false;
                if(this.filterMatiere && String(m.id)!==String(this.filterMatiere)) return false;
                return true;
            });
            const out = [];
            for(const ins of inscs){
                for(const mat of matieresFiltrees){
                    const calc = this.calcMoyenne(ins.id, mat.id);
                    if(calc < 0) continue;
                    const saved = this.moyennesSaved.find(m=>m.id_inscription===ins.id && m.id_matiere===mat.id);
                    const moyVal = saved ? Number(saved.moyenne) : calc;
                    out.push({
                        key: ins.id+'-'+mat.id,
                        etuNom: ins.etu_nom+' '+ins.etu_prenom,
                        numero: ins.numero_inscription||'',
                        matiere: mat.libelle,
                        detail: this.notesDetail(ins.id, mat.id),
                        moyVal, isPass: moyVal>=10,
                    });
                }
            }
            return out;
        },
        get totalPages(){ return Math.max(1, Math.ceil(this.rows.length/this.perPage)); },
        get pagedRows(){
            if(this.page>this.totalPages) this.page=this.totalPages;
            const start=(this.page-1)*this.perPage;
            return this.rows.slice(start, start+this.perPage);
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

        calculer(){
            if(!this.hasActiveYear || !this.filterClasse) return;
            this.calculating = true;
            fetch('/moyennes/classe/'+this.filterClasse, {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
            }).then(async res=>{
                if(!res.ok) throw new Error('Erreur lors du calcul');
                const result = await res.json();
                this.toast(result.count===0 ? 'Aucune note trouvée pour cette classe' : result.count+' moyenne(s) calculée(s) et sauvegardées', result.count===0?'warning':'success');
                setTimeout(()=>location.reload(), 900);
            }).catch(err=>{ this.calculating=false; this.toast(err.message,'error'); });
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
