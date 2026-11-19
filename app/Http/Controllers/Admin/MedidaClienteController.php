<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedidaClienteRequest;
use App\Models\MedidaCliente;
use App\Models\Usuario;
use Illuminate\Support\Arr;

class MedidaClienteController extends Controller
{
    public function update(MedidaClienteRequest $request, Usuario $cliente, MedidaCliente $medida)
    {
        abort_unless($cliente->rol_id === 2, 404);
        abort_unless($medida->usuario_id === $cliente->usuario_id, 404);

        $data = Arr::except($request->validated(), ['tomadas_en_taller']);
        $data['tomadas_en_taller'] = $request->boolean('tomadas_en_taller');

        $medida->update($data);

        return redirect()->route('admin.clientes.edit', $cliente->usuario_id)
            ->with('success', 'Medidas del cliente actualizadas.');
    }
}
