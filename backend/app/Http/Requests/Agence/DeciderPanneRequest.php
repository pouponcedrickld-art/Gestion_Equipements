<?php

namespace App\Http\Requests\Agence;

use Illuminate\Foundation\Http\FormRequest;

class DeciderPanneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:reparee,remplacement,irrecuperable'],

            'cout_estimatif' => ['nullable', 'numeric', 'min:0'],
            'commentaires' => ['nullable', 'string'],

            // Optionnels (si ton front les envoie)
            'diagnostic_technicien' => ['nullable', 'string'],
            'action_realisee' => ['nullable', 'string'],
            'solution' => ['nullable', 'string'],
        ];
    }
}


