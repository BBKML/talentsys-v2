<x-app-layout title="Trésorerie — Paramétrage Comptable">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.ss-drop{position:absolute;top:100%;left:0;right:0;margin-top:4px;max-height:220px;overflow-y:auto;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.12);border:1px solid #E2E8F0;z-index:30}
.ss-item{padding:9px 14px;font-size:13px;color:#334155;cursor:pointer}
.ss-item:hover{background:#F1F5F9}
.ss-sel{background:#EEF2FF;color:#5A67D8;font-weight:600}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none}
.type-card{background:#fff;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.04);padding:14px 16px;display:flex;align-items:center;gap:14px}
.compte-chip{display:inline-flex;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;border:1px solid}
</style>
@endpush

@php
$typesOperations = collect(\App\Http\Controllers\TresorerieController::TYPES_OPERATIONS);
$parametragesJson = $parametrages->map(fn($p) => ['type_operation'=>$p->type_operation,'id_compte_debit'=>$p->id_compte_debit,'id_compte_credit'=>$p->id_compte_credit,'id_journal'=>$p->id_journal]);
$comptesJson = $comptes->map(fn($c) => ['id'=>$c->id,'numero_compte'=>$c->numero_compte,'libelle'=>$c->libelle]);
$journauxJson = $journaux->map(fn($j) => ['id'=>$j->id,'code'=>$j->code,'libelle'=>$j->libelle]);
@endphp

<div x-data="parametragePage({{ $typesOperations }}, {{ $parametragesJson }}, {{ $comptesJson }}, {{ $journauxJson }})" class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Paramétrage Comptable</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8"><span x-text="parametrages.length"></span> / <span x-text="typesOperations.length"></span> type(s) configuré(s)</p>
        </div>
        <button @click="appliquerDefauts()" :disabled="saving" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border disabled:opacity-50" style="color:#4f46e5;border-color:#4f46e5">
            <i class="ri-magic-line"></i> Défauts SYSCOHADA
        </button>
    </div>

    <template x-if="comptes.length===0 || journaux.length===0">
        <div class="flex items-center gap-3 p-3 rounded-lg" style="background:#fffbeb;border:1px solid #fde68a">
            <i class="ri-alert-line" style="color:#d97706"></i>
            <p class="text-sm" style="color:#92400e">
                <span x-show="comptes.length===0">Aucun compte dans le plan comptable. Allez d'abord dans "Plan Comptable" et cliquez "Initialiser SYSCOHADA".</span>
                <span x-show="comptes.length>0 && journaux.length===0">Aucun journal configuré. Allez dans "Plan Comptable" → "Initialiser SYSCOHADA".</span>
            </p>
        </div>
    </template>

    <div>
        <div class="flex items-center justify-between mb-1">
            <span class="text-xs" style="color:#64748B">Couverture</span>
            <span class="text-xs font-bold" :style="'color:'+progressColor()" x-text="progressPct()+'%'"></span>
        </div>
        <div class="bg-gray-100 rounded-full h-1.5 overflow-hidden"><div class="h-full rounded-full" :style="'width:'+progressPct()+'%;background:'+progressColor()"></div></div>
    </div>

    <div class="space-y-2">
        <template x-for="t in typesOperations" :key="t.code">
            <div class="type-card" :style="paramFor(t.code)?'border:1px solid #bbf7d0':'border:1px solid #F1F5F9'">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" :style="paramFor(t.code)?'background:rgba(22,163,74,.1)':'background:#F1F5F9'">
                    <i :class="paramFor(t.code)?'ri-checkbox-circle-line':'ri-radio-button-line'" :style="'color:'+(paramFor(t.code)?'#16a34a':'#94A3B8')"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold" style="color:#1E293B" x-text="t.libelle"></p>
                    <template x-if="paramFor(t.code)">
                        <div class="flex gap-2 mt-1 flex-wrap">
                            <span class="compte-chip" style="color:#3b82f6;border-color:#3b82f680">D: <span x-text="compteLib(paramFor(t.code).id_compte_debit)"></span></span>
                            <span class="compte-chip" style="color:#f97316;border-color:#f9731680">C: <span x-text="compteLib(paramFor(t.code).id_compte_credit)"></span></span>
                            <span x-show="journalLib(paramFor(t.code).id_journal)" class="compte-chip" style="color:#5A67D8;border-color:#5A67D880">J: <span x-text="journalLib(paramFor(t.code).id_journal)"></span></span>
                        </div>
                    </template>
                    <p x-show="!paramFor(t.code)" class="text-xs" style="color:#94A3B8">Non configuré</p>
                </div>
                <button @click="openEdit(t)" class="w-9 h-9 rounded-lg inline-flex items-center justify-center hover:bg-gray-50" :style="'color:'+(paramFor(t.code)?'#3b82f6':'#16a34a')">
                    <i :class="paramFor(t.code)?'ri-edit-2-line':'ri-add-circle-line'"></i>
                </button>
            </div>
        </template>
    </div>

    <template x-if="modal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="currentType?.libelle"></h2>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="f-label">Compte Débit <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(comptes.map(c=>({v:c.id,l:c.numero_compte+' — '+c.libelle})), form.id_compte_debit, 'Sélectionner...')" class="relative" @click.outside="open=false">
                            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" :placeholder="ph" autocomplete="off">
                            <div x-show="open" class="ss-drop">
                                <template x-for="o in filtered" :key="o.v"><div @click="select(o); form.id_compte_debit=o.v" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div></template>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="f-label">Compte Crédit <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(comptes.map(c=>({v:c.id,l:c.numero_compte+' — '+c.libelle})), form.id_compte_credit, 'Sélectionner...')" class="relative" @click.outside="open=false">
                            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" :placeholder="ph" autocomplete="off">
                            <div x-show="open" class="ss-drop">
                                <template x-for="o in filtered" :key="o.v"><div @click="select(o); form.id_compte_credit=o.v" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div></template>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="f-label">Journal</label>
                        <div x-data="sSelect(journaux.map(j=>({v:j.id,l:j.code+' — '+j.libelle})), form.id_journal, 'Sélectionner...')" class="relative" @click.outside="open=false">
                            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" :placeholder="ph" autocomplete="off">
                            <div x-show="open" class="ss-drop">
                                <template x-for="o in filtered" :key="o.v"><div @click="select(o); form.id_journal=o.v" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div></template>
                            </div>
                        </div>
                    </div>
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
function parametragePage(typesOperations, parametragesData, comptesData, journauxData){
    return {
        typesOperations, parametrages: parametragesData, comptes: comptesData, journaux: journauxData,
        modal:false, submitting:false, saving:false, currentType:null,
        form:{type_operation:'',id_compte_debit:'',id_compte_credit:'',id_journal:''},

        paramFor(code){ return this.parametrages.find(p=>p.type_operation===code); },
        compteLib(id){ const c=this.comptes.find(c=>c.id===id); return c?c.numero_compte+' '+c.libelle:'—'; },
        journalLib(id){ const j=this.journaux.find(j=>j.id===id); return j?j.code+' — '+j.libelle:''; },
        progressPct(){ return this.typesOperations.length? Math.round(this.parametrages.length/this.typesOperations.length*100) : 0; },
        progressColor(){ const r=this.progressPct(); return r<50?'#ef4444':r<100?'#f97316':'#16a34a'; },

        openEdit(t){
            this.currentType=t; this.submitting=false;
            const existing=this.paramFor(t.code);
            this.form={type_operation:t.code, id_compte_debit:existing?.id_compte_debit||'', id_compte_credit:existing?.id_compte_credit||'', id_journal:existing?.id_journal||''};
            this.modal=true;
        },
        submitForm(){
            if(!this.form.id_compte_debit||!this.form.id_compte_credit) return this.toast('Comptes débit/crédit requis','error');
            this.submitting=true;
            fetch('/tresorerie/parametrage',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:JSON.stringify(this.form)})
                .then(async res=>{
                    if(!res.ok){ const e=await res.json(); throw new Error(e.message||'Erreur'); }
                    const r=await res.json();
                    const flat={type_operation:r.data.type_operation,id_compte_debit:r.data.id_compte_debit,id_compte_credit:r.data.id_compte_credit,id_journal:r.data.id_journal};
                    const idx=this.parametrages.findIndex(p=>p.type_operation===flat.type_operation);
                    if(idx!==-1) this.parametrages[idx]=flat; else this.parametrages.push(flat);
                    this.modal=false; this.submitting=false;
                    this.toast('Paramétrage enregistré','success');
                }).catch(err=>{ this.submitting=false; this.toast(err.message,'error'); });
        },
        appliquerDefauts(){
            if(!confirm("Ceci va pré-remplir les associations compte/journal selon les recommandations SYSCOHADA. Les paramètrages déjà configurés seront écrasés.\n\nContinuer ?")) return;
            this.saving=true;
            fetch('/tresorerie/parametrage/defauts',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); this.toast('Paramétrage SYSCOHADA par défaut appliqué','success'); setTimeout(()=>location.reload(),900); })
                .catch(err=>{ this.saving=false; this.toast(err.message,'error'); });
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
