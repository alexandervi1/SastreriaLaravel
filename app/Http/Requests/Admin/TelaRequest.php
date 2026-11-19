<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TelaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El middleware 'admin' ya se encarga de la autorización real.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre'      => 'required|string|max:80',
            'composicion' => 'nullable|string|max:120',
            'color'       => 'required|string|max:40',
            'recargo'     => 'required|numeric|min:0',
            'activo'      => 'boolean',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:max_width=4000,max_height=4000',
        ];
    }
}
