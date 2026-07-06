<?php

namespace App\View\Components;

use App\Models\Etablissement;
use Illuminate\View\Component;

class AppLayout extends Component
{
    public string $title;
    public string $primaryColor;

    public function __construct(string $title = 'TalentSys ERP')
    {
        $this->title = $title;

        $this->primaryColor = '#5A67D8'; // fallback
        try {
            $etabId = session('etablissement_id');
            if ($etabId) {
                $etab = Etablissement::with('couleurs')->find($etabId);
                if ($etab) {
                    $this->primaryColor = $etab->primary_color;
                }
            }
        } catch (\Throwable $e) {
            // DB indisponible — on garde le fallback
        }
    }

    public function render()
    {
        return view('layouts.app');
    }
}
