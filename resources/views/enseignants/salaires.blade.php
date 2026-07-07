<x-app-layout title="Salaires Enseignants">
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
$ensOpts = $enseignants->map(fn($e) => ['v' => $e->id, 'l' => $e->prenom.' '.$e->nom.' ('.$e->grade.')']);

$salairesData = $salaires->map(fn($s) => [
    'id'            => $s->id,
    'ens_id'        => $s->id_enseignant,
    'enseignant'    => $s->enseignant ? $s->enseignant->prenom.' '.$s->enseignant->nom : '—',
    'mois'          => $s->mois,
    'brut'          => (float)$s->salaire_brut,
    'cnps'          => (float)$s->retenue_cnps,
    'ir'            => (float)$s->retenue_ir,
    'autres'        => (float)$s->autres_retenues,
    'net'           => (float)$s->salaire_net,
    'statut'        => $s->statut,
    'reference'     => $s->reference ?? '',
    'date_paiement' => $s->date_paiement?->format('d/m/Y') ?? '',
]);

// KPI
$totalBrut   = $salaires->sum('salaire_brut');
$totalNet    = $salaires->sum('salaire_net');
$totalPaye   = $salaires->where('statut','payé')->sum('salaire_net');
$totalAttente= $salaires->where('statut','en_attente')->sum('salaire_net');

// Mois disponibles
$moisDispo = $salaires->pluck('mois')->unique()->sortDesc()->values();
@endphp

