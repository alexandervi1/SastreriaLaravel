<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedidaClienteRequest;
use App\Models\MedidaCliente;
use Illuminate\Support\Arr;

class MedidaClienteController extends Controller
{
    public function index()
    {
        $medidas = MedidaCliente::where('usuario_id', auth()->id())
            ->orderBy('nombre')
            ->get();

        return view('medidas.index', compact('medidas'));
    }

    public function create()
    {
        return view('medidas.create');
    }

    public function store(MedidaClienteRequest $request)
    {
        $data = Arr::except($request->validated(), ['tomadas_en_taller']);
        $data['usuario_id'] = auth()->id();
        $data['tomadas_en_taller'] = $request->boolean('tomadas_en_taller');
        $data['patron'] = $data['patron'] ?? 'caballero';

        MedidaCliente::create($data);

        return redirect()->route('medidas.index')
            ->with('success', 'Perfil de medidas creado.');
    }

    public function edit(MedidaCliente $medida)
    {
        $this->propia($medida);

        return view('medidas.edit', compact('medida'));
    }

    public function update(MedidaClienteRequest $request, MedidaCliente $medida)
    {
        $this->propia($medida);

        $data = Arr::except($request->validated(), ['tomadas_en_taller']);
        $data['tomadas_en_taller'] = $request->boolean('tomadas_en_taller');

        $medida->update($data);

        return redirect()->route('medidas.index')
            ->with('success', 'Perfil de medidas actualizado.');
    }

    public function destroy(MedidaCliente $medida)
    {
        $this->propia($medida);

        $medida->delete();

        return redirect()->route('medidas.index')
            ->with('success', 'Perfil de medidas eliminado.');
    }

    private function propia(MedidaCliente $medida): void
    {
        abort_unless($medida->usuario_id === auth()->id(), 404);
    }
}
