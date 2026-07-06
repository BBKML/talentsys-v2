<x-app-layout title="Établissement — Informations">

@push('styles')
<style>
.f-label { font-size:12px; font-weight:600; color:#475569; margin-bottom:6px; display:block; }
.f-input  { width:100%; padding:10px 12px; background:#F1F5F9; border:none; border-radius:8px; font-size:13px; color:#1E293B; outline:none; transition:all .15s; }
.f-input:focus { background:#fff; box-shadow:0 0 0 2px var(--primary)44; }
/* Info card item */
.info-item {
    background:#fff;
    border:1px solid #E2E8F0;
    border-radius:12px;
    padding:16px;
    display:flex;
    align-items:flex-start;
    gap:12px;
}
.info-icon {
    width:36px; height:36px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
}
/* Toggle switch */
.toggle-track {
    position:relative; width:44px; height:24px;
    border-radius:12px; cursor:pointer; transition:background .2s;
    flex-shrink:0;
}
.toggle-thumb {
    position:absolute; top:2px; left:2px;
    width:20px; height:20px; background:#fff;
    border-radius:50%; box-shadow:0 1px 3px rgba(0,0,0,.2);
    transition:transform .2s;
}
</style>
@endpush

@php
$logoUrl = $etab->logo ? \Illuminate\Support\Facades\Storage::url($etab->logo) : null;
@endphp

<div x-data="infoPage()" class="space-y-5">

    {{-- ── En-tête ── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold" style="color:#1E293B">Établissement</h1>
            <p class="text-sm mt-0.5" style="color:#64748B">Informations générales</p>
        </div>
        <button @click="openModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-white text-sm font-semibold hover:opacity-90 transition-opacity"
                style="background:var(--primary)">
            <i class="ri-edit-2-fill text-sm"></i>
            Modifier
        </button>
    </div>

    {{-- ── Flash ── --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
         style="background:rgba(34,197,94,.1); color:#15803d; border:1px solid rgba(34,197,94,.2)">
        <i class="ri-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    {{-- ══ CARD PRINCIPALE ══ --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">

        {{-- Logo + Nom + Badges --}}
        <div class="flex items-start gap-5 pb-6 border-b border-gray-100">

            {{-- Formulaire logo dédié (auto-submit) --}}
            <form action="{{ route('etablissement.logo.update') }}" method="POST"
                  enctype="multipart/form-data" x-ref="logoForm" style="display:none">
                @csrf
                <input type="file" name="logo" accept="image/*" x-ref="logoInput"
                       @change="$refs.logoForm.submit()">
            </form>

            {{-- Logo (cliquable) --}}
            <div class="relative flex-shrink-0 cursor-pointer" @click="$refs.logoInput.click()" title="Changer le logo">
                <div class="w-20 h-20 rounded-2xl overflow-hidden border border-gray-200 bg-gray-50 flex items-center justify-center shadow-sm">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" class="w-full h-full object-cover" alt="Logo">
                    @else
                        <i class="ri-building-line text-3xl" style="color:#CBD5E1"></i>
                    @endif
                </div>
                <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center shadow"
                     style="background:var(--primary)">
                    <i class="ri-camera-fill text-white" style="font-size:11px"></i>
                </div>
            </div>

            {{-- Infos --}}
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold" style="color:#1E293B">{{ $etab->nom }}</h2>
                <p class="text-sm mt-1" style="color:#64748B">
                    Code : <span class="font-semibold" style="color:#475569">{{ $etab->code ?? '—' }}</span>
                    &nbsp;—&nbsp;
                    Système :
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold ml-0.5"
                          style="background:rgba(90,103,216,.12); color:var(--primary)">
                        {{ $etab->systeme_academique ?? '—' }}
                    </span>
                </p>
                <div class="flex items-center gap-2 mt-3 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold"
                          style="background:rgba(34,197,94,.12); color:#15803d">
                        <i class="ri-checkbox-circle-fill text-xs"></i> Actif
                    </span>
                    @if($etab->siege)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold"
                          style="background:rgba(245,158,11,.12); color:#D97706; border:1px solid rgba(245,158,11,.2)">
                        <i class="ri-star-fill text-xs"></i> Siège
                    </span>
                    @endif
                    <button type="button" @click="$refs.logoInput.click()"
                            class="inline-flex items-center gap-1 text-xs font-semibold hover:opacity-70 transition-opacity"
                            style="color:var(--primary)">
                        <i class="ri-upload-2-line text-xs"></i> Changer le logo
                    </button>
                </div>
            </div>
        </div>

        {{-- Grille d'infos ── 2 colonnes --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-5">

            {{-- Adresse --}}
            <div class="info-item">
                <div class="info-icon" style="background:rgba(90,103,216,.1)">
                    <i class="ri-map-pin-2-fill text-sm" style="color:var(--primary)"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium" style="color:#94A3B8">Adresse</p>
                    <p class="text-[13px] font-semibold mt-0.5" style="color:#1E293B">
                        {{ $etab->adresse ?: 'N/A' }}
                    </p>
                </div>
            </div>

            {{-- Contact Principal --}}
            <div class="info-item">
                <div class="info-icon" style="background:rgba(13,148,136,.1)">
                    <i class="ri-phone-fill text-sm" style="color:#0D9488"></i>
                </div>
                <div>
                    <p class="text-[11px] font-medium" style="color:#94A3B8">Contact Principal</p>
                    <p class="text-[13px] font-semibold mt-0.5" style="color:#1E293B">
                        {{ $etab->contact_1 ?: 'N/A' }}
                    </p>
                </div>
            </div>

            {{-- Contact Secondaire --}}
            <div class="info-item">
                <div class="info-icon" style="background:rgba(13,148,136,.1)">
                    <i class="ri-phone-fill text-sm" style="color:#0D9488"></i>
                </div>
                <div>
                    <p class="text-[11px] font-medium" style="color:#94A3B8">Contact Secondaire</p>
                    <p class="text-[13px] font-semibold mt-0.5" style="color:#1E293B">
                        {{ $etab->contact_2 ?: 'N/A' }}
                    </p>
                </div>
            </div>

            {{-- Email Principal --}}
            <div class="info-item">
                <div class="info-icon" style="background:rgba(239,68,68,.1)">
                    <i class="ri-mail-fill text-sm" style="color:#EF4444"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium" style="color:#94A3B8">Email Principal</p>
                    <p class="text-[13px] font-semibold mt-0.5 truncate" style="color:#1E293B">
                        {{ $etab->email_1 ?: 'N/A' }}
                    </p>
                </div>
            </div>

            {{-- Email Secondaire --}}
            <div class="info-item">
                <div class="info-icon" style="background:rgba(239,68,68,.1)">
                    <i class="ri-mail-fill text-sm" style="color:#EF4444"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium" style="color:#94A3B8">Email Secondaire</p>
                    <p class="text-[13px] font-semibold mt-0.5 truncate" style="color:#1E293B">
                        {{ $etab->email_2 ?: 'N/A' }}
                    </p>
                </div>
            </div>

            {{-- Système Académique --}}
            <div class="info-item">
                <div class="info-icon" style="background:rgba(245,158,11,.1)">
                    <i class="ri-global-fill text-sm" style="color:#D97706"></i>
                </div>
                <div>
                    <p class="text-[11px] font-medium" style="color:#94A3B8">Système Académique</p>
                    <p class="text-[13px] font-semibold mt-0.5" style="color:#1E293B">
                        {{ $etab->systeme_academique ?: 'N/A' }}
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ MODAL MODIFIER ══════════════════════════════════════════ --}}
    <div x-show="modal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.45)"
         x-transition:enter="transition duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div class="bg-white rounded-2xl shadow-2xl w-full"
             style="max-width:540px; max-height:92vh; overflow-y:auto"
             @click.stop>

            {{-- Header --}}
            <div class="flex items-center gap-3 px-6 py-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:rgba(90,103,216,.12)">
                    <i class="ri-list-settings-fill text-lg" style="color:var(--primary)"></i>
                </div>
                <h2 class="flex-1 text-[15px] font-bold" style="color:#1E293B">Modifier Établissement</h2>
                <button @click="modal=false"
                        class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100">
                    <i class="ri-close-line text-lg" style="color:#64748B"></i>
                </button>
            </div>
            <div class="border-t border-gray-100"></div>

            <form action="{{ route('etablissement.informations.update') }}" method="POST"
                  enctype="multipart/form-data" @submit="submitting=true">
                @csrf

                <div class="px-6 pt-5 pb-2 space-y-4">

                    {{-- Nom + Code --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="f-label">Nom <span style="color:#EF4444">*</span></label>
                            <input type="text" name="nom" value="{{ old('nom', $etab->nom) }}"
                                   required class="f-input" placeholder="Université de Technologie">
                        </div>
                        <div>
                            <label class="f-label">Code <span style="color:#EF4444">*</span></label>
                            <input type="text" name="code" value="{{ old('code', $etab->code) }}"
                                   required class="f-input" placeholder="Ex: UTA-YOP">
                        </div>
                    </div>

                    {{-- Système Académique --}}
                    <div>
                        <label class="f-label">Système Académique</label>
                        <div class="relative">
                            <select name="systeme_academique" class="f-input" style="cursor:pointer; appearance:none; padding-right:36px; background-image:url(\"data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e\"); background-repeat:no-repeat; background-position:right 10px center; background-size:18px">
                                <option value="LMD"           {{ old('systeme_academique', $etab->systeme_academique) === 'LMD'           ? 'selected' : '' }}>LMD</option>
                                <option value="Grandes Écoles" {{ old('systeme_academique', $etab->systeme_academique) === 'Grandes Écoles' ? 'selected' : '' }}>Grandes Écoles</option>
                                <option value="BTS"           {{ old('systeme_academique', $etab->systeme_academique) === 'BTS'           ? 'selected' : '' }}>BTS</option>
                                <option value="Autres"        {{ old('systeme_academique', $etab->systeme_academique) === 'Autres'        ? 'selected' : '' }}>Autres</option>
                            </select>
                        </div>
                    </div>

                    {{-- Toggle Siège --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl"
                         :style="siege ? 'background:' + primaryAlpha + '; border:1px solid ' + primaryAlpha2 : 'background:#F8FAFC; border:1px solid #E2E8F0'">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                             :style="siege ? 'background:var(--primary)' : 'background:#E2E8F0'">
                            <i class="text-sm" :class="siege ? 'ri-checkbox-circle-fill text-white' : 'ri-checkbox-blank-circle-line'" :style="siege ? '' : 'color:#94A3B8'"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-[13px] font-semibold" style="color:#1E293B">Établissement siège</p>
                            <p class="text-xs mt-0.5" style="color:#64748B">Cochez si cet établissement est le siège principal</p>
                        </div>
                        <input type="hidden" name="siege" value="0">
                        <div class="toggle-track flex-shrink-0" @click="siege=!siege"
                             :style="siege ? 'background:var(--primary)' : 'background:#CBD5E1'">
                            <div class="toggle-thumb" :style="siege ? 'transform:translateX(20px)' : ''"></div>
                            <input type="checkbox" name="siege" value="1" :checked="siege" style="display:none">
                        </div>
                    </div>

                    {{-- Critère de passage (LMD) --}}
                    <div>
                        <label class="f-label">Critère de passage (LMD)</label>
                        <div class="relative">
                            <select name="critere_passage_lmd" class="f-input" style="cursor:pointer; appearance:none; padding-right:36px; background-image:url(\"data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e\"); background-repeat:no-repeat; background-position:right 10px center; background-size:18px">
                                <option value="">— Sélectionner —</option>
                                @foreach($criteres as $c)
                                <option value="{{ $c }}" {{ old('critere_passage_lmd', $etab->critere_passage_lmd) === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Adresse --}}
                    <div>
                        <label class="f-label">Adresse</label>
                        <input type="text" name="adresse" value="{{ old('adresse', $etab->adresse) }}"
                               class="f-input" placeholder="Ex: Yopougon, Abidjan">
                    </div>

                    {{-- Contact 1 + Contact 2 --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="f-label">Contact 1</label>
                            <input type="text" name="contact_1" value="{{ old('contact_1', $etab->contact_1) }}"
                                   class="f-input" placeholder="+225 0102030405">
                        </div>
                        <div>
                            <label class="f-label">Contact 2</label>
                            <input type="text" name="contact_2" value="{{ old('contact_2', $etab->contact_2) }}"
                                   class="f-input" placeholder="+225 07 XX XX XX XX">
                        </div>
                    </div>

                    {{-- Email 1 + Email 2 --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="f-label">Email 1</label>
                            <input type="email" name="email_1" value="{{ old('email_1', $etab->email_1) }}"
                                   class="f-input" placeholder="contact@uta.com">
                        </div>
                        <div>
                            <label class="f-label">Email 2</label>
                            <input type="email" name="email_2" value="{{ old('email_2', $etab->email_2) }}"
                                   class="f-input" placeholder="info@ecole.ci">
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-4 px-6 py-4">
                    <button type="button" @click="modal=false"
                            class="text-sm font-semibold hover:opacity-70"
                            style="color:var(--primary)">Annuler</button>
                    <button type="submit" :disabled="submitting"
                            class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60"
                            style="background:var(--primary)">
                        <span x-text="submitting ? 'Enregistrement...' : 'Enregistrer'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function infoPage() {
    return {
        modal: false,
        submitting: false,
        siege: {{ $etab->siege ? 'true' : 'false' }},
        primaryAlpha:  'rgba(90,103,216,.08)',
        primaryAlpha2: 'rgba(90,103,216,.2)',
        openModal() { this.modal = true; this.submitting = false; },
    }
}
</script>
@endpush

</x-app-layout>
