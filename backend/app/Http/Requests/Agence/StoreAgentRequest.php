<?php

namespace App\Http\Requests\Agence;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matricule' => 'required|string|max:50|unique:agents,matricule',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'direction' => 'nullable|string|max:255',
            'service' => 'nullable|string|max:255',
            'poste' => 'nullable|string|max:255',
            'statut' => 'sometimes|in:actif,inactif',
            'photo' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
        ];
    }
}
