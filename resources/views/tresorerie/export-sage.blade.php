<x-app-layout title="Trésorerie — Export Sage SAARI">
@push('styles')
<style>
.kpi-card{flex:1;background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.05);padding:12px;display:flex;align-items:center;gap:10px}
.kpi-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.f-input{padding:8px 10px;background:#fff;border:1px solid #E2E8F0;border-radius:8px;font-size:12px;color:#1E293B;outline:none;width:100%}
</style>
@endpush

@php
$ecrituresJson = $ecritures->map(fn($e) => ['id'=>$e->id,'id_journal'=>$e->id_journal,'numero_piece'=>$e->numero_piece,'date_ecriture'=>$e->date_ecriture,'libelle'=>$e->libelle]);
$lignesJson = $lignes->map(fn($l) => ['id_ecriture'=>$l->id_ecriture,'id_compte'=>$l->id_compte,'sens'=>$l->sens,'montant'=>$l->montant]);
$comptesJson = $comptes->map(fn($c) => ['id'=>$c->id,'numero_compte'=>$c->numero_compte]);
$journauxJson = $journaux->map(fn($j) => ['id'=>$j->id,'code'=>$j->code]);
@endphp

<div x-data="exportSagePage({{ $ecrituresJson }}, {{ $lignesJson }}, {{ $comptesJson }}, {{ $journauxJson }})" class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Export Sage SAARI</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8"><span x-text="preview().length"></span> ligne(s)</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="location.reload()" class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold border" style="color:#64748B;border-color:#E2E8F0"><i class="ri-refresh-line"></i> Actualiser</button>
            <button @click="exporter()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90" style="background:#15803d"><i class="ri-upload-2-line"></i> Exporter .txt</button>
        </div>
    </div>

    <div class="flex gap-3 flex-wrap">
        <div class="kpi-card"><div class="kpi-icon" style="background:rgba(90,103,216,.1)"><i class="ri-list-check-2" style="color:#5A67D8"></i></div><div><p class="text-[10px]" style="color:#94A3B8">Lignes</p><p class="text-sm font-bold" style="color:#5A67D8" x-text="preview().length"></p></div></div>
        <div class="kpi-card"><div class="kpi-icon" style="background:rgba(59,130,246,.1)"><i class="ri-arrow-up-line" style="color:#3b82f6"></i></div><div><p class="text-[10px]" style="color:#94A3B8">Débit</p><p class="text-sm font-bold" style="color:#3b82f6" x-text="fmt(totalDebit())+' FCFA'"></p></div></div>
        <div class="kpi-card"><div class="kpi-icon" style="background:rgba(249,115,22,.1)"><i class="ri-arrow-down-line" style="color:#f97316"></i></div><div><p class="text-[10px]" style="color:#94A3B8">Crédit</p><p class="text-sm font-bold" style="color:#f97316" x-text="fmt(totalCredit())+' FCFA'"></p></div></div>
        <div class="kpi-card"><div class="kpi-icon" :style="'background:'+(balanced()?'rgba(22,163,74,.1)':'rgba(239,68,68,.1)')"><i :class="balanced()?'ri-checkbox-circle-line':'ri-error-warning-line'" :style="'color:'+(balanced()?'#16a34a':'#ef4444')"></i></div><div><p class="text-[10px]" style="color:#94A3B8">Équilibre</p><p class="text-sm font-bold" :style="'color:'+(balanced()?'#16a34a':'#ef4444')" x-text="balanced()?'OK':'DÉSÉQUILIBRÉ'"></p></div></div>
    </div>

    <div class="bg-white rounded-xl p-4 border border-slate-200">
        <p class="text-sm font-bold mb-3" style="color:#1E293B">Filtres export</p>
        <div class="flex gap-4 flex-wrap items-start">
            <div style="width:160px"><label class="text-xs" style="color:#94A3B8">Date début</label><input type="date" x-model="dateDebut" class="f-input mt-1"></div>
            <div style="width:160px"><label class="text-xs" style="color:#94A3B8">Date fin</label><input type="date" x-model="dateFin" class="f-input mt-1"></div>
            <div>
                <label class="text-xs" style="color:#94A3B8">Journaux</label>
                <div class="flex gap-1.5 mt-1 flex-wrap">
                    <template x-for="j in journaux" :key="j.id">
                        <button type="button" @click="toggleJournal(j.code)" class="px-2.5 py-1 rounded-full text-xs font-semibold border"
                                :style="journauxSel.includes(j.code)?'background:rgba(90,103,216,.15);border-color:#5A67D8;color:#5A67D8':'border-color:#E2E8F0;color:#64748B'" x-text="j.code"></button>
                    </template>
                    <p x-show="!journaux.length" class="text-xs" style="color:#94A3B8">Aucun journal</p>
                </div>
            </div>
        </div>
    </div>

    <div>
        <p class="text-sm font-bold" style="color:#1E293B">Aperçu du fichier SAARI</p>
        <div class="mt-1 px-2.5 py-1.5" style="background:#F1F5F9">
            <p style="font-family:monospace;font-size:10px;color:#94A3B8">JNL ; DATE     ; NUMPIECE      ; COMPTE ; LIBELLÉ                            ; DÉBIT      ; CRÉDIT</p>
        </div>
        <div class="rounded-b-lg overflow-y-auto" style="background:#1e1e2e;max-height:420px">
            <template x-for="(l,i) in preview()" :key="i">
                <p class="px-3 py-0.5" style="font-family:monospace;font-size:11px" :style="'color:'+(l.debit>0?'#93c5fd':'#fdba74')" x-text="toSaariLine(l)"></p>
            </template>
            <p x-show="!preview().length" class="text-center py-10 text-sm" style="color:#94A3B8">Aucune écriture pour ces filtres</p>
        </div>
        <p class="text-[10px] mt-2" style="color:#94A3B8">Format SAARI : JNL;YYYYMMDD;PIECE(13);COMPTE;LIBELLE(35);DEBIT;CREDIT;TVA;ECHEANCE — Séparateur : point-virgule</p>
    </div>
</div>

@push('scripts')
<script>
function exportSagePage(ecrituresData, lignesData, comptesData, journauxData){
    return {
        ecritures: ecrituresData, lignes: lignesData, comptes: comptesData, journaux: journauxData,
        dateDebut:'', dateFin:'', journauxSel: journauxData.map(j=>j.code),
        fmt(n){ return Math.round(Number(n)||0).toLocaleString('fr-FR').replace(/,/g,' '); },
        toggleJournal(code){ const i=this.journauxSel.indexOf(code); if(i===-1) this.journauxSel.push(code); else this.journauxSel.splice(i,1); },
        codeJournal(id){ const j=this.journaux.find(j=>j.id===id); return j?j.code:'OD'; },
        truncate(s,max){ return (s||'').length>max?(s||'').slice(0,max):(s||''); },
        fmtDate(iso){ return (iso||'').replaceAll('-',''); },
        preview(){
            const lignes=[];
            for(const e of this.ecritures){
                const d=e.date_ecriture||'';
                if(this.dateDebut && d<this.dateDebut) continue;
                if(this.dateFin && d>this.dateFin) continue;
                const codeJ=this.codeJournal(e.id_journal);
                if(!this.journauxSel.includes(codeJ)) continue;
                const dateF=this.fmtDate(d);
                const libEcr=this.truncate(e.libelle,35);
                const linesEcr=this.lignes.filter(l=>l.id_ecriture===e.id);
                for(const l of linesEcr){
                    const compte=this.comptes.find(c=>c.id===l.id_compte);
                    const isDebit=l.sens==='Débit';
                    lignes.push({ journal:codeJ, date:dateF, piece:this.truncate(e.numero_piece,13), compte:compte?compte.numero_compte:'9999',
                        libelle:libEcr, debit:isDebit?Number(l.montant||0):0, credit:isDebit?0:Number(l.montant||0) });
                }
            }
            lignes.sort((a,b)=> a.journal.localeCompare(b.journal) || a.date.localeCompare(b.date) || a.piece.localeCompare(b.piece));
            return lignes;
        },
        toSaariLine(l){
            const m=v=>v>0?v.toFixed(2):'';
            return `${l.journal};${l.date};${l.piece};${l.compte};${l.libelle};${m(l.debit)};${m(l.credit)};;`;
        },
        totalDebit(){ return this.preview().reduce((s,l)=>s+l.debit,0); },
        totalCredit(){ return this.preview().reduce((s,l)=>s+l.credit,0); },
        balanced(){ return Math.abs(this.totalDebit()-this.totalCredit())<0.01; },
        exporter(){
            const data=this.preview();
            if(!data.length) return this.toast('Aucune écriture à exporter pour les filtres sélectionnés','warning');
            const lines=[
                `; Export Sage SAARI — TalentSys ERP — ${new Date().toISOString().slice(0,10)}`,
                '; JNL;DATE;NUMPIECE;COMPTE;LIBELLE;DEBIT;CREDIT;CODETVA;ECHEANCE',
                ...data.map(l=>this.toSaariLine(l)),
            ];
            const blob=new Blob([lines.join('\r\n')],{type:'text/plain;charset=utf-8;'});
            const link=document.createElement('a');
            link.href=URL.createObjectURL(blob);
            link.download=`SAARI_export_${new Date().getFullYear()}.txt`;
            link.click();
            this.toast(data.length+' ligne(s) exportée(s)','success');
        },
        toast(message,type='info'){
            const colors={success:{bg:'rgba(22,163,74,.95)'},error:{bg:'rgba(239,68,68,.95)'},warning:{bg:'rgba(245,158,11,.95)'},info:{bg:'rgba(90,103,216,.95)'}};
            const style=colors[type]||colors.info; const t=document.createElement('div');
            t.style.cssText=`position:fixed;bottom:24px;left:50%;transform:translateX(-50%);padding:12px 24px;border-radius:12px;font-size:13px;font-weight:500;background:${style.bg};color:#fff;z-index:9999;box-shadow:0 8px 32px rgba(0,0,0,.12);max-width:90%`;
            t.textContent=message; document.body.appendChild(t);
            setTimeout(()=>{ t.style.opacity='0'; t.style.transition='all .3s ease'; setTimeout(()=>t.remove(),300); },3500);
        },
    };
}
</script>
@endpush
</x-app-layout>
