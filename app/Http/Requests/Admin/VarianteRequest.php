<?php

namespace App\Http\Requests\Admin;

use App\Models\Producto;
use App\Models\ProductoVariante;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VarianteRequest extends FormRequest
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
        $variante = $this->route('variante');
        $ignorar = $variante instanceof ProductoVariante ? $variante->variante_id : null;

        return [
            'talla'  => 'required|string|max:10',
            'color'  => 'nullable|string|max:40',
            'sku'    => [
                'required',
                'string',
                'max:40',
                Rule::unique('producto_variante', 'sku')->ignore($ignorar, 'variante_id'),
            ],
            'stock'  => 'required|integer|min:0',
            'activo' => 'boolean',
        ];
    }

    /**
     * Rechaza dos variantes con la misma talla y color en el mismo modelo.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $producto = $this->route('producto');
            $variante = $this->route('variante');

            if (! $producto instanceof Producto) {
                return;
            }

            $consulta = ProductoVariante::where('producto_id', $producto->producto_id)
                ->where('talla', (string) $this->input('talla'));

            $color = $this->input('color');
            if ($color === null || $color === '') {
                $consulta->whereNull('color');
            } else {
                $consulta->where('color', $color);
            }

            if ($variante instanceof ProductoVariante) {
                $consulta->whereKeyNot($variante->variante_id);
            }

            if ($consulta->exists()) {
                $validator->errors()->add(
                    'talla',
                    'Ya existe una variante con esa talla y color para este modelo.'
                );
            }
        });
    }
}
