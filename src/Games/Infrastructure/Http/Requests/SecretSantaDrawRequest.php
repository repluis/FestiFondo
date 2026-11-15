<?php

namespace Src\Games\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SecretSantaDrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_uuid'         => ['required', 'string', 'exists:fund_raisings,uuid'],
            'participants'          => ['required', 'array', 'min:2'],
            'participants.*'        => ['integer', 'distinct'],
            'exclusions'            => ['nullable', 'array'],
            'exclusions.*.giver'    => ['required', 'integer'],
            'exclusions.*.receiver' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'participants.required' => 'Selecciona al menos 2 participantes.',
            'participants.min'      => 'Selecciona al menos 2 participantes.',
            'campaign_uuid.exists'  => 'La campaña seleccionada no existe.',
        ];
    }
}
