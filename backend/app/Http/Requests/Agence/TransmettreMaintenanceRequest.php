<?php

namespace App\Http\Requests\Agence;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class TransmettreMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Autorisation via Policy sur la ressource Panne (optionnel ici)
        return true;
    }

    public function rules(): array
    {
        return [
            'technicien_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}

