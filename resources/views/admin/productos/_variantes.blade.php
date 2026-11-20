@extends('admin.productos.create')

@section('variantes')
@if($ventaPorTalla && isset($producto))
<div class="card border-0 shadow-sm rounded-4 max-w-4xl mx-auto mt-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-ruler-combined text-primary me-2"></i> Tallas y colores</h5>
        <span class="badge bg-light text-muted fw-normal">{{ $variantes->count() }} {{ \Illuminate\Support\Str::plural('variante', $variantes->count()) }}</span>
    </div>
    <div class="card-body p-4">
        @if($variantes->isEmpty())
            <p class="text-muted mb-4">Este modelo todavía no tiene tallas. Agrega la primera abajo.</p>
        @else
            <div class="table-responsive mb-4">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Talla</th>
                            <th>Color</th>
                            <th>SKU</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Activa</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($variantes as $variante)
                            <tr>
                                <td class="fw-bold">{{ $variante->talla }}</td>
                                <td>{{ $variante->color ?? '—' }}</td>
                                <td><code>{{ $variante->sku }}</code></td>
                                <td class="text-center">{{ $variante->stock }}</td>
                                <td class="text-center">
                                    @if($variante->activo)
                                        <span class="badge bg-success rounded-pill">Sí</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill">No</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#variante-{{ $variante->variante_id }}"
                                            aria-expanded="false" aria-controls="variante-{{ $variante->variante_id }}">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <form action="{{ route('admin.productos.variantes.destroy', [$producto->producto_id, $variante->variante_id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar esta talla?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <tr class="collapse" id="variante-{{ $variante->variante_id }}">
                                <td colspan="6" class="bg-light">
                                    <form action="{{ route('admin.productos.variantes.update', [$producto->producto_id, $variante->variante_id]) }}" method="POST" class="row g-3 align-items-end">
                                        @csrf
                                        @method('PUT')
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold" for="talla-{{ $variante->variante_id }}">Talla</label>
                                            <input class="form-control" id="talla-{{ $variante->variante_id }}" name="talla" list="tallas" value="{{ $variante->talla }}" required maxlength="10">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold" for="color-{{ $variante->variante_id }}">Color</label>
                                            <input class="form-control" id="color-{{ $variante->variante_id }}" name="color" value="{{ $variante->color }}" maxlength="40">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold" for="sku-{{ $variante->variante_id }}">SKU</label>
                                            <input class="form-control" id="sku-{{ $variante->variante_id }}" name="sku" value="{{ $variante->sku }}" required maxlength="40">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold" for="stock-{{ $variante->variante_id }}">Stock</label>
                                            <input type="number" min="0" step="1" class="form-control" id="stock-{{ $variante->variante_id }}" name="stock" value="{{ $variante->stock }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check">
                                                <input type="hidden" name="activo" value="0">
                                                <input class="form-check-input" type="checkbox" id="activo-{{ $variante->variante_id }}" name="activo" value="1" {{ $variante->activo ? 'checked' : '' }}>
                                                <label class="form-check-label small fw-bold" for="activo-{{ $variante->variante_id }}">Activa</label>
                                            </div>
                                        </div>
                                        <div class="col-md-1 text-end">
                                            <button type="submit" class="btn btn-primary" title="Guardar"><i class="fas fa-save"></i></button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary"><i class="fas fa-plus me-2"></i> Agregar talla</h6>
        <form action="{{ route('admin.productos.variantes.store', $producto->producto_id) }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-2">
                <label class="form-label small fw-bold" for="nueva-talla">Talla *</label>
                <input class="form-control @error('talla') is-invalid @enderror" id="nueva-talla" name="talla" list="tallas" value="{{ old('talla') }}" required maxlength="10" placeholder="40">
                @error('talla')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold" for="nuevo-color">Color</label>
                <input class="form-control @error('color') is-invalid @enderror" id="nuevo-color" name="color" value="{{ old('color') }}" maxlength="40" placeholder="Azul noche">
                @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold" for="nuevo-sku">SKU *</label>
                <input class="form-control @error('sku') is-invalid @enderror" id="nuevo-sku" name="sku" value="{{ old('sku') }}" required maxlength="40" placeholder="TER-AZUL-40">
                @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold" for="nuevo-stock">Stock *</label>
                <input type="number" min="0" step="1" class="form-control @error('stock') is-invalid @enderror" id="nuevo-stock" name="stock" value="{{ old('stock', 0) }}" required>
                @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input type="hidden" name="activo" value="0">
                    <input class="form-check-input" type="checkbox" id="nueva-activa" name="activo" value="1" checked>
                    <label class="form-check-label small fw-bold" for="nueva-activa">Activa</label>
                </div>
            </div>
            <div class="col-md-1 text-end">
                <button type="submit" class="btn btn-primary" title="Agregar"><i class="fas fa-plus"></i></button>
            </div>
        </form>

        <datalist id="tallas">
            @foreach($tallas as $talla)
                <option value="{{ $talla }}"></option>
            @endforeach
        </datalist>
    </div>
</div>
@endif
@endsection
