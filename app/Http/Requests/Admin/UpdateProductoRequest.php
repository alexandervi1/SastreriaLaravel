<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Configuracion;
use App\Models\Producto;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // El middleware 'admin' ya se encarga de la autorización real.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'nombre'            => 'required|string|max:255',
            'tipo_prenda'       => ['sometimes', 'required', Rule::in(array_keys(Producto::TIPOS_PRENDA))],
            'publico'           => ['sometimes', 'required', Rule::in(array_keys(Producto::PUBLICOS))],
            'descripcion'       => 'nullable|string',
            'precio'            => 'required|numeric|min:0',
            'categoria_id'      => 'required|exists:categoria,categoria_id',
            'vende_por_talla'   => 'boolean',
            'vende_a_medida'    => 'boolean',
            'precio_medida'     => 'nullable|numeric|min:0',
            'dias_confeccion'   => 'nullable|integer|between:1,90',
            'imagen'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:max_width=4000,max_height=4000',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $ventaPorTalla = Configuracion::ventaPorTallaActiva();
            $porTalla = $ventaPorTalla && $this->boolean('vende_por_talla');
            $aMedida = $ventaPorTalla ? $this->boolean('vende_a_medida') : true;

            if (! $porTalla && ! $aMedida) {
                $validator->errors()->add('vende_por_talla', 'El modelo debe venderse al menos por talla o a medida.');
            }

            if ($aMedida && ! $this->filled('precio_medida')) {
                $validator->errors()->add('precio_medida', 'Indica el precio del terno a medida.');
            }

            if ($aMedida && ! $this->filled('dias_confeccion')) {
                $validator->errors()->add('dias_confeccion', 'Indica los días de confección.');
            }
        });
    }
}
