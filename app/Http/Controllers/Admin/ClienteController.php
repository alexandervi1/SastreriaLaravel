<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Usuario::with('rol')->where('rol_id', 2)->get();
        return view('admin.clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('admin.clientes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'nombre_usuario'  => 'required|string|max:255|unique:usuario,nombre_usuario',
            'password'        => 'required|string|min:8',
            'direccion'       => 'required|string|max:100',
            'correo'          => 'required|email|max:100',
            'telefono'        => 'nullable|string|max:10',
        ]);

        Usuario::create([
            'nombre_usuario'  => $data['nombre_usuario'],
            'nombre_completo' => $data['nombre_completo'],
            'password'        => Hash::make($data['password']),
            'rol_id'          => 2, // Siempre cliente desde este panel
            'direccion'       => $data['direccion'],
            'correo'          => $data['correo'],
            'telefono'        => $data['telefono'] ?? null,
        ]);

        return redirect()->route('admin.clientes.index')->with('success', 'Cliente registrado correctamente.');
    }

    public function edit(Usuario $cliente)
    {
        $this->soloCliente($cliente);

        $medidas = $cliente->medidas()->orderBy('nombre')->get();

        return view('admin.clientes.edit', compact('cliente', 'medidas'));
    }

    public function update(Request $request, Usuario $cliente)
    {
        $this->soloCliente($cliente);

        $data = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'nombre_usuario'  => 'required|string|max:255|unique:usuario,nombre_usuario,' . $cliente->usuario_id . ',usuario_id',
            'direccion'       => 'required|string|max:100',
            'correo'          => 'required|email|max:100',
            'telefono'        => 'nullable|string|max:10',
            'password'        => 'nullable|string|min:8',
        ]);

        $cambios = [
            'nombre_completo' => $data['nombre_completo'],
            'nombre_usuario'  => $data['nombre_usuario'],
            'direccion'       => $data['direccion'],
            'correo'          => $data['correo'],
            'telefono'        => $data['telefono'] ?? null,
        ];

        if (!empty($data['password'])) {
            $cambios['password'] = Hash::make($data['password']);
            $cambios['password_changed_at'] = now();
        }

        $cliente->update($cambios);

        return redirect()->route('admin.clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Usuario $cliente)
    {
        $this->soloCliente($cliente);

        if ($cliente->usuario_id === auth()->id()) {
            return redirect()->route('admin.clientes.index')
                ->with('error', 'No puedes eliminar tu propia cuenta desde este panel.');
        }

        if ($cliente->pedidos()->exists()) {
            return redirect()->route('admin.clientes.index')
                ->with('error', 'No se puede eliminar un cliente con pedidos registrados.');
        }

        $cliente->delete();

        return redirect()->route('admin.clientes.index')->with('success', 'Cliente eliminado.');
    }

    /**
     * Este panel solo administra usuarios con rol Cliente.
     */
    private function soloCliente(Usuario $usuario): void
    {
        abort_unless($usuario->rol_id === 2, 404);
    }
}
