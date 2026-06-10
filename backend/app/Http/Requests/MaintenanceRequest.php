<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MaintenanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'date_prevue' => 'required|date|after_or_equal:today',
            'responsable' => 'required|string|max:255',
            'type_maintenance' => 'required|in:preventive,corrective',
            'cout' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages in French.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'equipement_id.required' => 'Le champ équipement est requis.',
            'equipement_id.exists' => 'L\'équipement sélectionné n\'existe pas.',
            'date_prevue.required' => 'Le champ date prévue est requis.',
            'date_prevue.date' => 'La date prévue doit être une date valide.',
            'date_prevue.after_or_equal' => 'La date prévue doit être une date égale ou postérieure à aujourd\'hui.',
            'responsable.required' => 'Le champ responsable est requis.',
            'responsable.string' => 'Le responsable doit être une chaîne de caractères.',
            'responsable.max' => 'Le responsable ne peut pas dépasser 255 caractères.',
            'type_maintenance.required' => 'Le champ type de maintenance est requis.',
            'type_maintenance.in' => 'Le type de maintenance doit être "preventive" ou "corrective".',
            'cout.numeric' => 'Le coût doit être un nombre.',
            'cout.min' => 'Le coût doit être supérieur ou égal à 0.',
            'observations.string' => 'Les observations doivent être une chaîne de caractères.',
            'observations.max' => 'Les observations ne peuvent pas dépasser 1000 caractères.',
        ];
    }
}
