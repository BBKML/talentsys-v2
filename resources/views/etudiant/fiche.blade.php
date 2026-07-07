<x-app-layout title="Fiche étudiant — {{ $etudiant->nom }} {{ $etudiant->prenom }}">
@push('styles')
<style>
.card{background:#fff;border-radius:16px;box-shadow:0 1px 2px rgba(0,0,0,.04);border:1px solid #F1F5F9}
.info-label{font-size:11px;font-weight:600;color:#94A3B8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px}
.info-value{font-size:14px;font-weight:600;color:#1E293B}
.tool-btn{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:12px;font-size:13px;font-weight:600;border:1px solid #E2E8F0;background:#fff;color:#475569;cursor:pointer;transition:all .15s;text-decoration:none}
.tool-btn:hover{background:#F8FAFC}
.avatar-xl{width:96px;height:96px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:30px;background:rgba(90,103,216,.14);color:var(--primary);flex-shrink:0;overflow:hidden}
.avatar-xl img{width:100%;height:100%;object-fit:cover}
</style>
@endpush

@php
$statutActif = optional($etudiant->statut)->libelle === 'Actif' || $etudiant->id_statut == 1;
$initiale = strtoupper(substr($etudiant->nom ?? '?', 0, 1));
@endphp

<div class="space-y-5">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('etudiants.index') }}" class="tool-btn"><i class="ri-arrow-left-line"></i> Retour</a>
            <div>
                <h1 class="text-2xl font-bold" style="color:#1E293B">Fiche étudiant</h1>
                <p class="text-sm mt-0.5" style="color:#94A3B8">Matricule : <span class="font-semibold" style="color:var(--primary)">{{ $etudiant->matricule }}</span></p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('etudiants.carte', $etudiant->id) }}" target="_blank" class="tool-btn"><i class="ri-share-forward-line"></i> Carte scolaire</a>
            <a href="{{ route('etudiants.documents', $etudiant->id) }}" class="tool-btn"><i class="ri-folder-3-line"></i> Documents</a>
            <a href="{{ route('etudiants.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-90" style="background:var(--primary)">
                <i class="ri-edit-2-line"></i> Modifier
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium" style="background:rgba(34,197,94,.08);color:#15803d;border:1px solid rgba(34,197,94,.18)">
        <i class="ri-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-3 gap-5">

        {{-- Colonne gauche : identité --}}
        <div class="card p-6 col-span-1">
            <div class="flex flex-col items-center text-center">
                <div class="avatar-xl mb-4">
                    @if($etudiant->url_photo)
                        <img src="{{ $etudiant->url_photo }}" alt="Photo">
                    @else
                        {{ $initiale }}
                    @endif
                </div>
                <h2 class="text-lg font-bold" style="color:#1E293B">{{ $etudiant->nom }} {{ $etudiant->prenom }}</h2>
                <p class="text-sm" style="color:#94A3B8">{{ $etudiant->email ?: '—' }}</p>

                <span class="inline-flex items-center gap-1.5 mt-3 px-3 py-1 rounded-xl text-[11px] font-bold"
                      style="background:{{ $statutActif ? 'rgba(34,197,94,.12)' : 'rgba(239,68,68,.1)' }};color:{{ $statutActif ? '#15803d' : '#dc2626' }}">
                    <span class="w-1.5 h-1.5 rounded-full inline-block" style="background:{{ $statutActif ? '#16a34a' : '#dc2626' }}"></span>
                    {{ $statutActif ? 'Actif' : 'Inactif' }}
                </span>
            </div>

            <div style="height:1px;background:#F1F5F9;margin:20px 0"></div>

            <div class="space-y-4">
                <div>
                    <p class="info-label">Genre</p>
                    <p class="info-value">{{ $etudiant->sexe === 'M' ? 'Masculin' : ($etudiant->sexe === 'F' ? 'Féminin' : '—') }}</p>
                </div>
                <div>
                    <p class="info-label">Date de naissance</p>
                    <p class="info-value">{{ optional($etudiant->date_naissance)->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="info-label">Lieu de naissance</p>
                    <p class="info-value">{{ $etudiant->lieu_naissance ?: '—' }}</p>
                </div>
                <div>
                    <p class="info-label">Nationalité</p>
                    <p class="info-value">{{ $etudiant->nationalite ?: '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Colonne droite : scolarité + contact + parent --}}
        <div class="col-span-2 space-y-5">

            <div class="card p-6">
                <h3 class="text-sm font-bold mb-4" style="color:#1E293B"><i class="ri-graduation-cap-line mr-1" style="color:var(--primary)"></i> Scolarité</h3>
                <div class="grid grid-cols-3 gap-5">
                    <div>
                        <p class="info-label">Niveau</p>
                        <p class="info-value">{{ $inscription->niveau->libelle ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="info-label">Filière</p>
                        <p class="info-value">{{ $inscription->filiere->libelle ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="info-label">Classe</p>
                        <p class="info-value">{{ $inscription->classe->libelle ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="info-label">N° inscription</p>
                        <p class="info-value">{{ $inscription->numero_inscription ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="info-label">Date d'inscription</p>
                        <p class="info-value">{{ optional($inscription->date_inscription ?? null)->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="info-label">Type d'inscription</p>
                        <p class="info-value">{{ $inscription->type_inscription ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <h3 class="text-sm font-bold mb-4" style="color:#1E293B"><i class="ri-phone-line mr-1" style="color:var(--primary)"></i> Contact</h3>
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <p class="info-label">Téléphone</p>
                        <p class="info-value">{{ $etudiant->contact ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="info-label">Email</p>
                        <p class="info-value">{{ $etudiant->email ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold" style="color:#1E293B"><i class="ri-parent-line mr-1" style="color:var(--primary)"></i> Parent / Tuteur</h3>
                    @if($etudiant->id_parent)
                        <a href="{{ route('parents.index') }}" class="text-xs font-semibold hover:underline" style="color:var(--primary)">Voir tous les parents</a>
                    @endif
                </div>

                @if($etudiant->parent)
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <p class="info-label">Nom complet</p>
                            <p class="info-value">{{ $etudiant->parent->nom }} {{ $etudiant->parent->prenom }}</p>
                        </div>
                        <div>
                            <p class="info-label">Lien parental</p>
                            <p class="info-value">{{ $etudiant->parent->lien_parental ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="info-label">Contact</p>
                            <p class="info-value">{{ $etudiant->parent->contact_1 ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="info-label">Email</p>
                            <p class="info-value">{{ $etudiant->parent->email ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="info-label">Profession</p>
                            <p class="info-value">{{ $etudiant->parent->profession ?: '—' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-sm" style="color:#94A3B8">Aucun parent/tuteur associé à cet étudiant.</p>
                @endif
            </div>
        </div>
    </div>
</div>
</x-app-layout>
