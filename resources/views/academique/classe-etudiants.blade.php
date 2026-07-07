<x-app-layout title="Étudiants — {{ $classe->libelle }}">
@push('styles')
<style>
.tbl-th{font-size:11px;font-weight:600;color:#94A3B8;text-transform:uppercase;letter-spacing:.07em;padding:11px 16px;text-align:left}
.tbl-td{padding:13px 16px;font-size:13px;color:#475569}
</style>
@endpush

@php
$data = $inscriptions->map(fn($ins) => [
    'nom'                => $ins->nom,
    'prenom'             => $ins->prenom,
    'matricule'          => $ins->matricule ?? '',
    'numero_inscription' => $ins->numero_inscription ?? '',
    'type_inscription'   => $ins->type_inscription ?? '',
    'date_inscription'   => $ins->date_inscription
        ? \Carbon\Carbon::parse($ins->date_inscription)->format('d/m/Y')
        : '',
    'annee_libelle'      => $ins->annee_libelle ?? '',
]);
@endphp

<div x-data="page({{ $data }})" class="space-y-5">

    {{-- Breadcrumb & Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('classes.index') }}" class="flex items-center gap-1.5 text-sm font-medium hover:opacity-80" style="color:var(--primary)">
            <i class="ri-arrow-left-line"></i> Classes
        </a>
        <i class="ri-arrow-right-s-line text-sm" style="color:#CBD5E1"></i>
        <span class="text-sm font-semibold" style="color:#1E293B">{{ $classe->libelle }}</span>
    </div>

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold" style="color:#1E293B">{{ $classe->libelle }}</h1>
                @if($classe->filiere)
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:#F1F5F9;color:#64748B">{{ $classe->filiere->libelle }}</span>
                @endif
                @if($classe->niveau)
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:rgba(99,102,241,.12);color:#4f46e5">{{ $classe->niveau->code }}</span>
                @endif
            </div>
            <p class="text-sm mt-0.5" style="color:#94A3B8">
                <span x-text="filtered.length"></span> / {{ $inscriptions->count() }} étudiant(s) inscrit(s)
            </p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
            <div class="relative">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1"></i>
                <input x-model="search" type="text" placeholder="Rechercher un étudiant..." class="pl-9 pr-4 py-2 rounded-xl text-sm border border-gray-100 bg-gray-50 outline-none focus:border-indigo-300" style="min-width:260px">
            </div>
        </div>

        <div x-show="!filtered.length" class="py-16 text-center">
            <i class="ri-user-search-line text-4xl" style="color:#CBD5E1"></i>
            <p class="mt-3 text-sm" style="color:#94A3B8">Aucun étudiant trouvé</p>
        </div>

        <table x-show="filtered.length" class="w-full">
            <thead class="border-b border-gray-100">
                <tr>
                    <th class="tbl-th">Matricule</th>
                    <th class="tbl-th">Nom & Prénom</th>
                    <th class="tbl-th">N° Inscription</th>
                    <th class="tbl-th">Type</th>
                    <th class="tbl-th">Date Inscription</th>
                    <th class="tbl-th">Année</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(ins, i) in filtered" :key="i">
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                        <td class="tbl-td">
                            <span x-show="ins.matricule" class="font-mono text-xs px-2 py-0.5 rounded" style="background:#F1F5F9;color:#475569" x-text="ins.matricule"></span>
                            <span x-show="!ins.matricule" style="color:#CBD5E1">—</span>
                        </td>
                        <td class="tbl-td font-semibold" style="color:#1E293B">
                            <span x-text="ins.nom+' '+ins.prenom"></span>
                        </td>
                        <td class="tbl-td text-xs font-mono" style="color:#64748B" x-text="ins.numero_inscription||'—'"></td>
                        <td class="tbl-td">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                  :style="typeStyle(ins.type_inscription)"
                                  x-text="ins.type_inscription||'—'">
                            </span>
                        </td>
                        <td class="tbl-td text-xs" style="color:#94A3B8" x-text="ins.date_inscription||'—'"></td>
                        <td class="tbl-td text-xs" style="color:#94A3B8" x-text="ins.annee_libelle||'—'"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
function page(data){
    return {
        items: data,
        search: '',
        get filtered(){
            if(!this.search) return this.items;
            const q = this.search.toLowerCase();
            return this.items.filter(ins =>
                (ins.nom+' '+ins.prenom).toLowerCase().includes(q) ||
                ins.prenom.toLowerCase().includes(q) ||
                ins.matricule.toLowerCase().includes(q) ||
                ins.numero_inscription.toLowerCase().includes(q)
            );
        },
    }
}

function typeStyle(t){
    if(t==='Nouvelle')      return 'background:#DCFCE7;color:#166534';
    if(t==='Réinscription') return 'background:#DBEAFE;color:#1d4ed8';
    if(t==='Transfert')     return 'background:#FEF3C7;color:#92400e';
    return 'background:#F1F5F9;color:#64748B';
}
</script>
@endpush
</x-app-layout>
