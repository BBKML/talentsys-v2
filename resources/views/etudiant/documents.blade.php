<x-app-layout title="Documents — {{ $etudiant->nom }} {{ $etudiant->prenom }}">
@push('styles')
<style>
.card{background:#fff;border-radius:16px;box-shadow:0 1px 2px rgba(0,0,0,.04);border:1px solid #F1F5F9}
.tool-btn{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:12px;font-size:13px;font-weight:600;border:1px solid #E2E8F0;background:#fff;color:#475569;cursor:pointer;transition:all .15s;text-decoration:none}
.tool-btn:hover{background:#F8FAFC}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none;transition:all .15s}
.f-input:focus{background:#fff;box-shadow:0 0 0 2px var(--primary)44}
.doc-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0}
.act-btn{width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;cursor:pointer;border:none;background:transparent}
</style>
@endpush

@php
    // NB : pas de table "document" fournie pour l'instant, cette page est un
    // scaffold prêt à brancher. $documents est une collection vide par défaut ;
    // dès qu'un modèle Document existera (id, id_etudiant, nom, type, url,
    // taille, created_at...), remplace cette ligne par une vraie requête dans
    // le contrôleur (ex: $etudiant->documents) et passe-la à la vue.
    $documents = $documents ?? collect();

    $typesDocuments = [
        'Acte de naissance', 'Certificat médical', 'Bulletin', 'Diplôme',
        'Photo d\'identité', 'Pièce d\'identité parent', 'Autre',
    ];
@endphp

<div x-data="documentsPage()" class="space-y-5">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('etudiants.fiche', $etudiant->id) }}" class="tool-btn"><i class="ri-arrow-left-line"></i> Retour à la fiche</a>
            <div>
                <h1 class="text-2xl font-bold" style="color:#1E293B">Dossier documents</h1>
                <p class="text-sm mt-0.5" style="color:#94A3B8">{{ $etudiant->nom }} {{ $etudiant->prenom }} — <span class="font-semibold" style="color:var(--primary)">{{ $etudiant->matricule }}</span></p>
            </div>
        </div>
        <button @click="modal=true" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-90" style="background:var(--primary)">
            <i class="ri-upload-2-line"></i> Ajouter un document
        </button>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium" style="background:rgba(34,197,94,.08);color:#15803d;border:1px solid rgba(34,197,94,.18)">
        <i class="ri-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    <div class="card">
        @if($documents->isEmpty())
            <div class="py-20 text-center">
                <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#F1F5F9">
                    <i class="ri-folder-3-line text-3xl" style="color:#CBD5E1"></i>
                </div>
                <p class="text-sm font-semibold" style="color:#64748B">Aucun document enregistré</p>
                <p class="text-xs mt-1" style="color:#94A3B8">Ajoute un premier document avec le bouton ci-dessus.</p>
            </div>
        @else
            <div class="divide-y" style="border-color:#F8FAFC">
                @foreach($documents as $doc)
                    <div class="flex items-center justify-between px-5 py-4">
                        <div class="flex items-center gap-4">
                            <div class="doc-icon" style="background:rgba(90,103,216,.1);color:var(--primary)">
                                <i class="ri-file-text-line"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold" style="color:#1E293B">{{ $doc->nom }}</p>
                                <p class="text-xs" style="color:#94A3B8">{{ $doc->type ?? '—' }} · ajouté le {{ optional($doc->created_at)->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <a href="{{ $doc->url ?? '#' }}" target="_blank" class="act-btn hover:bg-indigo-50" style="color:#6366f1"><i class="ri-eye-line text-[15px]"></i></a>
                            <a href="{{ $doc->url ?? '#' }}" download class="act-btn hover:bg-green-50" style="color:#16a34a"><i class="ri-download-2-line text-[15px]"></i></a>
                            <button class="act-btn hover:bg-red-50" style="color:#CBD5E1"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Modal d'ajout --}}
    <template x-if="modal">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(15,23,42,.5)">
            <div class="bg-white rounded-2xl shadow-2xl w-full" style="max-width:460px" @click.stop>
                <div class="flex items-center gap-3 px-6 py-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(90,103,216,.12)"><i class="ri-upload-2-line text-xl" style="color:var(--primary)"></i></div>
                    <div class="flex-1"><h2 class="text-[15px] font-bold" style="color:#1E293B">Ajouter un document</h2></div>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line text-lg" style="color:#94A3B8"></i></button>
                </div>
                <div style="height:1px;background:#F1F5F9;margin:0 24px"></div>

                {{--
                    Formulaire prêt à brancher : pointe vers une route à créer,
                    ex. route('etudiants.documents.store', $etudiant->id).
                    Nécessite un contrôleur + modèle Document (upload sur disque
                    ou S3, puis enregistrement en base).
                --}}
                <form action="#" method="POST" enctype="multipart/form-data" @submit="submitting=true">
                    @csrf
                    <input type="hidden" name="id_etudiant" value="{{ $etudiant->id }}">
                    <div class="px-6 pt-5 pb-2 space-y-4">
                        <div>
                            <label class="text-xs font-semibold block mb-1.5" style="color:#475569">Type de document</label>
                            <select name="type" class="f-input">
                                @foreach($typesDocuments as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold block mb-1.5" style="color:#475569">Nom du document</label>
                            <input type="text" name="nom" required class="f-input" placeholder="Ex : Acte de naissance - copie">
                        </div>
                        <div>
                            <label class="text-xs font-semibold block mb-1.5" style="color:#475569">Fichier</label>
                            <input type="file" name="fichier" required class="f-input" style="padding:8px">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 px-6 py-4">
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
function documentsPage(){
    return { modal:false, submitting:false }
}
</script>
@endpush
</x-app-layout>
