<x-app-layout title="Modalités de Paiement — Scolarité">
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
$data = $modalites->map(fn($m) => [
    'id'                 => $m->id,
    'id_frais_scolarite' => $m->id_frais_scolarite,
    'frais_label'        => ($m->fraisScolarite?->typeFrais?->libelle ?? '—').' · '.($m->fraisScolarite?->niveau?->code ?? ''),
    'type_libelle'       => $m->fraisScolarite?->typeFrais?->libelle ?? '—',
    'niveau_code'        => $m->fraisScolarite?->niveau?->code ?? '—',
    'tranche'            => $m->tranche,
    'pourcentage'        => $m->pourcentage,
    'date_debut'         => $m->date_debut,
    'date_fin'           => $m->date_fin,
]);

$fraisJson   = $frais->map(fn($f) => [
    'id'     => $f->id,
    'libelle'=> ($f->typeFrais?->libelle ?? '?').' — '.($f->niveau?->libelle ?? ($f->niveau?->code ?? '?')).' ('.number_format($f->montant, 0, ',', ' ').' F)',
    'niveau_id' => $f->id_niveau,
]);
$niveauxJson = $niveaux->map(fn($n) => ['id' => $n->id, 'libelle' => $n->libelle, 'code' => $n->code ?? '']);
@endphp

