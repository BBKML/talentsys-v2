<x-app-layout title="Frais de Scolarité — Scolarité">
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
$data       = $frais->map(fn($f) => [
    'id'            => $f->id,
    'id_type_frais' => $f->id_type_frais,
    'type_libelle'  => $f->typeFrais?->libelle ?? '—',
    'id_niveau'     => $f->id_niveau,
    'niveau_code'   => $f->niveau?->code ?? '—',
    'niveau_libelle'=> $f->niveau?->libelle ?? '—',
    'annee_libelle' => $f->annee?->libelle ?? '—',
    'montant'       => $f->montant,
]);
$typesFraisJson = $typesFrais->map(fn($t) => ['id' => $t->id, 'libelle' => $t->libelle]);
$niveauxJson    = $niveaux->map(fn($n) => ['id' => $n->id, 'libelle' => $n->libelle, 'code' => $n->code ?? '']);
@endphp

<div x-data="page({{ $data }}, {{ $typesFraisJson }}, {{ $niveauxJson }})" class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Frais de Scolarité</h1>
            @if($anneeActive)
            <p class="text-sm mt-0.5" style="color:#94A3B8">
                <span style="color:var(--primary);font-weight:600">{{ $anneeActive->libelle }}</span>
                — <span x-text="items.length+' tarif(s)'"></span>
            </p>
            @else
            <p class="text-sm mt-0.5" style="color:#EF4444">Aucune année scolaire active</p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            {{-- Total --}}
            <div x-show="items.length" class="px-4 py-2 rounded-xl text-sm font-bold" style="background:rgba(90,103,216,.08);color:var(--primary);border:1px solid rgba(90,103,216,.2)">
                Total : <span x-text="Number(items.reduce((s,f)=>s+Number(f.montant),0)).toLocaleString('fr-FR')+' FCFA'"></span>
            </div>
            <button @click="openCreate()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition" style="background:var(--primary)">
                <i class="ri-add-line text-base"></i> Nouveau Frais
            </button>
        </div>
    </div>

    {{-- Warning si pas d'année active --}}
    @unless($anneeActive)
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl" style="background:#FFF7ED;border:1px solid #FED7AA">
        <i class="ri-alert-line text-lg" style="color:#F97316"></i>
        <span class="text-sm font-medium" style="color:#9A3412">Définissez une année scolaire active avant de créer des frais.
            <a href="{{ route('annees.index') }}" style="color:var(--primary);text-decoration:underline">Gérer les années</a>
        </span>
    </div>
    @endunless

    {{-- Filtres --}}
    <div class="flex items-center gap-3">
        <div x-data="{open:false}" class="relative" @click.outside="open=false">
            <button @click="open=!open" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white hover:bg-gray-50" :style="filterType?'border-color:var(--primary);color:var(--primary)':''">
                <i class="ri-filter-3-line text-sm"></i>
                <span x-text="filterType ? typesFrais.find(t=>t.id==filterType)?.libelle : 'Tous les types'"></span>
                <i class="ri-arrow-down-s-line text-sm"></i>
            </button>
            <div x-show="open" class="ss-drop" style="min-width:180px">
                <div @click="filterType='';open=false" class="ss-item" :class="!filterType?'ss-sel':''">Tous les types</div>
                <template x-for="t in typesFrais" :key="t.id">
                    <div @click="filterType=t.id;open=false" class="ss-item" :class="filterType===t.id?'ss-sel':''" x-text="t.libelle"></div>
                </template>
            </div>
        </div>

        <div x-data="{open:false}" class="relative" @click.outside="open=false">
            <button @click="open=!open" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white hover:bg-gray-50" :style="filterNiveau?'border-color:var(--primary);color:var(--primary)':''">
                <i class="ri-stack-line text-sm"></i>
                <span x-text="filterNiveau ? niveaux.find(n=>n.id==filterNiveau)?.libelle : 'Tous les niveaux'"></span>
                <i class="ri-arrow-down-s-line text-sm"></i>
            </button>
            <div x-show="open" class="ss-drop" style="min-width:180px">
                <div @click="filterNiveau='';open=false" class="ss-item" :class="!filterNiveau?'ss-sel':''">Tous les niveaux</div>
                <template x-for="n in niveaux" :key="n.id">
                    <div @click="filterNiveau=n.id;open=false" class="ss-item" :class="filterNiveau===n.id?'ss-sel':''" x-text="n.libelle"></div>
                </template>
            </div>
        </div>

        <button x-show="filterType||filterNiveau" @click="filterType='';filterNiveau=''" class="flex items-center gap-1 px-3 py-2 rounded-xl text-sm" style="color:#EF4444">
            <i class="ri-close-line"></i> Effacer
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="px-4 py-3 rounded-xl text-sm font-medium" style="background:#DCFCE7;color:#166534">{{ session('success') }}</div>
    @endif

    {{-- Tableau --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div x-show="!filtered.length" class="py-16 text-center">
            <i class="ri-money-dollar-circle-line text-4xl" style="color:#CBD5E1"></i>
            <p class="mt-3 text-sm" style="color:#94A3B8">Aucun frais de scolarité configuré</p>
        </div>
        <table x-show="filtered.length" class="w-full">
            <thead class="border-b border-gray-100">
                <tr>
                    <th class="tbl-th">Type de Frais</th>
                    <th class="tbl-th">Niveau</th>
                    <th class="tbl-th">Année</th>
                    <th class="tbl-th">Montant</th>
                    <th class="tbl-th text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="f in filtered" :key="f.id">
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                        <td class="tbl-td">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:rgba(90,103,216,.1);color:var(--primary)" x-text="f.type_libelle"></span>
                        </td>
                        <td class="tbl-td">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:rgba(20,184,166,.1);color:#0d9488" x-text="f.niveau_code"></span>
                        </td>
                        <td class="tbl-td text-xs" style="color:#94A3B8" x-text="f.annee_libelle"></td>
                        <td class="tbl-td font-bold" style="color:var(--primary)"
                            x-text="Number(f.montant).toLocaleString('fr-FR')+' FCFA'">
                        </td>
                        <td class="tbl-td">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEdit(f)" class="act-btn hover:bg-indigo-50" style="color:#6366f1"><i class="ri-edit-2-line text-[15px]"></i></button>
                                <form :action="'/frais-scolarite/'+f.id" method="POST" style="display:inline" @submit.prevent="if(confirm('Supprimer ce frais ?')) $el.submit()">
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
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="editing?'Modifier Frais':'Nouveau Frais de Scolarité'"></h2>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <form :action="editing?'/frais-scolarite/'+form.id:'/frais-scolarite'" method="POST" @submit="submitting=true" class="px-6 py-5 space-y-4">
                    @csrf
                    <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="f-label">Type de Frais <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(typesFrais.map(t=>({v:t.id,l:t.libelle})), form.id_type_frais, 'Rechercher un type...')"
                             class="relative" @click.outside="open=false">
                            <input type="hidden" name="id_type_frais" :value="v">
                            <div class="relative">
                                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1;pointer-events:none"></i>
                                <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" style="padding-left:32px" :placeholder="ph" autocomplete="off">
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
                        <label class="f-label">Niveau <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(niveaux.map(n=>({v:n.id,l:n.libelle+' ('+n.code+')'})), form.id_niveau, 'Rechercher un niveau...')"
                             class="relative" @click.outside="open=false">
                            <input type="hidden" name="id_niveau" :value="v">
                            <div class="relative">
                                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1;pointer-events:none"></i>
                                <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" style="padding-left:32px" :placeholder="ph" autocomplete="off">
                            </div>
                            <div x-show="open" class="ss-drop">
                                <div x-show="!filtered.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucun résultat</div>
                                <template x-for="o in filtered" :key="o.v">
                                    <div @click="select(o)" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                </template>
                            </div>
                        </div>
                    </div>

                    @if($anneeActive)
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl" style="background:#F1F5F9">
                        <i class="ri-calendar-check-line text-sm" style="color:var(--primary)"></i>
                        <span class="text-xs font-medium" style="color:#475569">Année scolaire : <strong>{{ $anneeActive->libelle }}</strong></span>
                    </div>
                    @endif

                    <div>
                        <label class="f-label">Montant (FCFA) <span style="color:#EF4444">*</span></label>
                        <input type="number" name="montant" :value="form.montant" class="f-input" placeholder="0" min="0" step="1" required>
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
function page(data, typesFrais, niveaux){
    return {
        items:data, typesFrais:typesFrais, niveaux:niveaux,
        filterType:'', filterNiveau:'',
        modal:false, editing:false, submitting:false,
        form:{id:'',id_type_frais:'',id_niveau:'',montant:0},
        get filtered(){
            let list = this.items;
            if(this.filterType) list = list.filter(f=>f.id_type_frais==this.filterType);
            if(this.filterNiveau) list = list.filter(f=>f.id_niveau==this.filterNiveau);
            return list;
        },
        openCreate(){ this.editing=false; this.submitting=false; this.form={id:'',id_type_frais:'',id_niveau:'',montant:0}; this.modal=true; },
        openEdit(f){ this.editing=true; this.submitting=false; this.form={...f}; this.modal=true; },
    }
}
</script>
@endpush
</x-app-layout>
