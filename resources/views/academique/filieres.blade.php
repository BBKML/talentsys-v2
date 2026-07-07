<x-app-layout title="Filières — Académique">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none;transition:all .15s}
.f-input:focus{background:#fff;box-shadow:0 0 0 2px var(--primary)44}
.tbl-th{font-size:11px;font-weight:600;color:#94A3B8;text-transform:uppercase;letter-spacing:.07em;padding:11px 16px;text-align:left}
.tbl-td{padding:13px 16px;font-size:13px;color:#475569}
.act-btn{width:34px;height:34px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;cursor:pointer;border:none;background:transparent}
.page-btn{width:32px;height:32px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .15s}
</style>
@endpush

@php
$data = $filieres->map(fn($f) => ['id'=>$f->id,'libelle'=>$f->libelle,'actif'=>$f->id_statut==1]);
@endphp

<div x-data="page({{ $data }})" class="space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold" style="color:#1E293B">Filières</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8"><span x-text="items.length"></span> filière(s)</p>
        </div>
        <button @click="openCreate()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-90 transition" style="background:var(--primary)">
            <i class="ri-add-line"></i> Nouvelle filière
        </button>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium" style="background:rgba(34,197,94,.08);color:#15803d;border:1px solid rgba(34,197,94,.18)">
        <i class="ri-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Résumé --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:rgba(90,103,216,.12)"><i class="ri-bookmark-3-fill text-xl" style="color:var(--primary)"></i></div>
            <div><p class="text-xs font-semibold" style="color:#94A3B8">Total</p><p class="text-2xl font-bold" style="color:#1E293B" x-text="items.length"></p></div>
        </div>
        <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:rgba(34,197,94,.12)"><i class="ri-checkbox-circle-fill text-xl" style="color:#16a34a"></i></div>
            <div><p class="text-xs font-semibold" style="color:#94A3B8">Actives</p><p class="text-2xl font-bold" style="color:#1E293B" x-text="items.filter(i=>i.actif).length"></p></div>
        </div>
        <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:rgba(239,68,68,.1)"><i class="ri-close-circle-fill text-xl" style="color:#dc2626"></i></div>
            <div><p class="text-xs font-semibold" style="color:#94A3B8">Inactives</p><p class="text-2xl font-bold" style="color:#1E293B" x-text="items.filter(i=>!i.actif).length"></p></div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="relative flex-1" style="max-width:280px">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1"></i>
                <input x-model="search" @input="currentPage=1" type="text" placeholder="Rechercher une filière..." class="f-input" style="padding:8px 12px 8px 34px">
            </div>
            <div class="ml-auto flex items-center gap-2 text-xs font-semibold" style="color:#94A3B8">
                Lignes/page :
                <select x-model.number="perPage" @change="currentPage=1" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs bg-white outline-none" style="color:#475569">
                    <option>10</option><option>25</option><option>50</option>
                </select>
            </div>
        </div>
        <table class="w-full">
            <thead><tr style="border-bottom:1px solid #F1F5F9;background:#FAFBFC">
                <th class="tbl-th" style="width:60%">Libellé</th>
                <th class="tbl-th text-center" style="width:120px">Statut</th>
                <th class="tbl-th text-right" style="width:100px">Actions</th>
            </tr></thead>
            <tbody>
                <template x-if="paginated.length===0">
                    <tr><td colspan="3" class="py-20 text-center">
                        <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#F1F5F9"><i class="ri-bookmark-3-line text-3xl" style="color:#CBD5E1"></i></div>
                        <p class="text-sm font-semibold" style="color:#64748B">Aucune filière</p>
                    </td></tr>
                </template>
                <template x-for="r in paginated" :key="r.id">
                    <tr style="border-bottom:1px solid #F8FAFC" class="hover:bg-slate-50 transition-colors">
                        <td class="tbl-td">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(90,103,216,.1)">
                                    <i class="ri-bookmark-3-fill text-sm" style="color:var(--primary)"></i>
                                </div>
                                <span class="font-semibold" style="color:#1E293B" x-text="r.libelle"></span>
                            </div>
                        </td>
                        <td class="tbl-td text-center">
                            <form :action="'/filieres/'+r.id+'/statut'" method="POST" style="display:inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[11px] font-bold border-0 cursor-pointer hover:opacity-75 transition"
                                        :class="r.actif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'">
                                    <span class="w-1.5 h-1.5 rounded-full inline-block" :style="'background:'+(r.actif?'#16a34a':'#dc2626')"></span>
                                    <span x-text="r.actif?'Active':'Inactive'"></span>
                                </button>
                            </form>
                        </td>
                        <td class="tbl-td">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEdit(r)" class="act-btn hover:bg-indigo-50" style="color:#94A3B8"><i class="ri-edit-2-line text-[15px]"></i></button>
                                <form :action="'/filieres/'+r.id" method="POST" style="display:inline" @submit.prevent="if(confirm('Supprimer « '+r.libelle+' » ?')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act-btn hover:bg-red-50" style="color:#CBD5E1"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <div class="flex items-center justify-between px-5 py-3 border-t border-gray-100">
            <span class="text-xs" style="color:#94A3B8" x-text="info"></span>
            <div class="flex items-center gap-1">
                <button @click="currentPage--" :disabled="currentPage===1" class="page-btn hover:bg-gray-100 disabled:opacity-30" style="color:#64748B"><i class="ri-arrow-left-s-line"></i></button>
                <template x-for="p in pages" :key="p">
                    <button @click="currentPage=p" class="page-btn" :style="currentPage===p?'background:var(--primary);color:#fff':'color:#475569'" :class="currentPage!==p?'hover:bg-gray-100':''" x-text="p"></button>
                </template>
                <button @click="currentPage++" :disabled="currentPage===totalPages" class="page-btn hover:bg-gray-100 disabled:opacity-30" style="color:#64748B"><i class="ri-arrow-right-s-line"></i></button>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div x-show="modal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(15,23,42,.5)">
        <div class="bg-white rounded-2xl shadow-2xl w-full" style="max-width:420px" @click.stop>
            <div class="flex items-center gap-3 px-6 py-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(90,103,216,.12)"><i class="ri-bookmark-3-fill text-xl" style="color:var(--primary)"></i></div>
                <div class="flex-1">
                    <h2 class="text-[15px] font-bold" style="color:#1E293B" x-text="editing?'Modifier la filière':'Nouvelle filière'"></h2>
                </div>
                <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line text-lg" style="color:#94A3B8"></i></button>
            </div>
            <div style="height:1px;background:#F1F5F9;margin:0 24px"></div>
            <form :action="editing?'/filieres/'+form.id:'{{ route('filieres.store') }}'" method="POST" @submit="submitting=true">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                <div class="px-6 pt-5 pb-2">
                    <label class="f-label">Libellé <span style="color:#EF4444">*</span></label>
                    <input type="text" name="libelle" :value="form.libelle" required class="f-input" placeholder="Ex : Informatique">
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4">
                    <button type="button" @click="modal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                    <button type="submit" :disabled="submitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:var(--primary)" x-text="submitting?'...':'Enregistrer'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function page(data){
    return {
        items:data, search:'', perPage:10, currentPage:1, modal:false, editing:false, submitting:false, form:{id:'',libelle:''},
        get filtered(){ const q=this.search.toLowerCase(); return q?this.items.filter(i=>i.libelle.toLowerCase().includes(q)):this.items; },
        get paginated(){ const s=(this.currentPage-1)*this.perPage; return this.filtered.slice(s,s+this.perPage); },
        get totalPages(){ return Math.max(1,Math.ceil(this.filtered.length/this.perPage)); },
        get pages(){ const p=[],t=this.totalPages,c=this.currentPage; for(let i=Math.max(1,c-2);i<=Math.min(t,c+2);i++)p.push(i); return p; },
        get info(){ if(!this.filtered.length)return '0 résultat(s)'; const s=(this.currentPage-1)*this.perPage+1,e=Math.min(this.currentPage*this.perPage,this.filtered.length); return `${s}–${e} sur ${this.filtered.length} résultat(s)`; },
        openCreate(){ this.editing=false; this.submitting=false; this.form={id:'',libelle:''}; this.modal=true; },
        openEdit(r){ this.editing=true; this.submitting=false; this.form={...r}; this.modal=true; },
    }
}
</script>
@endpush
</x-app-layout>
