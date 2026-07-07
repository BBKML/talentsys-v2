<x-app-layout title="Trésorerie — Plan Comptable">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none}
.f-select{width:100%;padding:10px 12px;background:#fff;border:1px solid #E2E8F0;border-radius:8px;font-size:13px;color:#1E293B;outline:none}
.f-table-container{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #E2E8F0;overflow:hidden}
.f-table-header th{padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.06em;background:#F8FAFC;border-bottom:1px solid #E2E8F0}
.f-table-row td{padding:12px 16px;font-size:13px;color:#334155;border-bottom:1px solid #F1F5F9}
.f-table-row:hover{background:#F8FAFC}
.f-badge{display:inline-flex;padding:4px 10px;border-radius:8px;font-size:12px;font-weight:700;white-space:nowrap}
.chip{padding:6px 11px;border-radius:16px;font-size:12px;font-weight:600;border:1px solid #E2E8F0;background:#fff;color:#475569;cursor:pointer}
.chip.active{color:#fff}
.kpi-mini{padding:8px 14px;border-radius:10px;font-size:11px;font-weight:700;white-space:nowrap;flex-shrink:0}
</style>
@endpush

@php
$comptesJson = $comptes->map(fn($c) => ['id'=>$c->id,'numero_compte'=>$c->numero_compte,'libelle'=>$c->libelle,'classe'=>$c->classe,'type_compte'=>$c->type_compte,'sens_normal'=>$c->sens_normal,'actif'=>$c->actif]);
@endphp

<div x-data="planComptablePage({{ $comptesJson }})" class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Plan Comptable SYSCOHADA</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8"><span x-text="filtered().length"></span> compte(s)</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="initSyscohada()" :disabled="initLoading" class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold border disabled:opacity-50" style="color:#4f46e5;border-color:#4f46e5">
                <i class="ri-magic-line"></i> <span x-text="initLoading?'Init...':'Initialiser SYSCOHADA'"></span>
            </button>
            <button @click="openCreate()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90" style="background:#5A67D8">
                <i class="ri-add-line"></i> Nouveau Compte
            </button>
        </div>
    </div>

    <div class="flex items-center flex-wrap gap-3">
        <div class="flex gap-1.5 flex-wrap">
            <button class="chip" :class="!filterClasse?'active':''" :style="!filterClasse?'background:#5A67D8;border-color:#5A67D8':''" @click="filterClasse=''">Tous</button>
            <template x-for="i in 8" :key="i">
                <button class="chip" :class="filterClasse===i?'active':''" :style="filterClasse===i?('background:'+classeColor(i)+';border-color:'+classeColor(i)):''" @click="filterClasse=filterClasse===i?'':i">Cl.<span x-text="i"></span></button>
            </template>
        </div>
        <div class="relative">
            <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-300"></i>
            <input x-model="search" type="text" placeholder="Rechercher N° ou libellé..." class="pl-9 pr-4 py-2 rounded-lg text-sm border border-slate-200 bg-white outline-none" style="min-width:240px">
        </div>
    </div>

    <div class="flex gap-2 overflow-x-auto pb-1">
        <template x-for="k in classeKpis()" :key="k.classe">
            <div class="kpi-mini" :style="'background:'+k.color+'14;color:'+k.color+';border:1px solid '+k.color+'4d'">
                <span x-text="'Cl.'+k.classe+' — '+k.libelle"></span> · <span x-text="k.count+' compte(s)'"></span>
            </div>
        </template>
    </div>

    <div class="f-table-container">
        <table class="w-full">
            <thead class="f-table-header"><tr><th>N° Compte</th><th>Libellé</th><th>Classe</th><th>Type</th><th>Sens</th><th>Statut</th><th style="text-align:right">Actions</th></tr></thead>
            <tbody>
                <template x-for="c in filtered()" :key="c.id">
                    <tr class="f-table-row">
                        <td style="font-family:monospace;font-weight:700;color:#5A67D8" x-text="c.numero_compte"></td>
                        <td style="font-weight:500" x-text="c.libelle"></td>
                        <td><span class="f-badge" :style="'background:'+classeColor(c.classe)+'1a;color:'+classeColor(c.classe)" x-text="'Cl.'+c.classe+' '+classeLib(c.classe)"></span></td>
                        <td style="font-size:12px" x-text="c.type_compte"></td>
                        <td><span class="f-badge" :style="c.sens_normal==='Débit'?'background:rgba(59,130,246,.1);color:#3b82f6':'background:rgba(249,115,22,.1);color:#f97316'" x-text="c.sens_normal"></span></td>
                        <td><span class="f-badge" :style="c.actif?'background:rgba(22,163,74,.1);color:#16a34a':'background:#F1F5F9;color:#94A3B8'" x-text="c.actif?'Actif':'Inactif'"></span></td>
                        <td style="text-align:right">
                            <button @click="openEdit(c)" class="w-8 h-8 rounded-lg hover:bg-indigo-50 inline-flex items-center justify-center" style="color:#5A67D8"><i class="ri-edit-2-line text-[15px]"></i></button>
                            <button @click="deleteCompte(c)" class="w-8 h-8 rounded-lg hover:bg-red-50 inline-flex items-center justify-center" style="color:#ef4444"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <p x-show="!filtered().length" class="text-center py-10 text-sm" style="color:#94A3B8">Aucun compte</p>
    </div>

    <template x-if="modal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="editing?'Modifier Compte':'Nouveau Compte'"></h2>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="f-label">N° Compte <span style="color:#EF4444">*</span></label><input type="text" x-model="form.numero_compte" class="f-input" placeholder="ex: 5111"></div>
                        <div><label class="f-label">Classe</label>
                            <select x-model.number="form.classe" class="f-select"><template x-for="i in 8" :key="i"><option :value="i" x-text="'Classe '+i"></option></template></select>
                        </div>
                    </div>
                    <div><label class="f-label">Libellé <span style="color:#EF4444">*</span></label><input type="text" x-model="form.libelle" class="f-input"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="f-label">Type</label>
                            <select x-model="form.type_compte" class="f-select">
                                <option>Actif</option><option>Passif</option><option>Capitaux</option><option>Charge</option><option>Produit</option><option>Trésorerie</option>
                            </select>
                        </div>
                        <div><label class="f-label">Sens normal</label>
                            <select x-model="form.sens_normal" class="f-select"><option>Débit</option><option>Crédit</option></select>
                        </div>
                    </div>
                    <label class="flex items-center gap-2"><input type="checkbox" x-model="form.actif"> <span class="text-sm" style="color:#334155">Compte actif</span></label>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="modal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                        <button type="button" @click="submitForm()" :disabled="submitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:#5A67D8" x-text="submitting?'...':'Enregistrer'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function planComptablePage(comptesData){
    return {
        comptes: comptesData, filterClasse:'', search:'',
        modal:false, editing:false, submitting:false, initLoading:false,
        form:{id:'',numero_compte:'',libelle:'',classe:5,type_compte:'Trésorerie',sens_normal:'Débit',actif:true},

        filtered(){
            return this.comptes.filter(c=>{
                if(this.filterClasse && c.classe!==this.filterClasse) return false;
                if(this.search.trim()){
                    const q=this.search.toLowerCase();
                    if(!(c.numero_compte||'').toLowerCase().includes(q) && !(c.libelle||'').toLowerCase().includes(q)) return false;
                }
                return true;
            }).slice().sort((a,b)=>(a.numero_compte||'').localeCompare(b.numero_compte||''));
        },
        classeColor(c){ return {1:'#a855f7',2:'#6366f1',3:'#3b82f6',4:'#14b8a6',5:'#22c55e',6:'#ef4444',7:'#f97316',8:'#92400e'}[c]||'#94A3B8'; },
        classeLib(c){ return {1:'Capitaux',2:'Immobilisations',3:'Stocks',4:'Tiers',5:'Trésorerie',6:'Charges',7:'Produits',8:'Hors activité'}[c]||('Cl.'+c); },
        classeKpis(){
            const counts={};
            for(const c of this.comptes) counts[c.classe]=(counts[c.classe]||0)+1;
            return Object.keys(counts).map(k=>({classe:k,count:counts[k],color:this.classeColor(Number(k)),libelle:this.classeLib(Number(k))})).sort((a,b)=>a.classe-b.classe);
        },

        openCreate(){ this.editing=false; this.submitting=false; this.form={id:'',numero_compte:'',libelle:'',classe:5,type_compte:'Trésorerie',sens_normal:'Débit',actif:true}; this.modal=true; },
        openEdit(c){ this.editing=true; this.submitting=false; this.form={id:c.id,numero_compte:c.numero_compte,libelle:c.libelle,classe:c.classe,type_compte:c.type_compte,sens_normal:c.sens_normal,actif:!!c.actif}; this.modal=true; },
        submitForm(){
            if(!this.form.numero_compte||!this.form.libelle) return this.toast('Champs requis manquants','error');
            this.submitting=true;
            const url=this.editing?'/tresorerie/comptes/'+this.form.id:'/tresorerie/comptes';
            const method=this.editing?'PUT':'POST';
            fetch(url,{method,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:JSON.stringify(this.form)})
                .then(async res=>{
                    if(!res.ok){ const e=await res.json(); throw new Error(e.message||'Erreur'); }
                    const r=await res.json();
                    const flat={id:r.data.id,numero_compte:r.data.numero_compte,libelle:r.data.libelle,classe:r.data.classe,type_compte:r.data.type_compte,sens_normal:r.data.sens_normal,actif:!!r.data.actif};
                    if(this.editing){ const idx=this.comptes.findIndex(c=>c.id===flat.id); if(idx!==-1) this.comptes[idx]=flat; }
                    else this.comptes.unshift(flat);
                    this.modal=false; this.submitting=false;
                    this.toast(this.editing?'Compte modifié':'Compte créé','success');
                }).catch(err=>{ this.submitting=false; this.toast(err.message,'error'); });
        },
        deleteCompte(c){
            if(!confirm('Supprimer "'+c.numero_compte+' — '+c.libelle+'" ?')) return;
            fetch('/tresorerie/comptes/'+c.id,{method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); this.comptes=this.comptes.filter(x=>x.id!==c.id); this.toast('Compte supprimé','success'); })
                .catch(err=>this.toast(err.message,'error'));
        },
        initSyscohada(){
            if(!confirm('Cette action va créer les comptes et journaux standards SYSCOHADA (seuls les comptes manquants seront ajoutés, les existants sont conservés). Continuer ?')) return;
            this.initLoading=true;
            fetch('/tresorerie/init-plan-comptable',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); this.toast('Plan comptable SYSCOHADA initialisé avec succès','success'); setTimeout(()=>location.reload(),900); })
                .catch(err=>{ this.initLoading=false; this.toast(err.message,'error'); });
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
