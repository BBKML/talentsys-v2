<x-app-layout title="Années Scolaires — Académique">
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
$data = $annees->map(fn($a) => [
    'id'=>$a->id,'libelle'=>$a->libelle,
    'date_debut'=>$a->date_debut??'','date_fin'=>$a->date_fin??'',
    'active'=>(bool)$a->active,
]);
@endphp

<div x-data="page({{ $data }})" class="space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold" style="color:#1E293B">Années Scolaires</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8"><span x-text="items.length"></span> année(s)</p>
        </div>
        <button @click="openCreate()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-90" style="background:var(--primary)">
            <i class="ri-add-line"></i> Nouvelle année
        </button>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium" style="background:rgba(34,197,94,.08);color:#15803d;border:1px solid rgba(34,197,94,.18)">
        <i class="ri-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:rgba(90,103,216,.12)"><i class="ri-calendar-2-fill text-xl" style="color:var(--primary)"></i></div>
            <div><p class="text-xs font-semibold" style="color:#94A3B8">Total</p><p class="text-2xl font-bold" style="color:#1E293B" x-text="items.length"></p></div>
        </div>
        <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:rgba(34,197,94,.12)"><i class="ri-checkbox-circle-fill text-xl" style="color:#16a34a"></i></div>
            <div><p class="text-xs font-semibold" style="color:#94A3B8">Active</p><p class="text-2xl font-bold" style="color:#1E293B" x-text="items.filter(i=>i.active).length"></p></div>
        </div>
        <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:rgba(100,116,139,.1)"><i class="ri-calendar-close-fill text-xl" style="color:#64748B"></i></div>
            <div><p class="text-xs font-semibold" style="color:#94A3B8">Inactives</p><p class="text-2xl font-bold" style="color:#1E293B" x-text="items.filter(i=>!i.active).length"></p></div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="relative flex-1" style="max-width:280px">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1"></i>
                <input x-model="search" @input="currentPage=1" type="text" placeholder="Rechercher une année..." class="f-input" style="padding:8px 12px 8px 34px">
            </div>
        </div>
        <table class="w-full">
            <thead><tr style="border-bottom:1px solid #F1F5F9;background:#FAFBFC">
                <th class="tbl-th" style="width:35%">Libellé</th>
                <th class="tbl-th" style="width:20%">Date début</th>
                <th class="tbl-th" style="width:20%">Date fin</th>
                <th class="tbl-th text-center" style="width:130px">Statut</th>
                <th class="tbl-th text-right" style="width:100px">Actions</th>
            </tr></thead>
            <tbody>
                <template x-if="paginated.length===0">
                    <tr><td colspan="5" class="py-20 text-center">
                        <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#F1F5F9"><i class="ri-calendar-2-line text-3xl" style="color:#CBD5E1"></i></div>
                        <p class="text-sm font-semibold" style="color:#64748B">Aucune année scolaire</p>
                    </td></tr>
                </template>
                <template x-for="r in paginated" :key="r.id">
                    <tr style="border-bottom:1px solid #F8FAFC" class="hover:bg-slate-50 transition-colors">
                        <td class="tbl-td">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" :style="r.active?'background:rgba(34,197,94,.12)':'background:#F1F5F9'">
                                    <i class="ri-calendar-check-fill text-sm" :style="r.active?'color:#16a34a':'color:#94A3B8'"></i>
                                </div>
                                <div>
                                    <span class="font-semibold" style="color:#1E293B" x-text="r.libelle"></span>
                                    <template x-if="r.active">
                                        <span class="ml-2 text-[10px] font-bold px-1.5 py-0.5 rounded" style="background:rgba(34,197,94,.12);color:#15803d">En cours</span>
                                    </template>
                                </div>
                            </div>
                        </td>
                        <td class="tbl-td text-[12px]" style="color:#64748B" x-text="r.date_debut||'—'"></td>
                        <td class="tbl-td text-[12px]" style="color:#64748B" x-text="r.date_fin||'—'"></td>
                        <td class="tbl-td text-center">
                            <template x-if="!r.active">
                                <form :action="'/annees-scolaires/'+r.id+'/activer'" method="POST" style="display:inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[11px] font-bold border border-dashed cursor-pointer hover:bg-indigo-50 transition" style="border-color:var(--primary);color:var(--primary);background:transparent">
                                        <i class="ri-focus-3-line text-[10px]"></i> Activer
                                    </button>
                                </form>
                            </template>
                            <template x-if="r.active">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[11px] font-bold" style="background:rgba(34,197,94,.12);color:#15803d">
                                    <i class="ri-checkbox-circle-fill text-[10px]"></i> Active
                                </span>
                            </template>
                        </td>
                        <td class="tbl-td">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEdit(r)" class="act-btn hover:bg-indigo-50" style="color:#94A3B8"><i class="ri-edit-2-line text-[15px]"></i></button>
                                <form :action="'/annees-scolaires/'+r.id" method="POST" style="display:inline" @submit.prevent="if(confirm('Supprimer cette année scolaire ?')) $el.submit()">
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
                <template x-for="p in pages" :key="p"><button @click="currentPage=p" class="page-btn" :style="currentPage===p?'background:var(--primary);color:#fff':'color:#475569'" :class="currentPage!==p?'hover:bg-gray-100':''" x-text="p"></button></template>
                <button @click="currentPage++" :disabled="currentPage===totalPages" class="page-btn hover:bg-gray-100 disabled:opacity-30" style="color:#64748B"><i class="ri-arrow-right-s-line"></i></button>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div x-show="modal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(15,23,42,.5)">
        <div class="bg-white rounded-2xl shadow-2xl w-full" style="max-width:480px" @click.stop>
            <div class="flex items-center gap-3 px-6 py-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(90,103,216,.12)"><i class="ri-calendar-2-fill text-xl" style="color:var(--primary)"></i></div>
                <div class="flex-1"><h2 class="text-[15px] font-bold" style="color:#1E293B" x-text="editing?'Modifier l\'année':'Nouvelle année scolaire'"></h2></div>
                <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line text-lg" style="color:#94A3B8"></i></button>
            </div>
            <div style="height:1px;background:#F1F5F9;margin:0 24px"></div>
            <form :action="editing?'/annees-scolaires/'+form.id:'{{ route('annees.store') }}'" method="POST" @submit="submitting=true">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                <div class="px-6 pt-5 pb-2 space-y-4">
                    <div>
                        <label class="f-label">Libellé <span style="color:#EF4444">*</span></label>
                        <input type="text" name="libelle" :value="form.libelle" required class="f-input" placeholder="Ex : 2024-2025">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="f-label">Date de début <span style="color:#EF4444">*</span></label>
                            <input type="date" name="date_debut" :value="form.date_debut" class="f-input">
                        </div>
                        <div>
                            <label class="f-label">Date de fin <span style="color:#EF4444">*</span></label>
                            <input type="date" name="date_fin" :value="form.date_fin" class="f-input">
                        </div>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-sm font-semibold" style="color:#475569">Année active</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="active" value="1" :checked="form.active" class="sr-only peer">
                            <div class="w-11 h-6 rounded-full peer transition-all"
                                 style="background:#CBD5E1"
                                 :style="form.active?'background:var(--primary)':'background:#CBD5E1'"
                                 @click="form.active=!form.active">
                                <div class="w-4 h-4 bg-white rounded-full shadow mt-1 transition-all"
                                     :style="form.active?'margin-left:26px':'margin-left:4px'"></div>
                            </div>
                        </label>
                    </div>
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
        items:data, search:'', perPage:10, currentPage:1, modal:false, editing:false, submitting:false,
        form:{id:'',libelle:'',date_debut:'',date_fin:'',active:false},
        get filtered(){ const q=this.search.toLowerCase(); return q?this.items.filter(i=>i.libelle.toLowerCase().includes(q)):this.items; },
        get paginated(){ const s=(this.currentPage-1)*this.perPage; return this.filtered.slice(s,s+this.perPage); },
        get totalPages(){ return Math.max(1,Math.ceil(this.filtered.length/this.perPage)); },
        get pages(){ const p=[],t=this.totalPages,c=this.currentPage; for(let i=Math.max(1,c-2);i<=Math.min(t,c+2);i++)p.push(i); return p; },
        get info(){ if(!this.filtered.length)return '0 résultat(s)'; const s=(this.currentPage-1)*this.perPage+1,e=Math.min(this.currentPage*this.perPage,this.filtered.length); return `${s}–${e} sur ${this.filtered.length} résultat(s)`; },
        openCreate(){ this.editing=false; this.submitting=false; this.form={id:'',libelle:'',date_debut:'',date_fin:'',active:false}; this.modal=true; },
        openEdit(r){ this.editing=true; this.submitting=false; this.form={...r}; this.modal=true; },
    }
}
</script>
@endpush
</x-app-layout>
