<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Carte scolaire — {{ $etudiant->matricule }}</title>
<style>
    @page { margin: 0; }
    * { box-sizing: border-box; }
    html, body {
        margin: 0;
        padding: 0;
        font-family: "DejaVu Sans", sans-serif;
    }

    @php
        $primary = $etudiant->etablissement->primary_color ?? '#5A67D8';
    @endphp

    .carte {
        width: 243pt;
        height: 153pt;
        position: relative;
        border: 1pt solid #E2E8F0;
        overflow: hidden;
    }

    /* ── Bandeau haut ─────────────────────────────────────────── */
    .entete {
        width: 100%;
        background-color: {{ $primary }};
        color: #ffffff;
    }
    .entete td { padding: 7pt 9pt 6pt 9pt; vertical-align: middle; }
    .logo {
        width: 20pt;
        height: 20pt;
        border-radius: 4pt;
        background-color: rgba(255,255,255,.2);
        color: #ffffff;
        font-size: 10pt;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
    }
    .etab-nom {
        font-size: 8.5pt;
        font-weight: bold;
        color: #ffffff;
        line-height: 1.2;
    }
    .etab-code {
        font-size: 6.5pt;
        color: rgba(255,255,255,.75);
        text-transform: uppercase;
        letter-spacing: .5pt;
    }
    .titre-carte {
        font-size: 7pt;
        font-weight: bold;
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: .8pt;
        text-align: right;
    }

    /* ── Corps ────────────────────────────────────────────────── */
    .corps { width: 100%; }
    .corps td { vertical-align: top; padding: 8pt 0 0 9pt; }

    .photo-cell { width: 62pt; }
    .photo-box {
        width: 54pt;
        height: 64pt;
        border-radius: 5pt;
        background-color: #F1F5F9;
        border: 1pt solid #E2E8F0;
        text-align: center;
        vertical-align: middle;
        color: {{ $primary }};
        font-size: 20pt;
        font-weight: bold;
        overflow: hidden;
    }
    .photo-box img { width: 54pt; height: 64pt; }

    .infos-cell { padding-right: 9pt; }
    .nom-etudiant {
        font-size: 11.5pt;
        font-weight: bold;
        color: #1E293B;
        text-transform: uppercase;
        line-height: 1.15;
    }
    .prenom-etudiant {
        font-size: 9pt;
        color: #475569;
        margin-top: 1pt;
    }

    .infos-table { width: 100%; margin-top: 6pt; border-collapse: collapse; }
    .infos-table td { padding: 1.5pt 0; font-size: 7.5pt; }
    .infos-label {
        color: #94A3B8;
        text-transform: uppercase;
        letter-spacing: .3pt;
        width: 46pt;
    }
    .infos-value { color: #1E293B; font-weight: bold; }

    /* ── Bas de carte ─────────────────────────────────────────── */
    .pied {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        border-top: 1pt solid #F1F5F9;
    }
    .pied td { padding: 5pt 9pt; vertical-align: middle; }
    .matricule-barre {
        font-size: 11pt;
        letter-spacing: 2.5pt;
        color: #1E293B;
        font-weight: bold;
    }
    .validite {
        font-size: 6pt;
        color: #94A3B8;
        text-align: right;
        line-height: 1.3;
    }
</style>
</head>
<body>

@php
    $initiale = strtoupper(substr($etudiant->nom ?? '?', 0, 1));
    $etablissement = $etudiant->etablissement;
    $classe = $inscription->classe->libelle ?? null;
    $niveau = $inscription->niveau->libelle ?? null;
    $filiere = $inscription->filiere->libelle ?? null;
    $anneeScolaire = $inscription->anneeScolaire->libelle ?? '—';
@endphp

<div class="carte">

    {{-- Bandeau établissement --}}
    <table class="entete" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:20pt">
                <div class="logo">
                    @if($etablissement && $etablissement->logo)
                        <img src="{{ $etablissement->logo }}" style="width:20pt;height:20pt;border-radius:4pt">
                    @else
                        {{ strtoupper(substr($etablissement->code ?? 'E', 0, 1)) }}
                    @endif
                </div>
            </td>
            <td>
                <div class="etab-nom">{{ $etablissement->nom ?? 'Établissement' }}</div>
                <div class="etab-code">{{ $etablissement->code ?? '' }}</div>
            </td>
            <td class="titre-carte">Carte<br>d'étudiant</td>
        </tr>
    </table>

    {{-- Corps : photo + identité --}}
    <table class="corps" cellpadding="0" cellspacing="0">
        <tr>
            <td class="photo-cell">
                <div class="photo-box">
                    @if($etudiant->url_photo)
                        <img src="{{ $etudiant->url_photo }}" alt="">
                    @else
                        {{ $initiale }}
                    @endif
                </div>
            </td>
            <td class="infos-cell">
                <div class="nom-etudiant">{{ $etudiant->nom }}</div>
                <div class="prenom-etudiant">{{ $etudiant->prenom }}</div>

                <table class="infos-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="infos-label">Matricule</td>
                        <td class="infos-value">{{ $etudiant->matricule }}</td>
                    </tr>
                    <tr>
                        <td class="infos-label">Niveau</td>
                        <td class="infos-value">{{ $niveau ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="infos-label">Classe</td>
                        <td class="infos-value">{{ $classe ?? '—' }}{{ $filiere ? ' · '.$filiere : '' }}</td>
                    </tr>
                    <tr>
                        <td class="infos-label">Naissance</td>
                        <td class="infos-value">{{ optional($etudiant->date_naissance)->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Pied de carte --}}
    <table class="pied" cellpadding="0" cellspacing="0">
        <tr>
            <td class="matricule-barre">{{ $etudiant->matricule }}</td>
            <td class="validite">
                Année scolaire<br>
                <strong style="color:#1E293B">{{ $anneeScolaire }}</strong>
            </td>
        </tr>
    </table>

</div>
</body>
</html>