<div x-data="page({{ $data }}, {{ $fraisJson }}, {{ $niveauxJson }})" class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Modalités / Tranches de Paiement</h1>
            @if($anneeActive)
            <p class="text-sm mt-0.5" style="color:#94A3B8">
                <span style="color:var(--primary);font-weight:600">{{ $anneeActive->libelle }}</span>
                — <span x-text="filtered.length+' enregistrement(s)'"></span>
            </p>
            @else
            <p class="text-sm mt-0.5" style="color:#EF4444">Aucune année scolaire active</p>
            @endif
        </div>
        <button @click="openCreate()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition" style="background:var(--primary)">
            <i class="ri-add-line text-base"></i> Nouvelle Tranche
        </button>
    </div>

    {{-- Warning --}}
    @unless($anneeActive)
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl" style="background:#FFF7ED;border:1px solid #FED7AA">
        <i class="ri-alert-line text-lg" style="color:#F97316"></i>
        <span class="text-sm font-medium" style="color:#9A3412">Définissez une année scolaire active avant de créer des modalités.
            <a href="{{ route('annees.index') }}" style="color:var(--primary);text-decoration:underline">Gérer les années</a>
        </span>
    </div>
    @endunless

    @if($frais->isEmpty())
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl" style="background:#FFF7ED;border:1px solid #FED7AA">
        <i class="ri-alert-line text-lg" style="color:#F97316"></i>
        <span class="text-sm font-medium" style="color:#9A3412">Aucun frais de scolarité configuré pour l'année active.
            <a href="{{ route('frais.index') }}" style="color:var(--primary);text-decoration:underline">Configurer les frais</a>
        </span>
    </div>
    @endif

    {{-- Filtres --}}
    <div class="flex items-center gap-3">
        <div x-data="{open:false}" class="relative" @click.outside="open=false">
            <button @click="open=!open" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white hover:bg-gray-50" :style="filterNiveau?'border-color:var(--primary);color:var(--primary)':''">
                <i class="ri-stack-line text-sm"></i>
                <span x-text="filterNiveau ? niveaux.find(n=>n.id==filterNiveau)?.libelle : 'Tous les niveaux'"></span>
                <i class="ri-arrow-down-s-line text-sm"></i>
            </button>
            <div x-show="open" class="ss-drop" style="min-width:180px">
                <div @click="filterNiveau='';open=false" class="ss-item" :class="!filterNiveau?'ss-sel':''">Tous les niveaux</div>
                <template x-for="n in niveaux" :key="n.id">
                    <div @click="filterNiveau=n.id;open=false" class="ss-item" :class="filterNiveau===n.id?'ss-sel':''" x-text="n.libelle+' ('+n.code+')'"></div>
                </template>
            </div>
        </div>
        <div class="relative">
            <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1"></i>
            <input x-model="search" type="text" placeholder="Rechercher..." class="pl-9 pr-4 py-2 rounded-xl text-sm border border-gray-200 bg-white outline-none focus:border-indigo-300" style="min-width:200px">
        </div>
        <button x-show="filterNiveau||search" @click="filterNiveau='';search=''" class="flex items-center gap-1 px-3 py-2 rounded-xl text-sm" style="color:#EF4444">
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
            <i class="ri-wallet-3-line text-4xl" style="color:#CBD5E1"></i>
            <p class="mt-3 text-sm" style="color:#94A3B8">Aucune modalité de paiement configurée</p>
        </div>
        <table x-show="filtered.length" class="w-full">
            <thead class="border-b border-gray-100">
                <tr>
                    <th class="tbl-th">Frais de Scolarité</th>
                    <th class="tbl-th">Tranche</th>
                    <th class="tbl-th">Pourcentage</th>
                    <th class="tbl-th">Délai (Fin)</th>
                    <th class="tbl-th text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="m in filtered" :key="m.id">
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                        <td class="tbl-td">
                            <div class="font-semibold text-sm" style="color:#1E293B" x-text="m.type_libelle"></div>
                            <div class="text-xs mt-0.5" style="color:#94A3B8" x-text="m.niveau_code"></div>
                        </td>
                        <td class="tbl-td">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:rgba(90,103,216,.1);color:var(--primary)" x-text="m.tranche"></span>
                        </td>
                        <td class="tbl-td font-bold" style="color:#475569" x-text="m.pourcentage+' %'"></td>
                        <td class="tbl-td text-xs" style="color:#94A3B8" x-text="m.date_fin || '—'"></td>
                        <td class="tbl-td">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEdit(m)" class="act-btn hover:bg-indigo-50" style="color:#6366f1"><i class="ri-edit-2-line text-[15px]"></i></button>
                                <form :action="'/modalites-paiement/'+m.id" method="POST" style="display:inline" @submit.prevent="if(confirm('Supprimer cette tranche ?')) $el.submit()">
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
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="editing?'Modifier Modalité':'Nouvelle Modalité / Tranche'"></h2>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <form :action="editing?'/modalites-paiement/'+form.id:'/modalites-paiement'" method="POST" @submit="submitting=true" class="px-6 py-5 space-y-4">
                    @csrf
                    <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="f-label">Frais de Scolarité <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(frais.map(f=>({v:f.id,l:f.libelle})), form.id_frais_scolarite, 'Sélectionner un frais...')"
                             class="relative" @click.outside="open=false">
                            <input type="hidden" name="id_frais_scolarite" :value="v">
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

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="f-label">Nom de la tranche <span style="color:#EF4444">*</span></label>
                            <input type="text" name="tranche" :value="form.tranche" class="f-input" placeholder="Ex: Tranche 1" required>
                        </div>
                        <div>
                            <label class="f-label">Pourcentage (%)</label>
                            <input type="number" name="pourcentage" :value="form.pourcentage" class="f-input" placeholder="0" min="0" max="100" step="0.01">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="f-label">Date de début</label>
                            <input type="date" name="date_debut" :value="form.date_debut" class="f-input">
                        </div>
                        <div>
                            <label class="f-label">Date de fin</label>
                            <input type="date" name="date_fin" :value="form.date_fin" class="f-input">
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
function page(data, frais, niveaux){
    return {
        items:data, frais:frais, niveaux:niveaux,
        search:'', filterNiveau:'',
        modal:false, editing:false, submitting:false,
        form:{id:'',id_frais_scolarite:'',tranche:'',pourcentage:0,date_debut:'',date_fin:''},
        get filtered(){
            let list = this.items;
            if(this.filterNiveau) {
                const ids = this.frais.filter(f=>f.niveau_id==this.filterNiveau).map(f=>f.id);
                list = list.filter(m=>ids.includes(m.id_frais_scolarite));
            }
            const q = this.search.toLowerCase();
            if(q) list = list.filter(m=>m.tranche.toLowerCase().includes(q)||m.type_libelle.toLowerCase().includes(q));
            return list;
        },
        openCreate(){ this.editing=false; this.submitting=false; this.form={id:'',id_frais_scolarite:'',tranche:'',pourcentage:0,date_debut:'',date_fin:''}; this.modal=true; },
        openEdit(m){ this.editing=true; this.submitting=false; this.form={...m}; this.modal=true; },
    }
}
</script>
@endpush
</x-app-layout>
