<?php

namespace App\Http\Requests\Agence;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePanneStatutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statut' => ['required', 'in:declaree,en_cours,en_maintenance,resolue,irrecuperable'],

            // Champs optionnels métier pour la transition
            'diagnostic_technicien' => ['nullable', 'string'],
            'action_realisee' => ['nullable', 'string'],
            'cout_reparation' => ['nullable', 'numeric', 'min:0'],
            'date_resolution' => ['nullable', 'date'],
            'decision_finale' => ['nullable', 'string'],
            'solution' => ['nullable', 'string'],
            'technicien_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}

