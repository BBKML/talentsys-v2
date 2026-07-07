<x-app-layout title="Trésorerie — Grand Livre & Balance">
@push('styles')
<style>
.f-table-container{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #E2E8F0;overflow:hidden}
.f-table-header th{padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.06em;background:#F8FAFC;border-bottom:1px solid #E2E8F0}
.f-table-row td{padding:12px 16px;font-size:13px;color:#334155;border-bottom:1px solid #F1F5F9}
.f-table-row:hover{background:#F8FAFC}
.f-badge{display:inline-flex;padding:4px 10px;border-radius:8px;font-size:11px;font-weight:700}
.f-input{padding:8px 10px;background:#fff;border:1px solid #E2E8F0;border-radius:8px;font-size:12px;color:#1E293B;outline:none}
.gl-tab{padding:9px 18px;border-radius:8px;font-size:13px;font-weight:700;color:#64748B;background:transparent;border:none;cursor:pointer}
.gl-tab.active{color:#5A67D8;background:rgba(90,103,216,.1)}
</style>
@endpush

@php
$ecrituresJson = $ecritures->map(fn($e) => ['id'=>$e->id,'id_journal'=>$e->id_journal,'numero_piece'=>$e->numero_piece,'date_ecriture'=>$e->date_ecriture,'libelle'=>$e->libelle,'valide'=>$e->valide]);
$lignesJson = $lignes->map(fn($l) => ['id_ecriture'=>$l->id_ecriture,'id_compte'=>$l->id_compte,'sens'=>$l->sens,'montant'=>$l->montant]);
$comptesJson = $comptes->map(fn($c) => ['id'=>$c->id,'numero_compte'=>$c->numero_compte,'libelle'=>$c->libelle,'classe'=>$c->classe]);
$journauxJson = $journaux->map(fn($j) => ['id'=>$j->id,'code'=>$j->code,'libelle'=>$j->libelle]);
@endphp

<div x-data="grandLivrePage({{ $ecrituresJson }}, {{ $lignesJson }}, {{ $comptesJson }}, {{ $journauxJson }})" class="space-y-5">
    <div>
        <h1 class="text-xl font-bold" style="color:#1E293B">Grand Livre &amp; Balance</h1>
        <p class="text-sm mt-0.5" style="color:#94A3B8"><span x-text="ecritures.length"></span> écriture(s)</p>
    </div>

    <div class="flex items-center flex-wrap gap-2">
        <select x-model="filterJournal" class="f-input">
            <option value="">Tous les journaux</option>
            <template x-for="j in journaux" :key="j.id"><option :value="j.code" x-text="j.code+' — '+j.libelle"></option></template>
        </select>
        <input type="date" x-model="dateDebut" class="f-input" title="Du">
        <input type="date" x-model="dateFin" class="f-input" title="Au">
        <input type="text" x-model="search" placeholder="Rechercher libellé / pièce..." class="f-input" style="min-width:200px">
    </div>

    <div class="flex gap-2">
        <button class="gl-tab" :class="tab==='journal'?'active':''" @click="tab='journal'">Journal des écritures</button>
        <button class="gl-tab" :class="tab==='balance'?'active':''" @click="tab='balance'">Balance des comptes</button>
    </div>

    <template x-if="tab==='journal'">
        <div class="f-table-container">
            <table class="w-full">
                <thead class="f-table-header"><tr><th>Date</th><th>N° Pièce</th><th>Journal</th><th>Libellé</th><th>Débit</th><th>Crédit</th><th>Statut</th></tr></thead>
                <tbody>
                    <template x-for="e in ecrituresFiltered()" :key="e.id">
                        <tr class="f-table-row">
                            <td style="font-family:monospace;font-size:12px" x-text="e.date_ecriture"></td>
                            <td style="font-size:12px;color:#5A67D8;font-weight:600" x-text="e.numero_piece"></td>
                            <td style="font-size:11px" x-text="nomJournal(e.id_journal)"></td>
                            <td class="truncate" x-text="e.libelle"></td>
                            <td style="color:#3b82f6;font-size:12px" x-text="debitEcriture(e.id)>0?fmt(debitEcriture(e.id))+' FCFA':'—'"></td>
                            <td style="color:#f97316;font-size:12px" x-text="creditEcriture(e.id)>0?fmt(creditEcriture(e.id))+' FCFA':'—'"></td>
                            <td>
                                <span class="f-badge" :style="e.valide?'background:rgba(22,163,74,.1);color:#16a34a':'background:#F1F5F9;color:#94A3B8'" x-text="e.valide?'Validé':'Brouillon'"></span>
                                <span x-show="Math.abs(debitEcriture(e.id)-creditEcriture(e.id))>=0.01" class="f-badge" style="background:rgba(239,68,68,.1);color:#ef4444">Déséquilibré</span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p x-show="!ecrituresFiltered().length" class="text-center py-10 text-sm" style="color:#94A3B8">Aucune écriture pour ces critères.</p>
        </div>
    </template>

    <template x-if="tab==='balance'">
        <div class="space-y-3">
            <div class="flex items-center gap-6 p-3 rounded-lg" :style="'background:'+(balanceEquilibree()?'#f0fdf4':'#fef2f2')+';border:1px solid '+(balanceEquilibree()?'#bbf7d0':'#fecaca')">
                <span class="font-bold" style="color:#3b82f6">Total Débit : <span x-text="fmt(totalBalanceDebit())+' FCFA'"></span></span>
                <span class="font-bold" style="color:#f97316">Total Crédit : <span x-text="fmt(totalBalanceCredit())+' FCFA'"></span></span>
                <span class="flex-1"></span>
                <span class="font-bold flex items-center gap-1.5" :style="'color:'+(balanceEquilibree()?'#16a34a':'#ef4444')">
                    <i :class="balanceEquilibree()?'ri-checkbox-circle-line':'ri-alert-line'"></i>
                    <span x-text="balanceEquilibree()?'Balance équilibrée':'Écart : '+fmt(Math.abs(totalBalanceDebit()-totalBalanceCredit()))+' FCFA'"></span>
                </span>
            </div>
            <div class="f-table-container">
                <table class="w-full">
                    <thead class="f-table-header"><tr><th>N° Compte</th><th>Libellé</th><th>Classe</th><th>Débit</th><th>Crédit</th><th>Solde</th></tr></thead>
                    <tbody>
                        <template x-for="r in balance()" :key="r.id_compte">
                            <tr class="f-table-row">
                                <td style="font-family:monospace;font-weight:700;color:#5A67D8" x-text="r.numero_compte"></td>
                                <td x-text="r.libelle"></td>
                                <td style="font-size:12px" x-text="'Cl.'+r.classe"></td>
                                <td style="color:#3b82f6;font-size:12px" x-text="r.debit>0?fmt(r.debit)+' FCFA':'—'"></td>
                                <td style="color:#f97316;font-size:12px" x-text="r.credit>0?fmt(r.credit)+' FCFA':'—'"></td>
                                <td class="font-bold" :style="'color:'+(r.debit-r.credit>=0?'#15803d':'#ef4444')" x-text="(r.debit-r.credit>=0?'+':'')+fmt(r.debit-r.credit)+' FCFA'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <p x-show="!balance().length" class="text-center py-10 text-sm" style="color:#94A3B8">Aucun mouvement comptable.</p>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function grandLivrePage(ecrituresData, lignesData, comptesData, journauxData){
    return {
        ecritures: ecrituresData, lignes: lignesData, comptes: comptesData, journaux: journauxData,
        tab:'journal', dateDebut:'', dateFin:'', filterJournal:'', search:'',
        fmt(n){ return Math.round(Number(n)||0).toLocaleString('fr-FR').replace(/,/g,' '); },
        nomJournal(id){ const j=this.journaux.find(j=>j.id===id); return j?j.code+' — '+j.libelle:'—'; },
        codeJournal(id){ const j=this.journaux.find(j=>j.id===id); return j?j.code:''; },
        lignesPour(idE){ return this.lignes.filter(l=>l.id_ecriture===idE); },
        debitEcriture(idE){ return this.lignesPour(idE).filter(l=>l.sens==='Débit').reduce((s,l)=>s+Number(l.montant||0),0); },
        creditEcriture(idE){ return this.lignesPour(idE).filter(l=>l.sens==='Crédit').reduce((s,l)=>s+Number(l.montant||0),0); },
        ecrituresFiltered(){
            return this.ecritures.filter(e=>{
                const d=e.date_ecriture||'';
                if(this.dateDebut && d<this.dateDebut) return false;
                if(this.dateFin && d>this.dateFin) return false;
                if(this.filterJournal && this.codeJournal(e.id_journal)!==this.filterJournal) return false;
                if(this.search.trim()){
                    const q=this.search.toLowerCase();
                    if(!(e.libelle||'').toLowerCase().includes(q) && !(e.numero_piece||'').toLowerCase().includes(q)) return false;
                }
                return true;
            }).slice().sort((a,b)=>(b.date_ecriture||'').localeCompare(a.date_ecriture||''));
        },
        balance(){
            const acc = {};
            for(const l of this.lignes){
                if(this.dateDebut||this.dateFin){
                    const e=this.ecritures.find(e=>e.id===l.id_ecriture);
                    const d=e?.date_ecriture||'';
                    if(this.dateDebut && d<this.dateDebut) continue;
                    if(this.dateFin && d>this.dateFin) continue;
                }
                const compte=this.comptes.find(c=>c.id===l.id_compte);
                if(!acc[l.id_compte]) acc[l.id_compte]={id_compte:l.id_compte,numero_compte:compte?.numero_compte||'?',libelle:compte?.libelle||'?',classe:compte?.classe||0,debit:0,credit:0};
                if(l.sens==='Débit') acc[l.id_compte].debit+=Number(l.montant||0); else acc[l.id_compte].credit+=Number(l.montant||0);
            }
            return Object.values(acc).sort((a,b)=>(a.numero_compte||'').localeCompare(b.numero_compte||''));
        },
        totalBalanceDebit(){ return this.balance().reduce((s,r)=>s+r.debit,0); },
        totalBalanceCredit(){ return this.balance().reduce((s,r)=>s+r.credit,0); },
        balanceEquilibree(){ return Math.abs(this.totalBalanceDebit()-this.totalBalanceCredit())<0.01; },
    };
}
</script>
@endpush
</x-app-layout>
