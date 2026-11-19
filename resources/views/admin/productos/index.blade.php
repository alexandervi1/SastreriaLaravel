@extends('layouts.admin')

@section('title', 'Gestión de Productos')

@section('actions')
<a href="{{ route('admin.productos.create') }}" class="btn btn-primary fw-bold shadow-sm rounded-pill px-4">
    <i class="fas fa-plus me-2"></i> Nuevo Producto
</a>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-fish text-primary me-2"></i> Catálogo de Productos</h5>
        <span class="badge bg-light text-muted fw-normal">{{ $productos->count() }} {{ \Illuminate\Support\Str::plural('registro', $productos->count()) }}</span>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">Producto</th>
                        <th scope="col">Categoría</th>
                        <th scope="col" class="text-center">Precio Unit.</th>
                        <th scope="col" class="text-center">Stock</th>
                        <th scope="col" class="text-center d-none d-xl-table-cell">Unidades en bodega</th>
                        <th scope="col" class="text-center pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset($producto->imagen_url ?? 'img/placeholder.svg') }}" class="rounded-3 me-3 object-fit-cover shadow-sm border" style="width: 50px; height: 50px;" alt="{{ $producto->nombre }}">
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $producto->nombre }}</h6>
                                        <div class="small text-muted text-truncate product-desc">{{ $producto->descripcion }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark rounded-pill badge-categoria">{{ $producto->categoria->nombre ?? 'Ninguna' }}</span>
                            </td>
                            <td class="text-center fw-bold text-success">
                                ${{ number_format($producto->precio, 2) }}
                            </td>
                            <td class="text-center">
                                @if($producto->stock < 50)
                                    <span class="badge bg-danger rounded-pill px-2">{{ $producto->stock }} u</span>
                                @else
                                    <span class="px-2">{{ $producto->stock }} u</span>
                                @endif
                            </td>
                            <td class="text-center text-muted small d-none d-xl-table-cell">
                                {{ $producto->stock }} u
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.productos.edit', $producto->producto_id) }}" class="btn btn-sm btn-outline-primary btn-action" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.productos.destroy', $producto->producto_id) }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-action" title="Eliminar" onclick="return confirm('¿Confirma la eliminación?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-fish fa-3x text-muted mb-3 opacity-50"></i>
                                <h5 class="text-muted">No se han encontrado productos.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
