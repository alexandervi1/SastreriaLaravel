<?php

namespace App\Http\Requests;

use App\Models\MedidaCliente;
use Illuminate\Foundation\Http\FormRequest;

class MedidaClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $reglas = [
            'nombre' => 'required|string|max:60',
            'notas' => 'nullable|string|max:1000',
            'tomadas_en_taller' => 'boolean',
        ];

        foreach (MedidaCliente::CAMPOS as $campo) {
            $reglas[$campo] = $campo === 'estatura'
                ? 'nullable|numeric|between:100,230'
                : 'nullable|numeric|between:10,250';
        }

        return $reglas;
    }
}
