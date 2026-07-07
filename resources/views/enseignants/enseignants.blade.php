<x-app-layout title="Enseignants">
@push('styles')
<style>
/* ===== Flutter Design System ===== */
:root {
    --teal-50: color-mix(in srgb, var(--primary) 5%, white);
    --teal-100: color-mix(in srgb, var(--primary) 10%, white);
    --teal-200: color-mix(in srgb, var(--primary) 20%, white);
    --teal-600: var(--primary);
    --teal-700: color-mix(in srgb, var(--primary) 90%, black);
    --teal-800: color-mix(in srgb, var(--primary) 80%, black);
    --slate-50: #f8fafc;
    --slate-100: #f1f5f9;
    --slate-200: #e2e8f0;
    --slate-300: #cbd5e1;
    --slate-400: #94a3b8;
    --slate-500: #64748b;
    --slate-600: #475569;
    --slate-700: #334155;
    --slate-800: #1e293b;
    --slate-900: #0f172a;
    --red-500: #ef4444;
    --blue-500: #3b82f6;
    --radius: 12px;
    --radius-sm: 8px;
    --radius-full: 9999px;
    --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    --shadow-lg: 0 10px 40px rgba(0,0,0,0.08);
    --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Flutter-inspired Typography */
.f-text-body { font-size: 13px; color: var(--slate-600); line-height: 1.5; }
.f-text-body-bold { font-size: 13px; font-weight: 600; color: var(--slate-800); }
.f-text-small { font-size: 11px; color: var(--slate-400); font-family: 'monospace'; letter-spacing: 0.02em; }
.f-text-caption { font-size: 12px; color: var(--slate-400); }
.f-text-title { font-size: 20px; font-weight: 700; color: var(--slate-800); letter-spacing: -0.01em; }
.f-text-subtitle { font-size: 14px; color: var(--slate-400); }

/* Flutter Inputs */
.f-input {
    width: 100%;
    padding: 10px 14px;
    background: var(--slate-100);
    border: 2px solid transparent;
    border-radius: var(--radius-sm);
    font-size: 13px;
    color: var(--slate-800);
    outline: none;
    transition: var(--transition);
    font-family: inherit;
}
.f-input:focus {
    background: #ffffff;
    border-color: var(--teal-600);
    box-shadow: 0 0 0 4px color-mix(in srgb, var(--primary) 15%, transparent);
}
.f-input:read-only { opacity: 0.6; cursor: default; }
.f-input::placeholder { color: var(--slate-400); }

/* Flutter Labels */
.f-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--slate-600);
    margin-bottom: 6px;
    letter-spacing: 0.02em;
}
.f-label .required { color: var(--red-500); margin-left: 2px; }

/* Flutter Buttons */
.f-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: var(--teal-600);
    color: #ffffff;
    border: none;
    border-radius: var(--radius);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}
.f-btn-primary:hover { background: var(--teal-700); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3); }
.f-btn-primary:active { transform: translateY(0); }

.f-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    background: transparent;
    color: var(--slate-600);
    border: 1.5px solid var(--slate-200);
    border-radius: var(--radius);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}
.f-btn-secondary:hover { background: var(--slate-50); border-color: var(--slate-300); }

.f-btn-danger {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    background: transparent;
    color: var(--red-500);
    border: 1.5px solid transparent;
    border-radius: var(--radius);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}
.f-btn-danger:hover { background: #fef2f2; }

/* Flutter Table */
.f-table-container {
    background: #ffffff;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--slate-200);
    overflow: hidden;
}
.f-table-header {
    background: var(--slate-50);
    border-bottom: 1px solid var(--slate-200);
}
.f-table-header th {
    padding: 12px 16px;
    text-align: left;
    font-size: 11px;
    font-weight: 600;
    color: var(--slate-400);
    text-transform: uppercase;
    letter-spacing: 0.07em;
}
.f-table-row {
    border-bottom: 1px solid var(--slate-100);
    transition: var(--transition);
}
.f-table-row:hover { background: var(--slate-50); }
.f-table-row td {
    padding: 12px 16px;
    font-size: 13px;
    color: var(--slate-600);
    vertical-align: middle;
}

/* Flutter Avatar */
.f-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: rgba(13, 148, 136, 0.12);
    color: var(--teal-600);
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
}

