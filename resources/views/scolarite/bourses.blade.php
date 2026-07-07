<x-app-layout title="Bourses — Scolarité">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none;transition:all .15s}
.f-input:focus{background:#fff;box-shadow:0 0 0 2px var(--primary)44}
.tbl-th{font-size:11px;font-weight:600;color:#94A3B8;text-transform:uppercase;letter-spacing:.07em;padding:11px 16px;text-align:left}
.tbl-td{padding:13px 16px;font-size:13px;color:#475569}
.act-btn{width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;cursor:pointer;border:none;background:transparent}
</style>
@endpush

@php
$data = $bourses->map(fn($b) => [
    'id'          => $b->id,
    'libelle'     => $b->libelle,
    'type_bourse' => $b->type_bourse,
    'valeur'      => $b->valeur,
    'actif'       => $b->id_statut == 1,
]);
@endphp

<div x-data="page({{ $data }})" class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Bourses</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8" x-text="items.length+' bourse(s) configurée(s)'"></p>
        </div>
        <button @click="openCreate()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition" style="background:var(--primary)">
            <i class="ri-add-line text-base"></i> Nouvelle Bourse
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="px-4 py-3 rounded-xl text-sm font-medium" style="background:#DCFCE7;color:#166534">{{ session('success') }}</div>
    @endif

    {{-- Tableau --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
            <div class="relative">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1"></i>
                <input x-model="search" type="text" placeholder="Rechercher..." class="pl-9 pr-4 py-2 rounded-xl text-sm border border-gray-100 bg-gray-50 outline-none focus:border-indigo-300" style="min-width:220px">
            </div>
        </div>
        <div x-show="!filtered.length" class="py-16 text-center">
            <i class="ri-medal-line text-4xl" style="color:#CBD5E1"></i>
            <p class="mt-3 text-sm" style="color:#94A3B8">Aucune bourse trouvée</p>
        </div>
        <table x-show="filtered.length" class="w-full">
            <thead class="border-b border-gray-100">
                <tr>
                    <th class="tbl-th">Libellé</th>
                    <th class="tbl-th">Type</th>
                    <th class="tbl-th">Valeur</th>
                    <th class="tbl-th">Statut</th>
                    <th class="tbl-th text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="b in filtered" :key="b.id">
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                        <td class="tbl-td font-semibold" style="color:#1E293B" x-text="b.libelle"></td>
                        <td class="tbl-td">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:rgba(217,119,6,.1);color:#d97706" x-text="b.type_bourse"></span>
                        </td>
                        <td class="tbl-td font-bold" style="color:#d97706"
                            x-text="b.type_bourse==='Pourcentage' ? b.valeur+'%' : Number(b.valeur).toLocaleString('fr-FR')+' FCFA'">
                        </td>
                        <td class="tbl-td">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:#DCFCE7;color:#166534">Actif</span>
                        </td>
                        <td class="tbl-td">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEdit(b)" class="act-btn hover:bg-indigo-50" style="color:#6366f1"><i class="ri-edit-2-line text-[15px]"></i></button>
                                <form :action="'/bourses/'+b.id" method="POST" style="display:inline" @submit.prevent="if(confirm('Supprimer cette bourse ?')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act-btn hover:bg-red-50" style="color:#ef4444"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    <template x-if="modal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="editing?'Modifier Bourse':'Nouvelle Bourse'"></h2>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <form :action="editing?'/bourses/'+form.id:'/bourses'" method="POST" @submit="submitting=true" class="px-6 py-5 space-y-4">
                    @csrf
                    <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="f-label">Libellé <span style="color:#EF4444">*</span></label>
                        <input type="text" name="libelle" :value="form.libelle" class="f-input" placeholder="Ex: Bourse d'Excellence" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="f-label">Type <span style="color:#EF4444">*</span></label>
                            <div x-data="sSelect([{v:'Pourcentage',l:'Pourcentage'},{v:'Montant Fixe',l:'Montant Fixe'}], form.type_bourse, 'Type...')"
                                 class="relative" @click.outside="open=false">
                                <input type="hidden" name="type_bourse" :value="v">
                                <div class="relative">
                                    <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" :placeholder="ph" autocomplete="off">
                                </div>
                                <div x-show="open" class="ss-drop">
                                    <div x-show="!filtered.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucun résultat</div>
                                    <template x-for="o in filtered" :key="o.v">
                                        <div @click="select(o)" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="f-label">Valeur <span style="color:#EF4444">*</span></label>
                            <input type="number" name="valeur" :value="form.valeur" class="f-input" placeholder="50 ou montant" min="0" step="0.01" required>
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
function page(data){
    return {
        items:data, search:'', modal:false, editing:false, submitting:false,
        form:{id:'',libelle:'',type_bourse:'Pourcentage',valeur:0},
        get filtered(){
            const q=this.search.toLowerCase();
            return q ? this.items.filter(b=>b.libelle.toLowerCase().includes(q)||b.type_bourse.toLowerCase().includes(q)) : this.items;
        },
        openCreate(){ this.editing=false; this.submitting=false; this.form={id:'',libelle:'',type_bourse:'Pourcentage',valeur:0}; this.modal=true; },
        openEdit(b){ this.editing=true; this.submitting=false; this.form={...b}; this.modal=true; },
    }
}
</script>
@endpush
</x-app-layout>
