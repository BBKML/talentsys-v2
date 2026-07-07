<x-app-layout title="Volume Horaire">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none;transition:all .15s}
.f-input:focus{background:#fff;box-shadow:0 0 0 2px var(--primary)44}
.tbl-th{font-size:11px;font-weight:600;color:#94A3B8;text-transform:uppercase;letter-spacing:.07em;padding:11px 16px;text-align:left}
.tbl-td{padding:13px 16px;font-size:13px;color:#475569}
.act-btn{width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;cursor:pointer;border:none;background:transparent}
.tab-btn{padding:8px 18px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;border:none}
.tab-btn.active{background:var(--primary);color:#fff}
.tab-btn:not(.active){background:#F1F5F9;color:#64748B}
</style>
@endpush

@php
$affOpts = $affectations->map(fn($a) => [
    'v' => $a->id,
    'l' => ($a->enseignant ? $a->enseignant->prenom.' '.$a->enseignant->nom : '?').' — '.($a->matiere?->libelle ?? '?'),
]);

$pointagesData = $pointages->map(fn($p) => [
    'id'           => $p->id,
    'aff_id'       => $p->id_affectation_enseignant,
    'enseignant'   => $p->affectation?->enseignant ? $p->affectation->enseignant->prenom.' '.$p->affectation->enseignant->nom : '—',
    'matiere'      => $p->affectation?->matiere?->libelle ?? '—',
    'quota'        => $p->affectation?->nombre_heure ?? 0,
    'arrive'       => $p->date_heures_arrive?->format('Y-m-d H:i') ?? '',
    'depart'       => $p->date_heures_depart?->format('Y-m-d H:i') ?? '',
    'arrive_fmt'   => $p->date_heures_arrive?->format('d/m H:i') ?? '—',
    'depart_fmt'   => $p->date_heures_depart?->format('d/m H:i') ?? '',
    'heures_done'  => isset($heuresByAff[$p->id_affectation_enseignant]) ? (float)$heuresByAff[$p->id_affectation_enseignant] : 0,
]);

$comptaData = $comptaList->map(fn($c) => [
    'id'             => $c->id,
    'aff_id'         => $c->id_affectation_enseignant,
    'date'           => $c->date?->format('Y-m-d') ?? '',
    'date_fmt'       => $c->date?->format('d/m/Y') ?? '—',
    'enseignant'     => $c->affectation?->enseignant ? $c->affectation->enseignant->prenom.' '.$c->affectation->enseignant->nom : '—',
    'matiere'        => $c->affectation?->matiere?->libelle ?? '—',
    'heures'         => (float)$c->heures_realisees,
    'montant'        => (float)$c->montant_total,
]);
@endphp

<div x-data="volPage({{ $pointagesData }}, {{ $comptaData }}, {{ $affOpts }})" class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Volume Horaire</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8">Pointage et comptabilité des heures enseignées</p>
        </div>
        <template x-if="tab==='pointage'">
            <button @click="openCreate('pointage')" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition" style="background:var(--primary)">
                <i class="ri-time-line text-base"></i> Pointer un Enseignant
            </button>
        </template>
        <template x-if="tab==='compta'">
            <button @click="openCreate('compta')" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition" style="background:#059669">
                <i class="ri-add-line text-base"></i> Ajouter Entrée
            </button>
        </template>
    </div>

    {{-- Onglets --}}
    <div class="flex gap-2">
        <button class="tab-btn" :class="tab==='pointage'?'active':''" @click="tab='pointage'">
            <i class="ri-time-line mr-1.5"></i>Pointage
        </button>
        <button class="tab-btn" :class="tab==='compta'?'active':''" @click="tab='compta'">
            <i class="ri-calculator-line mr-1.5"></i>Comptabilité
        </button>
    </div>

    {{-- === Onglet Pointage === --}}
    <div x-show="tab==='pointage'">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div x-show="!pointages.length" class="py-16 text-center">
                <i class="ri-timer-line text-4xl" style="color:#CBD5E1"></i>
                <p class="mt-3 text-sm" style="color:#94A3B8">Aucun pointage enregistré</p>
            </div>
            <div class="overflow-x-auto" x-show="pointages.length">
                <table class="w-full">
                    <thead class="border-b border-gray-100">
                        <tr>
                            <th class="tbl-th">Enseignant</th>
                            <th class="tbl-th">Matière</th>
                            <th class="tbl-th">Arrivée</th>
                            <th class="tbl-th">Départ</th>
                            <th class="tbl-th">Durée</th>
                            <th class="tbl-th">Quota</th>
                            <th class="tbl-th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="p in pointages" :key="p.id">
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="tbl-td font-semibold" style="color:#1E293B" x-text="p.enseignant"></td>
                                <td class="tbl-td" x-text="p.matiere"></td>
                                <td class="tbl-td">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:#DCFCE7;color:#166534" x-text="p.arrive_fmt"></span>
                                </td>
                                <td class="tbl-td">
                                    <template x-if="p.depart">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:#DBEAFE;color:#1d4ed8" x-text="p.depart_fmt"></span>
                                    </template>
                                    <template x-if="!p.depart">
                                        <span class="text-xs" style="color:#94A3B8">—</span>
                                    </template>
                                </td>
                                <td class="tbl-td" x-text="duree(p)"></td>
                                <td class="tbl-td" x-text="p.heures_done+'h / '+p.quota+'h'"></td>
                                <td class="tbl-td">
                                    <div class="flex items-center justify-end gap-1">
                                        <template x-if="!p.depart">
                                            <form :action="'/volume-horaire/'+p.id" method="POST" style="display:inline">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="id_affectation_enseignant" :value="p.aff_id">
                                                <input type="hidden" name="date_heures_arrive" :value="p.arrive">
                                                <input type="hidden" name="date_heures_depart" :value="now()">
                                                <button type="submit" class="flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold hover:opacity-90" style="background:#DCFCE7;color:#166534">
                                                    <i class="ri-check-line"></i> Terminé
                                                </button>
                                            </form>
                                        </template>
                                        <button @click="openEdit('pointage', p)" class="act-btn hover:bg-primary/5" style="color:var(--primary)"><i class="ri-edit-2-line text-[15px]"></i></button>
                                        <form :action="'/volume-horaire/'+p.id" method="POST" style="display:inline" @submit.prevent="if(confirm('Supprimer ce pointage ?')) $el.submit()">
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
    </div>

    {{-- === Onglet Comptabilité === --}}
    <div x-show="tab==='compta'">
        {{-- KPI --}}
        <div class="grid grid-cols-3 gap-4 mb-5">
            <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:color-mix(in srgb, var(--primary) 8%, white)">
                    <i class="ri-time-fill text-lg" style="color:var(--primary)"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold" style="color:#94A3B8">Total Heures</p>
                    <p class="text-xl font-bold" style="color:#1E293B">{{ number_format($totalHeures, 1) }}h</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#F0FDF4">
                    <i class="ri-money-cny-circle-fill text-lg" style="color:#059669"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold" style="color:#94A3B8">Montant Total</p>
                    <p class="text-xl font-bold" style="color:#1E293B">{{ number_format($totalMontant, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#FFF7ED">
                    <i class="ri-file-list-3-fill text-lg" style="color:#d97706"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold" style="color:#94A3B8">Nb Entrées</p>
                    <p class="text-xl font-bold" style="color:#1E293B">{{ $nbEntrees }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div x-show="!comptas.length" class="py-16 text-center">
                <i class="ri-calculator-line text-4xl" style="color:#CBD5E1"></i>
                <p class="mt-3 text-sm" style="color:#94A3B8">Aucune entrée comptable</p>
            </div>
            <div class="overflow-x-auto" x-show="comptas.length">
                <table class="w-full">
                    <thead class="border-b border-gray-100">
                        <tr>
                            <th class="tbl-th">Date</th>
                            <th class="tbl-th">Enseignant</th>
                            <th class="tbl-th">Matière</th>
                            <th class="tbl-th">Heures</th>
                            <th class="tbl-th">Montant</th>
                            <th class="tbl-th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="c in comptas" :key="c.id">
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="tbl-td" x-text="c.date_fmt"></td>
                                <td class="tbl-td font-semibold" style="color:#1E293B" x-text="c.enseignant"></td>
                                <td class="tbl-td" x-text="c.matiere"></td>
                                <td class="tbl-td font-bold" style="color:var(--primary)" x-text="c.heures+'h'"></td>
                                <td class="tbl-td font-bold" style="color:#059669" x-text="Number(c.montant).toLocaleString('fr-FR')+' FCFA'"></td>
                                <td class="tbl-td">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="openEdit('compta', c)" class="act-btn hover:bg-primary/5" style="color:var(--primary)"><i class="ri-edit-2-line text-[15px]"></i></button>
                                        <form :action="'/comptabilite-horaire/'+c.id" method="POST" style="display:inline" @submit.prevent="if(confirm('Supprimer ?')) $el.submit()">
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
    </div>

    {{-- Modal Pointage --}}
    <template x-if="modal && modalType==='pointage'">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="editing?'Modifier Pointage':'Nouveau Pointage'"></h2>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <form :action="editing?'/volume-horaire/'+form.id:'/volume-horaire'" method="POST" @submit="submitting=true" class="px-6 py-5 space-y-4">
                    @csrf
                    <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="f-label">Affectation (Enseignant — Matière) <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(affOpts, form.aff_id, 'Sélectionner...')"
                             class="relative" @click.outside="open=false">
                            <input type="hidden" name="id_affectation_enseignant" :value="v">
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
                        <label class="f-label">Heure d'arrivée <span style="color:#EF4444">*</span></label>
                        <input type="datetime-local" name="date_heures_arrive" :value="form.arrive" class="f-input" required>
                    </div>
                    <div>
                        <label class="f-label">Heure de départ <span style="color:#94A3B8">(optionnel)</span></label>
                        <input type="datetime-local" name="date_heures_depart" :value="form.depart" class="f-input">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="modal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                        <button type="submit" :disabled="submitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:var(--primary)" x-text="submitting?'...':'Enregistrer'"></button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- Modal Comptabilité --}}
    <template x-if="modal && modalType==='compta'">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="editing?'Modifier Entrée':'Nouvelle Entrée Comptable'"></h2>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <form :action="editing?'/comptabilite-horaire/'+form.id:'/comptabilite-horaire'" method="POST" @submit="submitting=true" class="px-6 py-5 space-y-4">
                    @csrf
                    <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="f-label">Affectation (Enseignant — Matière) <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(affOpts, form.aff_id, 'Sélectionner...')"
                             class="relative" @click.outside="open=false">
                            <input type="hidden" name="id_affectation_enseignant" :value="v">
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
                        <label class="f-label">Date <span style="color:#EF4444">*</span></label>
                        <input type="date" name="date" :value="form.date" class="f-input" required>
                    </div>
                    <div>
                        <label class="f-label">Heures réalisées <span style="color:#EF4444">*</span></label>
                        <input type="number" name="heures_realisees" :value="form.heures" class="f-input" min="0" step="0.5" required>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="modal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                        <button type="submit" :disabled="submitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:#059669" x-text="submitting?'...':'Enregistrer'"></button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function volPage(pointagesData, comptaData, affOpts){
    return {
        tab: 'pointage',
        pointages: pointagesData,
        comptas: comptaData,
        affOpts,
        modal: false, modalType: 'pointage', editing: false, submitting: false,
        form: {id:'', aff_id:'', arrive:'', depart:'', date:'', heures:''},
        duree(p){
            if(!p.arrive || !p.depart) return '—';
            const d = (new Date(p.depart) - new Date(p.arrive)) / 3600000;
            return d.toFixed(1)+'h';
        },
        now(){
            const n = new Date();
            return n.getFullYear()+'-'+String(n.getMonth()+1).padStart(2,'0')+'-'+String(n.getDate()).padStart(2,'0')+'T'+String(n.getHours()).padStart(2,'0')+':'+String(n.getMinutes()).padStart(2,'0');
        },
        openCreate(type){
            this.modalType=type; this.editing=false; this.submitting=false;
            this.form={id:'',aff_id:'',arrive:'',depart:'',date:'',heures:''};
            this.modal=true;
        },
        openEdit(type, item){
            this.modalType=type; this.editing=true; this.submitting=false;
            if(type==='pointage'){
                this.form={id:item.id, aff_id:item.aff_id, arrive:item.arrive, depart:item.depart, date:'', heures:''};
            } else {
                this.form={id:item.id, aff_id:item.aff_id, arrive:'', depart:'', date:item.date, heures:item.heures};
            }
            this.modal=true;
        },
    }
}
</script>
@endpush
</x-app-layout>
