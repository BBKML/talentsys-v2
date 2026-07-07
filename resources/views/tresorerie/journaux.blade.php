<x-app-layout title="Trésorerie — Journaux Comptables">
@push('styles')
<style>
.kpi-card{flex:1;background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.05);padding:14px;display:flex;align-items:center;gap:10px}
.kpi-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.chip{padding:7px 12px;border-radius:20px;font-size:12px;font-weight:600;border:1px solid #E2E8F0;background:#fff;color:#475569;cursor:pointer}
.chip.active{color:#fff}
.f-input{padding:8px 10px;background:#fff;border:1px solid #E2E8F0;border-radius:8px;font-size:12px;color:#1E293B;outline:none}
.ecr-card{background:#fff;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.04);margin-bottom:10px;overflow:hidden}
.jnl-badge{padding:4px 10px;border-radius:6px;color:#fff;font-weight:700;font-size:12px}
.ligne-row{display:flex;align-items:center;padding:6px 16px;font-size:12px;gap:10px}
.sens-badge{width:48px;text-align:center;padding:2px 4px;border-radius:4px;font-size:10px;font-weight:700}
</style>
@endpush

@php
$ecrituresJson = $ecritures->map(fn($e) => ['id'=>$e->id,'id_journal'=>$e->id_journal,'numero_piece'=>$e->numero_piece,'date_ecriture'=>$e->date_ecriture,'libelle'=>$e->libelle,'origine'=>$e->origine,'valide'=>$e->valide]);
$lignesJson = $lignes->map(fn($l) => ['id_ecriture'=>$l->id_ecriture,'id_compte'=>$l->id_compte,'sens'=>$l->sens,'montant'=>$l->montant]);
$comptesJson = $comptes->map(fn($c) => ['id'=>$c->id,'numero_compte'=>$c->numero_compte,'libelle'=>$c->libelle]);
$journauxJson = $journaux->map(fn($j) => ['id'=>$j->id,'code'=>$j->code,'libelle'=>$j->libelle]);
@endphp

<div x-data="journauxPage({{ $ecrituresJson }}, {{ $lignesJson }}, {{ $comptesJson }}, {{ $journauxJson }})" class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Journaux Comptables</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8"><span x-text="ecrituresFiltered().length"></span> écriture(s)</p>
        </div>
        <button @click="location.reload()" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border" style="color:#64748B;border-color:#E2E8F0"><i class="ri-refresh-line"></i> Actualiser</button>
    </div>

    <div class="flex gap-3 flex-wrap">
        <div class="kpi-card"><div class="kpi-icon" style="background:rgba(59,130,246,.1)"><i class="ri-arrow-up-line" style="color:#3b82f6"></i></div><div><p class="text-[10px]" style="color:#94A3B8">Total Débit</p><p class="text-sm font-bold" style="color:#3b82f6" x-text="fmt(totalDebit())+' FCFA'"></p></div></div>
        <div class="kpi-card"><div class="kpi-icon" style="background:rgba(249,115,22,.1)"><i class="ri-arrow-down-line" style="color:#f97316"></i></div><div><p class="text-[10px]" style="color:#94A3B8">Total Crédit</p><p class="text-sm font-bold" style="color:#f97316" x-text="fmt(totalCredit())+' FCFA'"></p></div></div>
        <div class="kpi-card"><div class="kpi-icon" :style="'background:'+(balanced()?'rgba(22,163,74,.1)':'rgba(239,68,68,.1)')"><i :class="balanced()?'ri-checkbox-circle-line':'ri-alert-line'" :style="'color:'+(balanced()?'#16a34a':'#ef4444')"></i></div><div><p class="text-[10px]" style="color:#94A3B8">Équilibre</p><p class="text-sm font-bold" :style="'color:'+(balanced()?'#16a34a':'#ef4444')" x-text="balanced()?'Équilibré':'DÉSÉQUILIBRÉ'"></p></div></div>
        <div class="kpi-card"><div class="kpi-icon" style="background:rgba(90,103,216,.1)"><i class="ri-file-list-3-line" style="color:#5A67D8"></i></div><div><p class="text-[10px]" style="color:#94A3B8">Écritures</p><p class="text-sm font-bold" style="color:#5A67D8" x-text="ecrituresFiltered().length"></p></div></div>
    </div>

    <div class="flex items-center flex-wrap gap-2">
        <button class="chip" :class="!filterJournal?'active':''" :style="!filterJournal?'background:#5A67D8;border-color:#5A67D8':''" @click="filterJournal=''">Tous</button>
        <template x-for="j in journaux" :key="j.id">
            <button class="chip" :class="filterJournal===j.code?'active':''" :style="filterJournal===j.code?('background:'+journalColor(j.code)+';border-color:'+journalColor(j.code)):''" @click="filterJournal=filterJournal===j.code?'':j.code" x-text="j.code"></button>
        </template>
        <input type="date" x-model="dateDebut" class="f-input" title="Date début">
        <input type="date" x-model="dateFin" class="f-input" title="Date fin">
        <input type="text" x-model="search" placeholder="Rechercher..." class="f-input" style="min-width:180px">
    </div>

    <template x-if="!ecrituresFiltered().length">
        <div class="text-center py-16">
            <i class="ri-file-list-3-line" style="font-size:56px;color:#CBD5E1"></i>
            <p class="mt-3 text-sm font-medium" style="color:#64748B">Aucune écriture comptable</p>
            <p class="text-xs mt-1" style="color:#94A3B8">Les écritures sont générées automatiquement lors des paiements.</p>
        </div>
    </template>

    <div>
        <template x-for="e in ecrituresFiltered()" :key="e.id">
            <div class="ecr-card">
                <div class="flex items-center gap-3 px-4 py-2.5">
                    <span class="jnl-badge" :style="'background:'+journalColor(codeJournal(e.id_journal))" x-text="codeJournal(e.id_journal)"></span>
                    <span style="font-family:monospace;font-size:12px;color:#5A67D8;font-weight:700" x-text="e.numero_piece"></span>
                    <span style="font-size:12px;color:#64748B" x-text="e.date_ecriture"></span>
                    <span class="flex-1 text-sm font-medium truncate" x-text="e.libelle"></span>
                    <span x-show="e.origine" class="chip" style="padding:3px 8px;font-size:10px" :style="'color:'+origineColor(e.origine)+';border-color:'+origineColor(e.origine)+'4d'" x-text="origineLabel(e.origine)"></span>
                    <i :class="lignesEquilibrees(e.id)?'ri-checkbox-circle-fill':'ri-error-warning-fill'" :style="'color:'+(lignesEquilibrees(e.id)?'#16a34a':'#ef4444')"></i>
                    <i :class="e.valide?'ri-lock-fill':'ri-lock-unlock-line'" :style="'color:'+(e.valide?'#5A67D8':'#94A3B8')"></i>
                </div>
                <div style="background:#F8FAFC;border-top:1px solid #F1F5F9">
                    <template x-for="l in lignesPour(e.id)" :key="l.id_ecriture+'-'+l.id_compte+'-'+l.sens">
                        <div class="ligne-row">
                            <span x-show="l.sens==='Crédit'" style="width:80px"></span>
                            <span class="sens-badge" :style="l.sens==='Débit'?'background:#dbeafe;color:#3b82f6':'background:#ffedd5;color:#f97316'" x-text="l.sens"></span>
                            <span style="font-family:monospace;color:#5A67D8" x-text="compteNum(l.id_compte)"></span>
                            <span class="flex-1 truncate" x-text="compteLib(l.id_compte)"></span>
                            <span class="font-bold" :style="'color:'+(l.sens==='Débit'?'#1d4ed8':'#c2410c')" x-text="fmt(l.montant)+' FCFA'"></span>
                            <span x-show="l.sens==='Débit'" style="width:80px"></span>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
function journauxPage(ecrituresData, lignesData, comptesData, journauxData){
    return {
        ecritures: ecrituresData, lignes: lignesData, comptes: comptesData, journaux: journauxData,
        filterJournal:'', dateDebut:'', dateFin:'', search:'',
        fmt(n){ return Math.round(Number(n)||0).toLocaleString('fr-FR').replace(/,/g,' '); },
        journalColor(code){ return {VT:'#5A67D8',BQ:'#3b82f6',CA:'#16a34a',AC:'#ef4444',OD:'#f97316'}[code]||'#94A3B8'; },
        origineColor(o){ return {paiement:'#16a34a',inscription:'#5A67D8',stock:'#0d9488',depense:'#ef4444'}[o]||'#94A3B8'; },
        origineLabel(o){ return {paiement:'Paiement',inscription:'Inscription',stock:'Stock',depense:'Dépense',manuel:'Manuel'}[o]||o; },
        codeJournal(id){ const j=this.journaux.find(j=>j.id===id); return j?j.code:'OD'; },
        compteNum(id){ const c=this.comptes.find(c=>c.id===id); return c?c.numero_compte:'—'; },
        compteLib(id){ const c=this.comptes.find(c=>c.id===id); return c?c.libelle:'—'; },
        lignesPour(idE){ return this.lignes.filter(l=>l.id_ecriture===idE); },
        lignesEquilibrees(idE){
            const ls=this.lignesPour(idE);
            const d=ls.filter(l=>l.sens==='Débit').reduce((s,l)=>s+Number(l.montant||0),0);
            const c=ls.filter(l=>l.sens==='Crédit').reduce((s,l)=>s+Number(l.montant||0),0);
            return Math.abs(d-c)<0.01;
        },
        ecrituresFiltered(){
            return this.ecritures.filter(e=>{
                if(this.filterJournal && this.codeJournal(e.id_journal)!==this.filterJournal) return false;
                const d = e.date_ecriture||'';
                if(this.dateDebut && d<this.dateDebut) return false;
                if(this.dateFin && d>this.dateFin) return false;
                if(this.search.trim()){
                    const q=this.search.toLowerCase();
                    if(!(e.libelle||'').toLowerCase().includes(q) && !(e.numero_piece||'').toLowerCase().includes(q)) return false;
                }
                return true;
            }).slice().sort((a,b)=>(b.date_ecriture||'').localeCompare(a.date_ecriture||''));
        },
        totalDebit(){ return this.ecrituresFiltered().reduce((s,e)=>s+this.lignesPour(e.id).filter(l=>l.sens==='Débit').reduce((ss,l)=>ss+Number(l.montant||0),0),0); },
        totalCredit(){ return this.ecrituresFiltered().reduce((s,e)=>s+this.lignesPour(e.id).filter(l=>l.sens==='Crédit').reduce((ss,l)=>ss+Number(l.montant||0),0),0); },
        balanced(){ return Math.abs(this.totalDebit()-this.totalCredit())<0.01; },
    };
}
</script>
@endpush
</x-app-layout>
