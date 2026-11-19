@if(isset($producto))
    <div class="card border-0 shadow-sm rounded-4 max-w-4xl mx-auto mt-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-images text-primary me-2"></i> Galería</h5>
            <span class="badge bg-light text-muted fw-normal">{{ $imagenes->count() }} / 8</span>
        </div>
        <div class="card-body p-4">
            @if($imagenes->isNotEmpty())
                <div class="row g-3 mb-4">
                    @foreach($imagenes as $imagen)
                        <div class="col-6 col-md-3">
                            <div class="border rounded-3 p-2 h-100 d-flex flex-column">
                                <img src="{{ asset($imagen->url) }}" alt="{{ $imagen->alt }}" class="img-fluid rounded-2 mb-2 object-fit-cover" style="height: 120px; width: 100%;">
                                @if($producto->imagen_url === $imagen->url)
                                    <span class="badge bg-success rounded-pill mb-2">Portada</span>
                                @endif
                                <form action="{{ route('admin.productos.imagenes.update', [$producto->producto_id, $imagen->imagen_id]) }}" method="POST" class="mb-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="alt" value="{{ $imagen->alt }}" class="form-control form-control-sm mb-1" maxlength="160" required aria-label="Texto alternativo">
                                    <button class="btn btn-sm btn-outline-primary w-100">Guardar alt</button>
                                </form>
                                <div class="d-flex gap-1 mb-1">
                                    <form method="POST" action="{{ route('admin.productos.imagenes.mover', [$producto->producto_id, $imagen->imagen_id, 'subir']) }}" class="flex-fill">@csrf @method('PUT')<button class="btn btn-sm btn-outline-secondary w-100" title="Subir">↑</button></form>
                                    <form method="POST" action="{{ route('admin.productos.imagenes.mover', [$producto->producto_id, $imagen->imagen_id, 'bajar']) }}" class="flex-fill">@csrf @method('PUT')<button class="btn btn-sm btn-outline-secondary w-100" title="Bajar">↓</button></form>
                                </div>
                                <div class="d-flex gap-1">
                                    <form method="POST" action="{{ route('admin.productos.imagenes.portada', [$producto->producto_id, $imagen->imagen_id]) }}" class="flex-fill">@csrf @method('PUT')<button class="btn btn-sm btn-outline-success w-100" title="Portada"><i class="fas fa-star"></i></button></form>
                                    <form method="POST" action="{{ route('admin.productos.imagenes.destroy', [$producto->producto_id, $imagen->imagen_id]) }}" class="flex-fill">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger w-100" title="Eliminar"><i class="fas fa-trash-alt"></i></button></form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($imagenes->count() < 8)
                <form action="{{ route('admin.productos.imagenes.store', $producto->producto_id) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="imagenes">Agregar imágenes</label>
                        <input type="file" class="form-control" id="imagenes" name="imagenes[]" accept=".jpg,.jpeg,.png,.webp" multiple required>
                        <div class="form-text">JPG, PNG o WebP. Máximo 2 MB y 4000×4000 px cada una.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="alt-galeria">Texto alternativo</label>
                        <input type="text" class="form-control" id="alt-galeria" name="alt" maxlength="160" placeholder="Terno azul noche, vista frontal">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Subir</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endif
