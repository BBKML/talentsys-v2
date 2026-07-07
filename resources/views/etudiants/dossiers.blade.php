<x-app-layout>
    <x-slot name="title">Suivi des Dossiers justificatifs</x-slot>
    
    @push('styles')
    <style>
        .badge-valide { background: #D1FAE5; color: #065F46; }
        .badge-attente { background: #FEF3C7; color: #92400E; }
        .badge-rejete { background: #FEE2E2; color: #991B1B; }
    </style>
    @endpush

    @php
        $dossiersJson = $dossiers->map(fn($d) => [
            'id' => $d->id,
            'etudiant_lib' => $d->etudiant ? $d->etudiant->nom . ' ' . $d->etudiant->prenom : '—',
            'matricule' => $d->etudiant ? $d->etudiant->matricule : '—',
            'type_doc_lib' => $d->typeDocument ? $d->typeDocument->libelle : '—',
            'url_document' => $d->url_document,
            'date_ajout' => $d->date_ajout,
            'id_statut' => (int)$d->id_statut,
            'statut_lib' => $d->status ? $d->status->libelle : 'En attente'
        ])->toJson();

        $statutsJson = $statuts->map(fn($s) => ['id' => $s->id, 'libelle' => $s->libelle])->toJson();
    @endphp

    <div x-data="dossierPage({{ $dossiersJson }}, {{ $statutsJson }})" class="space-y-6">

        <!-- Title Row -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Suivi des Dossiers Justificatifs</h1>
                <p class="text-sm text-slate-500 mt-1" x-text="filteredDossiers().length + ' document(s) répertorié(s)'"></p>
            </div>
        </div>

        <!-- Table Panel -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100">
                <input type="text" x-model="searchQuery" placeholder="Rechercher par étudiant, matricule, type document..." class="w-full max-w-xs border border-slate-200 rounded-lg px-3 py-2 text-xs outline-none bg-slate-50">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="p-4">MATRICULE</th>
                            <th class="p-4">ÉTUDIANT</th>
                            <th class="p-4">TYPE DE DOCUMENT</th>
                            <th class="p-4">DATE D'AJOUT</th>
                            <th class="p-4">FICHIER</th>
                            <th class="p-4">STATUT</th>
                            <th class="p-4 text-right">ACTION STATUT</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        <template x-for="d in filteredDossiers()" :key="d.id">
                            <tr class="hover:bg-slate-50/20">
                                <td class="p-4 font-mono font-bold text-indigo-600" x-text="d.matricule"></td>
                                <td class="p-4 font-bold text-slate-800" x-text="d.etudiant_lib"></td>
                                <td class="p-4 text-slate-600" x-text="d.type_doc_lib"></td>
                                <td class="p-4 text-slate-500" x-text="d.date_ajout"></td>
                                <td class="p-4">
                                    <template x-if="d.url_document">
                                        <a :href="d.url_document" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                                            <i class="ri-external-link-line"></i> Ouvrir
                                        </a>
                                    </template>
                                    <template x-if="!d.url_document">
                                        <span class="text-slate-400 italic">Aucun</span>
                                    </template>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="d.id_statut === 1 ? 'badge-valide' : (d.id_statut === 2 ? 'badge-rejete' : 'badge-attente')" x-text="d.statut_lib"></span>
                                </td>
                                <td class="p-4 text-right">
                                    <select :value="d.id_statut" @change="updateStatus(d.id, $event.target.value)" class="text-xs border border-slate-200 rounded px-2 py-1 bg-white outline-none">
                                        <template x-for="s in statuts" :key="s.id">
                                            <option :value="s.id" x-text="s.libelle"></option>
                                        </template>
                                    </select>
                                </td>
                            </tr>
                        </template>
                        <template x-if="filteredDossiers().length === 0">
                            <tr>
                                <td colspan="7" class="text-center py-8 text-slate-400 italic">Aucun document justificatif trouvé.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dossierPage', (dossiers, statuts) => ({
                dossiers, statuts,
                searchQuery: '',

                filteredDossiers() {
                    const q = this.searchQuery.toLowerCase();
                    return this.dossiers.filter(d => {
                        return !q || d.etudiant_lib.toLowerCase().includes(q) || d.matricule.toLowerCase().includes(q) || d.type_doc_lib.toLowerCase().includes(q);
                    });
                },

                async updateStatus(id, newStatusId) {
                    try {
                        const r = await fetch(`/etudiants/dossiers/${id}/statut`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ id_statut: newStatusId })
                        });
                        const res = await r.json();
                        if (res.success) {
                            alert(res.message);
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    }
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