<div x-data="salPage({{ $salairesData }}, {{ $ensOpts }})" class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Salaires Enseignants</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8">Gestion des fiches de paie</p>
        </div>
        <button @click="openCreate()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition" style="background:var(--primary)">
            <i class="ri-add-line text-base"></i> Nouvelle Fiche
        </button>
    </div>

    {{-- KPI --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:color-mix(in srgb, var(--primary) 8%, white)">
                <i class="ri-money-dollar-circle-fill text-lg" style="color:var(--primary)"></i>
            </div>
            <div>
                <p class="text-xs font-semibold" style="color:#94A3B8">Total Brut</p>
                <p class="text-base font-bold" style="color:#1E293B">{{ number_format($totalBrut, 0, ',', ' ') }}<span class="text-xs font-normal ml-1">FCFA</span></p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:color-mix(in srgb, var(--primary) 8%, white)">
                <i class="ri-secure-payment-fill text-lg" style="color:var(--primary)"></i>
            </div>
            <div>
                <p class="text-xs font-semibold" style="color:#94A3B8">Total Net</p>
                <p class="text-base font-bold" style="color:var(--primary)">{{ number_format($totalNet, 0, ',', ' ') }}<span class="text-xs font-normal ml-1">FCFA</span></p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#F0FDF4">
                <i class="ri-checkbox-circle-fill text-lg" style="color:#059669"></i>
            </div>
            <div>
                <p class="text-xs font-semibold" style="color:#94A3B8">Total Payé</p>
                <p class="text-base font-bold" style="color:#059669">{{ number_format($totalPaye, 0, ',', ' ') }}<span class="text-xs font-normal ml-1">FCFA</span></p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#FFF7ED">
                <i class="ri-time-fill text-lg" style="color:#d97706"></i>
            </div>
            <div>
                <p class="text-xs font-semibold" style="color:#94A3B8">Restant</p>
                <p class="text-base font-bold" style="color:#d97706">{{ number_format($totalAttente, 0, ',', ' ') }}<span class="text-xs font-normal ml-1">FCFA</span></p>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="flex items-center gap-3 flex-wrap">
        <select x-model="filterMois" class="f-input" style="width:auto;min-width:160px">
            <option value="">Tous les mois</option>
            @foreach($moisDispo as $m)
            <option value="{{ $m }}">{{ \Carbon\Carbon::createFromFormat('Y-m', $m)->translatedFormat('F Y') }}</option>
            @endforeach
        </select>
        <span class="text-xs px-3 py-1.5 rounded-lg" style="background:color-mix(in srgb, var(--primary) 8%, white);color:var(--primary)" x-text="filtered.length+' fiche(s)'"></span>
    </div>

    {{-- Tableau --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div x-show="!filtered.length" class="py-16 text-center">
            <i class="ri-money-cny-circle-line text-4xl" style="color:#CBD5E1"></i>
            <p class="mt-3 text-sm" style="color:#94A3B8">Aucune fiche de salaire</p>
        </div>
        <div class="overflow-x-auto" x-show="filtered.length">
            <table class="w-full">
                <thead class="border-b border-gray-100">
                    <tr>
                        <th class="tbl-th">Enseignant</th>
                        <th class="tbl-th">Mois</th>
                        <th class="tbl-th">Brut</th>
                        <th class="tbl-th">Retenues</th>
                        <th class="tbl-th">Net</th>
                        <th class="tbl-th">Statut</th>
                        <th class="tbl-th">Référence</th>
                        <th class="tbl-th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="s in filtered" :key="s.id">
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                            <td class="tbl-td">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                                         style="background:rgba(13,148,136,.15);color:#0d9488"
                                         x-text="s.enseignant.split(' ').map(w=>w.charAt(0)).slice(0,2).join('').toUpperCase()"></div>
                                    <span class="font-semibold text-sm" style="color:#1E293B" x-text="s.enseignant"></span>
                                </div>
                            </td>
                            <td class="tbl-td font-semibold" x-text="fmtMois(s.mois)"></td>
                            <td class="tbl-td" x-text="fmt(s.brut)+' FCFA'"></td>
                            <td class="tbl-td">
                                <span style="color:#ef4444;font-weight:600" x-text="'- '+fmt(s.cnps+s.ir+s.autres)+' FCFA'"></span>
                                <p class="text-[10px]" style="color:#94A3B8" x-text="'CNPS:'+fmt(s.cnps)+' IR:'+fmt(s.ir)+' Autres:'+fmt(s.autres)"></p>
                            </td>
                            <td class="tbl-td font-bold" style="color:#059669" x-text="fmt(s.net)+' FCFA'"></td>
                            <td class="tbl-td">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                      :style="s.statut==='payé'?'background:#DCFCE7;color:#166534':s.statut==='annulé'?'background:#FEE2E2;color:#991b1b':'background:#FEF3C7;color:#92400e'"
                                      x-text="s.statut"></span>
                            </td>
                            <td class="tbl-td text-xs" style="color:#64748B" x-text="s.reference || '—'"></td>
                            <td class="tbl-td">
                                <div class="flex items-center justify-end gap-1 flex-wrap">
                                    <template x-if="s.statut==='en_attente'">
                                        <form :action="'/salaires-enseignants/'+s.id+'/payer'" method="POST" style="display:inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold hover:opacity-90 whitespace-nowrap" style="background:#DCFCE7;color:#166534">
                                                <i class="ri-check-line"></i> Payer
                                            </button>
                                        </form>
                                    </template>
                                    <button @click="openEdit(s)" class="act-btn hover:bg-primary/5" style="color:var(--primary)"><i class="ri-edit-2-line text-[15px]"></i></button>
                                    <form :action="'/salaires-enseignants/'+s.id" method="POST" style="display:inline" @submit.prevent="if(confirm('Supprimer cette fiche ?')) $el.submit()">
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
    </div>

    {{-- Modal --}}
    <template x-if="modal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="editing?'Modifier Fiche Salaire':'Nouvelle Fiche Salaire'"></h2>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <form :action="editing?'/salaires-enseignants/'+form.id:'/salaires-enseignants'" method="POST" @submit="submitting=true" class="px-6 py-5 space-y-4">
                    @csrf
                    <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="f-label">Enseignant <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(ensOpts, form.ens_id, 'Sélectionner enseignant...')"
                             class="relative" @click.outside="open=false">
                            <input type="hidden" name="id_enseignant" :value="v">
                            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" :placeholder="ph" autocomplete="off">
                            <div x-show="open" class="ss-drop">
                                <div x-show="!filtered.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucun</div>
                                <template x-for="o in filtered" :key="o.v">
                                    <div @click="select(o)" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="f-label">Mois (YYYY-MM) <span style="color:#EF4444">*</span></label>
                        <input type="month" name="mois" :value="form.mois" class="f-input" required>
                    </div>

                    <div>
                        <label class="f-label">Salaire Brut (FCFA) <span style="color:#EF4444">*</span></label>
                        <input type="number" name="salaire_brut" x-model.number="form.brut" class="f-input" min="0" step="100" required>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="f-label">Retenue CNPS</label>
                            <input type="number" name="retenue_cnps" x-model.number="form.cnps" class="f-input" min="0" step="100">
                        </div>
                        <div>
                            <label class="f-label">Retenue IR</label>
                            <input type="number" name="retenue_ir" x-model.number="form.ir" class="f-input" min="0" step="100">
                        </div>
                        <div>
                            <label class="f-label">Autres retenues</label>
                            <input type="number" name="autres_retenues" x-model.number="form.autres" class="f-input" min="0" step="100">
                        </div>
                    </div>

                    {{-- Net calculé --}}
                    <div class="rounded-xl p-3" style="background:#F0FDF4;border:1px solid #BBF7D0">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold" style="color:#166534">Salaire Net calculé</p>
                            <p class="text-lg font-bold" style="color:#166534" x-text="fmt(net)+' FCFA'"></p>
                        </div>
                        <p class="text-xs mt-1" style="color:#4ade80">= Brut - CNPS - IR - Autres retenues</p>
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
function salPage(salairesData, ensOpts){
    return {
        items: salairesData,
        ensOpts,
        filterMois: '',
        modal: false, editing: false, submitting: false,
        form: {id:'', ens_id:'', mois:'', brut:0, cnps:0, ir:0, autres:0},
        get net(){ return Math.max(0, (this.form.brut||0) - (this.form.cnps||0) - (this.form.ir||0) - (this.form.autres||0)); },
        get filtered(){
            if(!this.filterMois) return this.items;
            return this.items.filter(s => s.mois === this.filterMois);
        },
        fmt(v){ return Number(v||0).toLocaleString('fr-FR'); },
        fmtMois(m){
            if(!m) return '—';
            const [y,mo] = m.split('-');
            const d = new Date(parseInt(y), parseInt(mo)-1, 1);
            return d.toLocaleDateString('fr-FR',{month:'long',year:'numeric'});
        },
        openCreate(){
            this.editing=false; this.submitting=false;
            this.form={id:'', ens_id:'', mois:'', brut:0, cnps:0, ir:0, autres:0};
            this.modal=true;
        },
        openEdit(s){
            this.editing=true; this.submitting=false;
            this.form={id:s.id, ens_id:s.ens_id, mois:s.mois, brut:s.brut, cnps:s.cnps, ir:s.ir, autres:s.autres};
            this.modal=true;
        },
    }
}
</script>
@endpush
</x-app-layout>