/* Flutter Badge */
.f-badge {
    display: inline-flex;
    padding: 4px 12px;
    border-radius: var(--radius-full);
    font-size: 12px;
    font-weight: 600;
    background: rgba(13, 148, 136, 0.10);
    color: var(--teal-600);
}

/* Flutter Action Buttons */
.f-action-btn {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm);
    border: none;
    background: transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    color: var(--slate-400);
}
.f-action-btn:hover { background: var(--slate-100); color: var(--slate-600); }
.f-action-btn.edit:hover { background: rgba(59, 130, 246, 0.08); color: var(--blue-500); }
.f-action-btn.delete:hover { background: rgba(239, 68, 68, 0.08); color: var(--red-500); }

/* Flutter Modal */
.f-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(4px);
    animation: fadeIn 0.2s ease;
}
.f-modal {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: var(--shadow-lg);
    width: 100%;
    max-width: 560px;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideUp 0.25s ease;
}
.f-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid var(--slate-200);
    position: sticky;
    top: 0;
    background: #ffffff;
    z-index: 10;
    border-radius: 16px 16px 0 0;
}
.f-modal-header h2 {
    font-size: 16px;
    font-weight: 700;
    color: var(--slate-800);
}
.f-modal-close {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    color: var(--slate-400);
}
.f-modal-close:hover { background: var(--slate-100); color: var(--slate-600); }
.f-modal-body { padding: 24px; }
.f-modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    border-top: 1px solid var(--slate-100);
}

/* Flutter Grid */
.f-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.f-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

/* Header icon (modal) */
.f-header-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: var(--teal-100);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--teal-700);
    font-size: 16px;
    flex-shrink: 0;
}

/* Photo upload (modal) */
.f-photo-wrapper {
    position: relative;
    width: 96px;
    height: 96px;
    margin: 0 auto;
    cursor: pointer;
}
.f-photo-circle {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    background: var(--teal-100);
    border: 3px solid var(--teal-200);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    transition: var(--transition);
}
.f-photo-wrapper:hover .f-photo-circle { border-color: var(--teal-600); }
.f-photo-circle img { width: 100%; height: 100%; object-fit: cover; }
.f-photo-circle i { font-size: 36px; color: var(--teal-600); }
.f-photo-camera-badge {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--teal-700);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    border: 3px solid var(--teal-50);
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

/* Scrollbar Flutter-style */
.f-modal::-webkit-scrollbar { width: 4px; }
.f-modal::-webkit-scrollbar-track { background: transparent; }
.f-modal::-webkit-scrollbar-thumb { background: var(--slate-300); border-radius: 4px; }
.f-modal::-webkit-scrollbar-thumb:hover { background: var(--slate-400); }

/* Responsive */
@media (max-width: 640px) {
    .f-grid-2 { grid-template-columns: 1fr; }
    .f-grid-3 { grid-template-columns: 1fr; }
    .f-modal { max-width: 95%; margin: 10px; }
    .f-table-container { border-radius: 0; }
}
</style>
@endpush

@php
$data = $enseignants->map(fn($e) => [
    'id'            => $e->id,
    'matricule'     => $e->matricule,
    'nom'           => $e->nom,
    'prenom'        => $e->prenom,
    'sexe'          => $e->sexe,
    'grade'         => $e->grade,
    'email'         => $e->email ?? '',
    'contact_1'     => $e->contact_1 ?? '',
    'contact_2'     => $e->contact_2 ?? '',
    'specialite'    => $e->specialite ?? '',
    'date_naissance'=> $e->date_naissance ?? '',
    'lieu_naissance'=> $e->lieu_naissance ?? '',
    'nationalite'   => $e->nationalite ?? '',
    'url_photo'     => $e->url_photo ?? '',
]);
@endphp

