<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TelaRequest;
use App\Models\Tela;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TelaController extends Controller
{
    public function index()
    {
        $telas = Tela::orderBy('nombre')->get();

        return view('admin.telas.index', compact('telas'));
    }

    public function create()
    {
        return view('admin.telas.create');
    }

    public function store(TelaRequest $request)
    {
        $data = Arr::except($request->validated(), ['imagen']);
        $data['activo'] = $request->boolean('activo', true);

        if ($request->hasFile('imagen')) {
            $data['imagen_url'] = $this->guardarImagen($request);
        }

        Tela::create($data);

        return redirect()->route('admin.telas.index')
            ->with('success', 'Tela creada exitosamente.');
    }

    public function edit(Tela $tela)
    {
        return view('admin.telas.edit', compact('tela'));
    }

    public function update(TelaRequest $request, Tela $tela)
    {
        $data = Arr::except($request->validated(), ['imagen']);
        $data['activo'] = $request->boolean('activo');

        if ($request->hasFile('imagen')) {
            $this->borrarImagen($tela->imagen_url);
            $data['imagen_url'] = $this->guardarImagen($request);
        }

        $tela->update($data);

        return redirect()->route('admin.telas.index')
            ->with('success', 'Tela actualizada exitosamente.');
    }

    public function destroy(Tela $tela)
    {
        $imagen = $tela->imagen_url;

        $tela->delete();

        $this->borrarImagen($imagen);

        return redirect()->route('admin.telas.index')
            ->with('success', 'Tela eliminada exitosamente.');
    }

    private function guardarImagen(Request $request): string
    {
        $file = $request->file('imagen');
        $filename = Str::uuid() . '.' . strtolower($file->extension());
        $file->move(public_path('img/telas'), $filename);

        return 'img/telas/' . $filename;
    }

    /**
     * Borra solo imágenes gestionadas por la aplicación (dentro de public/img).
     */
    private function borrarImagen(?string $url): void
    {
        if (! $url || ! Str::startsWith($url, 'img/')) {
            return;
        }

        $base = realpath(public_path('img'));
        $ruta = realpath(public_path($url));

        if ($base === false || $ruta === false || ! Str::startsWith($ruta, $base)) {
            return;
        }

        if (is_file($ruta)) {
            unlink($ruta);
        }
    }
}
