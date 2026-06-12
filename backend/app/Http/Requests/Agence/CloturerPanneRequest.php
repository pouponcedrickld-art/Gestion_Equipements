<?php

namespace App\Http\Requests\Agence;

use Illuminate\Foundation\Http\FormRequest;

class CloturerPanneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // clôture = action finale, on garde le payload optionnel pour historiser
            'commentaire' => ['nullable', 'string'],
            'action_realisee' => ['nullable', 'string'],
            'cout_reparation' => ['nullable', 'numeric', 'min:0'],
            'solution' => ['nullable', 'string'],
            'decision_finale' => ['nullable', 'in:reparee,remplacement,irrecuperable'],
        ];
    }
}