<div x-data="page({{ $data }})" class="space-y-6">

    {{-- Header Flutter-style --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="f-text-title">Enseignants</h1>
            <p class="f-text-caption mt-1" x-text="items.length + ' enseignant(s) au total'"></p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="downloadTemplate()" class="f-btn-secondary">
                <i class="ri-file-download-line text-base"></i> Modèle
            </button>
            <button @click="importCSV()" class="f-btn-secondary">
                <i class="ri-upload-2-line text-base"></i> Importer
            </button>
            <button @click="exportCSV()" class="f-btn-secondary">
                <i class="ri-table-line text-base"></i> Exporter
            </button>
            <button @click="openCreate()" class="f-btn-primary">
                <i class="ri-add-line text-base"></i> Nouvel Enseignant
            </button>
        </div>
    </div>

    {{-- Table Flutter-style --}}
    <div class="f-table-container">
        {{-- Toolbar --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 flex-wrap gap-3">
            <div class="relative">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-300"></i>
                <input x-model="search" @input="page=1" type="text" placeholder="Rechercher..."
                       class="pl-9 pr-4 py-2 rounded-lg text-sm border border-slate-200 bg-slate-50 outline-none focus:border-teal-500 focus:bg-white transition-all duration-200"
                       style="min-width: 240px;">
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">Lignes/page :</span>
                <select x-model.number="perPage" @change="page=1" class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 outline-none bg-white">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        {{-- Empty State --}}
        <div x-show="!filtered.length" class="py-20 text-center">
            <i class="ri-user-line text-5xl text-slate-300"></i>
            <p class="mt-4 f-text-body" style="color:var(--slate-400)">Aucun enseignant trouvé</p>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto" x-show="filtered.length">
            <table class="w-full">
                <thead class="f-table-header">
                    <tr>
                        <th style="width: 35%;">Enseignant</th>
                        <th style="width: 15%;">Grade</th>
                        <th style="width: 20%;">Email</th>
                        <th style="width: 15%;">Contact 1</th>
                        <th style="width: 15%;">Contact 2</th>
                        <th style="width: 80px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="e in paged" :key="e.id">
                        <tr class="f-table-row">
                            <td>
                                <div class="flex items-center gap-3">
                                    {{-- Avatar avec photo --}}
                                    <template x-if="e.url_photo">
                                        <img :src="e.url_photo" class="f-avatar object-cover" 
                                             style="width:36px;height:36px;border-radius:50%;object-fit:cover"
                                             x-on:error="e.url_photo=''">
                                    </template>
                                    <template x-if="!e.url_photo">
                                        <div class="f-avatar" 
                                             x-text="(e.prenom.charAt(0) + e.nom.charAt(0)).toUpperCase()">
                                        </div>
                                    </template>
                                    <div>
                                        <p class="f-text-body-bold" x-text="e.prenom + ' ' + e.nom"></p>
                                        <p class="f-text-small" x-text="e.matricule"></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="f-badge" x-text="e.grade || '—'"></span>
                            </td>
                            <td class="f-text-body" x-text="e.email || '—'"></td>
                            <td class="f-text-body" x-text="e.contact_1 || '—'"></td>
                            <td class="f-text-body" x-text="e.contact_2 || '—'"></td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEdit(e)" class="f-action-btn edit" title="Modifier">
                                        <i class="ri-edit-2-line text-[15px]"></i>
                                    </button>
                                    <button @click="deleteEnseignant(e)" class="f-action-btn delete" title="Supprimer">
                                        <i class="ri-delete-bin-2-line text-[15px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-between px-5 py-3 border-t border-slate-100 flex-wrap gap-2">
            <p class="f-text-caption" x-text="filtered.length === 0 ? '0 résultat' : (((page-1)*perPage+1) + '–' + Math.min(page*perPage, filtered.length) + ' sur ' + filtered.length + ' résultat(s)')"></p>
            <div class="flex items-center gap-1">
                <button @click="page > 1 && page--" :disabled="page === 1"
                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40 transition-colors">
                    <i class="ri-arrow-left-s-line text-sm" style="color:var(--slate-500)"></i>
                </button>
                <template x-for="p in pageButtons" :key="p.key">
                    <span>
                        <span x-show="p.ellipsis" class="px-1 text-xs text-slate-400">...</span>
                        <button x-show="!p.ellipsis" @click="page = p.n"
                                class="w-7 h-7 rounded-lg text-xs font-bold transition-colors"
                                :class="page === p.n ? 'text-white' : 'border border-slate-200 text-slate-500 hover:bg-slate-50'"
                                :style="page === p.n ? 'background:var(--teal-600)' : ''"
                                x-text="p.n"></button>
                    </span>
                </template>
                <button @click="page < totalPages && page++" :disabled="page === totalPages"
                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40 transition-colors">
                    <i class="ri-arrow-right-s-line text-sm" style="color:var(--slate-500)"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Flutter-style --}}
    <template x-if="modal">
        <div class="f-modal-overlay" @click.self="modal=false">
            <div class="f-modal">
                {{-- Header --}}
                <div class="f-modal-header">
                    <div class="flex items-center gap-3">
                        <div class="f-header-icon-box">
                            <i class="ri-user-add-line"></i>
                        </div>
                        <h2 x-text="editing ? 'Modifier Enseignant' : 'Nouvel Enseignant'"></h2>
                    </div>
                    <button @click="modal=false" class="f-modal-close">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>

                {{-- Body --}}
                <form @submit.prevent="submitForm()" class="f-modal-body space-y-4">
                    @csrf
                    <input type="hidden" name="_method" x-model="editing ? 'PUT' : ''">
                    <input type="hidden" name="id" x-model="form.id">
                    <input type="hidden" name="url_photo" x-model="form.url_photo">

                    {{-- Photo --}}
                    <div class="flex flex-col items-center gap-2" style="margin-bottom: 8px;">
                        <div class="f-photo-wrapper" @click="$refs.photoInput.click()">
                            <div class="f-photo-circle">
                                <template x-if="form.url_photo">
                                    <img :src="form.url_photo" alt="Photo">
                                </template>
                                <template x-if="!form.url_photo">
                                    <i class="ri-user-3-line"></i>
                                </template>
                            </div>
                            <div class="f-photo-camera-badge">
                                <i class="ri-camera-fill"></i>
                            </div>
                        </div>
                        <input type="file" accept="image/*" x-ref="photoInput" class="hidden" @change="handlePhotoChange($event)">
                        <p class="f-text-caption" style="text-align:center;">Cliquer sur l'icône pour changer la photo</p>
                    </div>

                    {{-- Matricule + Genre --}}
                    <div class="f-grid-2">
                        <div>
                            <label class="f-label">Matricule</label>
                            <input type="text" name="matricule" x-model="form.matricule" 
                                   class="f-input" readonly>
                        </div>
                        <div>
                            <label class="f-label">Genre <span class="required">*</span></label>
                            <select name="sexe" x-model="form.sexe" class="f-input" required>
                                <option value="M">Masculin</option>
                                <option value="F">Féminin</option>
                            </select>
                        </div>
                    </div>

                    {{-- Prénom + Nom --}}
                    <div class="f-grid-2">
                        <div>
                            <label class="f-label">Prénom <span class="required">*</span></label>
                            <input type="text" name="prenom" x-model="form.prenom" 
                                   class="f-input" required placeholder="Prénom">
                        </div>
                        <div>
                            <label class="f-label">Nom <span class="required">*</span></label>
                            <input type="text" name="nom" x-model="form.nom" 
                                   class="f-input" required placeholder="NOM">
                        </div>
                    </div>

                    {{-- Grade --}}
                    <div>
                        <label class="f-label">Grade <span class="required">*</span></label>
                        <select name="grade" x-model="form.grade" class="f-input" required>
                            <option value="Professeur">Professeur</option>
                            <option value="Maître de Conférences">Maître de Conférences</option>
                            <option value="Chargé de Cours">Chargé de Cours</option>
                            <option value="Assistant">Assistant</option>
                        </select>
                    </div>

                    {{-- Date + Lieu naissance --}}
                    <div class="f-grid-2">
                        <div>
                            <label class="f-label">Date de naissance</label>
                            <input type="date" name="date_naissance" x-model="form.date_naissance" 
                                   class="f-input">
                        </div>
                        <div>
                            <label class="f-label">Lieu de naissance</label>
                            <input type="text" name="lieu_naissance" x-model="form.lieu_naissance" 
                                   class="f-input" placeholder="Ex: Abidjan">
                        </div>
                    </div>

                    {{-- Nationalité + Spécialité --}}
                    <div class="f-grid-2">
                        <div>
                            <label class="f-label">Nationalité</label>
                            <input type="text" name="nationalite" x-model="form.nationalite" 
                                   class="f-input" placeholder="Ivoirienne">
                        </div>
                        <div>
                            <label class="f-label">Spécialité <span class="required">*</span></label>
                            <input type="text" name="specialite" x-model="form.specialite" 
                                   class="f-input" required placeholder="Ex: Informatique">
                        </div>
                    </div>

                    {{-- Contacts --}}
                    <div class="f-grid-2">
                        <div>
                            <label class="f-label">Contact 1 <span class="required">*</span></label>
                            <input type="tel" name="contact_1" x-model="form.contact_1" 
                                   class="f-input" required placeholder="+225 07 XX XX XX XX">
                        </div>
                        <div>
                            <label class="f-label">Contact 2</label>
                            <input type="tel" name="contact_2" x-model="form.contact_2" 
                                   class="f-input" placeholder="+225 07 XX XX XX XX">
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="f-label">Email <span class="required">*</span></label>
                        <input type="email" name="email" x-model="form.email" 
                               class="f-input" required placeholder="email@uta.cm">
                    </div>

                    {{-- Footer --}}
                    <div class="f-modal-footer" style="padding: 16px 0 0 0;">
                        <button type="button" @click="modal=false" class="f-btn-secondary">
                            Annuler
                        </button>
                        <button type="submit" :disabled="submitting" 
                                class="f-btn-primary" 
                                x-text="submitting ? 'Enregistrement...' : 'Enregistrer'">
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function page(data) {
    return {
        items: data,
        search: '',
        modal: false,
        editing: false,
        submitting: false,
        page: 1,
        perPage: 10,
        form: {
            id: '',
            matricule: '',
            nom: '',
            prenom: '',
            sexe: 'M',
            grade: 'Assistant',
            email: '',
            contact_1: '',
            contact_2: '',
            specialite: '',
            date_naissance: '',
            lieu_naissance: '',
            nationalite: 'Ivoirienne',
            url_photo: ''
        },

        get filtered() {
            const q = this.search.toLowerCase();
            if (!q) return this.items;
            return this.items.filter(e =>
                (e.nom + ' ' + e.prenom).toLowerCase().includes(q) ||
                (e.matricule || '').toLowerCase().includes(q) ||
                (e.grade || '').toLowerCase().includes(q) ||
                (e.email || '').toLowerCase().includes(q)
            );
        },

        get totalPages() {
            return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
        },

        get paged() {
            if (this.page > this.totalPages) this.page = this.totalPages;
            const start = (this.page - 1) * this.perPage;
            return this.filtered.slice(start, start + this.perPage);
        },

        get pageButtons() {
            const total = this.totalPages;
            const current = this.page - 1; // 0-indexed comme en Flutter
            const btn = (i) => ({ n: i + 1, key: 'p' + i, ellipsis: false });
            const ell = (key) => ({ key, ellipsis: true });
            if (total <= 7) {
                return Array.from({ length: total }, (_, i) => btn(i));
            }
            const result = [btn(0)];
            if (current > 2) result.push(ell('e1'));
            const from = Math.min(Math.max(current - 1, 1), total - 2);
            const to = Math.min(Math.max(current + 1, 1), total - 2);
            for (let i = from; i <= to; i++) result.push(btn(i));
            if (current < total - 3) result.push(ell('e2'));
            result.push(btn(total - 1));
            return result;
        },

        // Nouvel enseignant
        openCreate() {
            this.editing = false;
            this.submitting = false;
            const year = new Date().getFullYear();
            const prefix = 'ENS-' + year + '-';
            const existing = this.items.filter(e => e.matricule.startsWith(prefix));
            const maxNum = existing.reduce((max, e) => {
                const num = parseInt(e.matricule.substring(prefix.length));
                return num > max ? num : max;
            }, 0);
            const nextNum = String(maxNum + 1).padStart(3, '0');

            this.form = {
                id: '',
                matricule: prefix + nextNum,
                nom: '',
                prenom: '',
                sexe: 'M',
                grade: 'Assistant',
                email: '',
                contact_1: '',
                contact_2: '',
                specialite: '',
                date_naissance: '',
                lieu_naissance: '',
                nationalite: 'Ivoirienne',
                url_photo: ''
            };
            this.modal = true;
        },

        // Modifier enseignant
        openEdit(e) {
            this.editing = true;
            this.submitting = false;
            this.form = { ...e };
            this.modal = true;
        },

        // Changement de photo (prévisualisation + encodage base64)
        handlePhotoChange(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                this.showToast('Veuillez sélectionner une image valide', 'error');
                return;
            }
            if (file.size > 3 * 1024 * 1024) {
                this.showToast('Image trop lourde (max 3 Mo)', 'error');
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                this.form.url_photo = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        // Soumettre le formulaire
        submitForm() {
            this.submitting = true;
            const url = this.editing ? '/enseignants/' + this.form.id : '/enseignants';
            const method = this.editing ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.form)
            })
            .then(async res => {
                if (!res.ok) {
                    const err = await res.json();
                    throw new Error(err.message || 'Erreur lors de l\'enregistrement');
                }
                const result = await res.json();
                if (this.editing) {
                    const idx = this.items.findIndex(i => i.id === this.form.id);
                    if (idx !== -1) this.items[idx] = { ...this.items[idx], ...this.form };
                } else {
                    this.items.unshift(result.data || this.form);
                }
                this.modal = false;
                this.submitting = false;
                this.showToast(this.editing ? 'Enseignant modifié avec succès' : 'Enseignant ajouté avec succès', 'success');
            })
            .catch(err => {
                this.submitting = false;
                this.showToast(err.message, 'error');
            });
        },

        // Supprimer
        deleteEnseignant(e) {
            if (!confirm(`Supprimer ${e.prenom} ${e.nom} ? Cette action est irréversible.`)) return;

            fetch('/enseignants/' + e.id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(async res => {
                if (!res.ok) throw new Error('Erreur lors de la suppression');
                this.items = this.items.filter(i => i.id !== e.id);
                this.showToast('Enseignant supprimé avec succès', 'success');
            })
            .catch(err => this.showToast(err.message, 'error'));
        },

        // Import CSV
        importCSV() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.csv,.txt';
            input.onchange = async (e) => {
                const file = e.target.files[0];
                if (!file) return;
                const text = await file.text();
                const lines = text.split('\n').filter(l => l.trim());
                if (lines.length < 2) return this.showToast('Fichier CSV vide ou invalide', 'error');

                const hasHeader = lines[0].toLowerCase().includes('nom') || 
                                 lines[0].toLowerCase().includes('prenom');
                const dataLines = hasHeader ? lines.slice(1) : lines;

                let imported = 0, skipped = 0, duplicates = 0;
                const existing = new Set(this.items.map(i => i.matricule.toLowerCase()));

                for (const line of dataLines) {
                    const cols = line.split(/[;,]/).map(c => c.trim().replace(/"/g, ''));
                    if (cols.length < 2) { skipped++; continue; }

                    const nom = cols[0];
                    const prenom = cols[1] || '';
                    if (!nom) { skipped++; continue; }

                    const year = new Date().getFullYear();
                    const n = this.items.length + imported + 1;
                    const matricule = (cols.length > 2 && cols[2]) ? cols[2] : `ENS-${year}-${String(n).padStart(3, '0')}`;

                    if (existing.has(matricule.toLowerCase())) { duplicates++; continue; }
                    existing.add(matricule.toLowerCase());

                    const newEns = {
                        nom: nom.toUpperCase(),
                        prenom: prenom,
                        matricule: matricule,
                        sexe: (cols.length > 3 && cols[3].toUpperCase() === 'F') ? 'F' : 'M',
                        grade: (cols.length > 4 && cols[4]) ? cols[4] : 'Assistant',
                        specialite: cols.length > 5 ? cols[5] : '',
                        email: cols.length > 6 ? cols[6] : '',
                        contact_1: cols.length > 7 ? cols[7] : '',
                        contact_2: cols.length > 8 ? cols[8] : '',
                        nationalite: cols.length > 9 ? cols[9] : 'Ivoirienne',
                        date_naissance: '',
                        lieu_naissance: '',
                        url_photo: ''
                    };

                    // Envoi
                    await fetch('/enseignants', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(newEns)
                    }).then(res => {
                        if (res.ok) { imported++; this.items.unshift(newEns); }
                        else skipped++;
                    }).catch(() => skipped++);
                }

                const parts = [`${imported} importé(s)`];
                if (duplicates > 0) parts.push(`${duplicates} doublon(s)`);
                if (skipped > 0) parts.push(`${skipped} ligne(s) invalide(s)`);
                this.showToast(`Import : ${parts.join(', ')}`, imported > 0 ? 'success' : 'warning');
            };
            input.click();
        },

        // Export CSV
        exportCSV() {
            const headers = ['Nom', 'Prénom', 'Matricule', 'Genre', 'Grade', 'Spécialité', 'Email', 'Contact 1', 'Contact 2', 'Nationalité'];
            const rows = this.filtered.map(e => [
                e.nom, e.prenom, e.matricule, e.sexe || 'M', e.grade || '',
                e.specialite || '', e.email || '', e.contact_1 || '', e.contact_2 || '',
                e.nationalite || ''
            ].map(v => `"${String(v).replace(/"/g, '""')}"`).join(';'));

            const csv = [headers.join(';'), ...rows].join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'enseignants_export.csv';
            link.click();
            this.showToast(`${this.filtered.length} enseignant(s) exporté(s)`, 'success');
        },

        // Télécharger modèle
        downloadTemplate() {
            const header = 'Nom;Prénom;Matricule;Genre;Grade;Spécialité;Email;Contact 1;Contact 2;Nationalité';
            const sample = 'KONAN;Yao;ENS-2025-001;M;Chargé de Cours;Informatique;yao@email.com;0707070707;;Ivoirienne';
            const blob = new Blob([header + '\n' + sample + '\n'], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'modele_import_enseignants.csv';
            link.click();
            this.showToast('Modèle CSV téléchargé', 'success');
        },

        // Toast Flutter-style
        showToast(message, type = 'info') {
            const colors = {
                success: { bg: 'rgba(13,148,136,0.95)', text: '#ffffff' },
                error: { bg: 'rgba(239,68,68,0.95)', text: '#ffffff' },
                warning: { bg: 'rgba(245,158,11,0.95)', text: '#ffffff' },
                info: { bg: 'rgba(59,130,246,0.95)', text: '#ffffff' }
            };
            const style = colors[type] || colors.info;
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
                padding: 12px 24px; border-radius: 12px; font-size: 13px; font-weight: 500;
                background: ${style.bg}; color: ${style.text}; z-index: 9999;
                box-shadow: 0 8px 32px rgba(0,0,0,0.12);
                animation: slideUp 0.3s ease;
                max-width: 90%;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(20px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }
    };
}
</script>
@endpush
</x-app-layout>