<?php

namespace App\Http\Requests\Agence;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeMaterielRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // À adapter selon les rôles si nécessaire
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'equipement_id' => 'required|exists:equipements,id',
            'quantite' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $equipement = \App\Models\Equipement::find($this->equipement_id);
                    if ($equipement && $value > $equipement->quantite) {
                        $fail("La quantité demandée ({$value}) dépasse la quantité disponible en stock ({$equipement->quantite}).");
                    }
                },
            ],
            'urgence' => 'required|in:Basse,Moyenne,Haute',
            'motif' => 'required|string',
            'date_souhaitee' => 'required|date|after_or_equal:today',
        ];
    }
}
