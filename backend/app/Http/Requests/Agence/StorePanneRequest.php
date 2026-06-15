<?php

namespace App\Http\Requests\Agence;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePanneRequest extends FormRequest
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
            'equipement_id' => ['required', 'integer', 'exists:equipements,id'],
            'agent_id' => ['required', 'integer', 'exists:agents,id'],
            'description' => ['required', 'string', 'min:5'],
            'niveau_gravite' => ['required', 'in:mineure,majeure,critique'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['nullable'],
        ];
    }

}
