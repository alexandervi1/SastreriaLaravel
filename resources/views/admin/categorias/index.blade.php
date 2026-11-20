@extends('layouts.admin')

@section('title', 'Gestión de Categorías')

@section('actions')
<button type="button" class="btn btn-primary fw-bold shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalCategoria">
    <i class="fas fa-plus-circle me-2"></i> Nueva Categoría
</button>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-tags text-primary me-2"></i> Listado de Categorías</h5>
        <span class="badge bg-light text-muted fw-normal">{{ $categorias->count() }} {{ \Illuminate\Support\Str::plural('registro', $categorias->count()) }}</span>
    </div>
    
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th class="rounded-start">ID</th>
                        <th>Nombre de Categoría</th>
                        <th class="text-center">Productos Asociados</th>
                        <th class="text-end rounded-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categorias as $cat)
                    <tr>
                        <td class="fw-bold text-secondary">#{{ str_pad($cat->categoria_id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>
                            <span class="fw-bold text-dark">{{ $cat->nombre }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $cat->productos_count > 0 ? 'bg-info' : 'bg-secondary' }} rounded-pill px-3">
                                {{ $cat->productos_count }} ítems
                            </span>
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-action btn-edit" 
                                    data-id="{{ $cat->categoria_id }}" 
                                    data-nombre="{{ $cat->nombre }}" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalCategoria"
                                    title="Editar Categoría">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <form action="{{ route('admin.categorias.destroy', $cat->categoria_id) }}" method="POST" class="d-inline form-delete m-0 p-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action btn-delete" title="Eliminar Categoría">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="fas fa-tags fa-3x text-muted mb-3 opacity-50"></i>
                                <h5 class="text-muted">No hay categorías registradas.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Combinado (Crear/Editar) -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-primary" id="modalCategoriaLabel"><i class="fas fa-tag me-2"></i> <span id="modo-titulo">Nueva Categoría</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('admin.categorias.store') }}" method="POST" id="formCategoria">
                @csrf
                <div id="method-container"></div>
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-bold">Nombre de la Categoría *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required placeholder="Ej. Ternos" autofocus>
                        <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> Esta categoría aparecerá visible públicamente en la barra lateral del catálogo.</div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm" id="btn-guardar">Registrar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modal logic
        const modalCategoria = document.getElementById('modalCategoria');
        const formCategoria = document.getElementById('formCategoria');
        const titulo = document.getElementById('modo-titulo');
        const inputNombre = document.getElementById('nombre');
        const btnGuardar = document.getElementById('btn-guardar');
        const methodContainer = document.getElementById('method-container');
        
        const storeRoute = "{{ route('admin.categorias.store') }}";

        // Reset forms when completely hidden
        modalCategoria.addEventListener('hidden.bs.modal', function () {
            formCategoria.reset();
            formCategoria.action = storeRoute;
            methodContainer.innerHTML = '';
            titulo.textContent = 'Nueva Categoría';
            btnGuardar.textContent = 'Registrar Categoría';
        });

        // Edit button triggers modal prep
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                
                // Form setup for PUT
                formCategoria.action = `{{ url('/admin/categorias') }}/${id}`;
                methodContainer.innerHTML = '@method("PUT")';
                
                // Set data
                inputNombre.value = nombre;
                titulo.textContent = 'Editar Categoría';
                btnGuardar.textContent = 'Guardar Cambios';
            });
        });

        // Delete Confirmations using Sweet Alert logic or native
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                if (confirm('¿Estás seguro de eliminar esta categoría?\n\nLos productos dentro de ella no se borrarán, pero quedarán sin categoría asignada.')) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
