@extends('layouts.admin')

@section('title', 'Configuración del Sistema')

@section('content')
<div class="row mt-4">
    <div class="col-lg-10 mx-auto">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
                <ul class="nav nav-tabs border-bottom-0 flex-nowrap overflow-auto" id="configTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $tab === 'negocio' ? 'active fw-bold text-primary px-4 py-3 border-0 text-nowrap border-bottom border-primary border-3' : 'fw-bold text-secondary px-4 py-3 border-0 text-nowrap' }}" id="negocio-tab" data-bs-toggle="tab" data-bs-target="#negocio" type="button" role="tab" aria-controls="negocio" aria-selected="{{ $tab === 'negocio' ? 'true' : 'false' }}">
                            <i class="fas fa-store me-2"></i> Datos del Negocio
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $tab === 'taller' ? 'active fw-bold text-primary px-4 py-3 border-0 text-nowrap border-bottom border-primary border-3' : 'fw-bold text-secondary px-4 py-3 border-0 text-nowrap' }}" id="taller-tab" data-bs-toggle="tab" data-bs-target="#taller" type="button" role="tab" aria-controls="taller" aria-selected="{{ $tab === 'taller' ? 'true' : 'false' }}">
                            <i class="fas fa-scissors me-2"></i> Taller
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $tab === 'seguridad' ? 'active fw-bold text-primary px-4 py-3 border-0 text-nowrap border-bottom border-primary border-3' : 'fw-bold text-secondary px-4 py-3 border-0 text-nowrap' }}" id="seguridad-tab" data-bs-toggle="tab" data-bs-target="#seguridad" type="button" role="tab" aria-controls="seguridad" aria-selected="{{ $tab === 'seguridad' ? 'true' : 'false' }}">
                            <i class="fas fa-shield-alt me-2"></i> Seguridad y Acceso
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $tab === 'historial' ? 'active fw-bold text-primary px-4 py-3 border-0 text-nowrap border-bottom border-primary border-3' : 'fw-bold text-secondary px-4 py-3 border-0 text-nowrap' }}" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab" aria-controls="historial" aria-selected="{{ $tab === 'historial' ? 'true' : 'false' }}">
                            <i class="fas fa-clock-rotate-left me-2"></i> Historial
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0 bg-light">
                <div class="tab-content" id="configTabsContent">

                    <div class="tab-pane fade {{ $tab === 'negocio' ? 'show active' : '' }} p-4 p-md-5 bg-white" id="negocio" role="tabpanel" aria-labelledby="negocio-tab">
                        <form action="{{ route('admin.configuracion.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <h6 class="fw-bold mb-4 text-primary"><i class="fas fa-file-invoice-dollar me-2"></i> Información Fiscal Tributaria</h6>
                            <div class="row g-4 mb-5">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Razón Social</label>
                                    <input type="text" class="form-control" name="negocio_nombre" value="{{ old('negocio_nombre', $configuraciones['negocio_nombre']) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Identificación (RUC)</label>
                                    <input type="text" class="form-control" name="negocio_ruc" value="{{ old('negocio_ruc', $configuraciones['negocio_ruc']) }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-secondary">Leyenda (Ej. Contribuyente Régimen RIMPE)</label>
                                    <input type="text" class="form-control" name="negocio_leyenda" value="{{ old('negocio_leyenda', $configuraciones['negocio_leyenda']) }}">
                                </div>
                            </div>

                            <h6 class="fw-bold mb-4 text-primary"><i class="fas fa-map-marked-alt me-2"></i> Local y Contactos</h6>
                            <div class="row g-4 mb-5">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Dirección pública</label>
                                    <input type="text" class="form-control" name="negocio_direccion_publica" value="{{ old('negocio_direccion_publica', $configuraciones['negocio_direccion_publica']) }}" placeholder="Riobamba, sector…">
                                    <div class="form-text mt-2"><i class="fas fa-eye me-1"></i> Se muestra en la web y en los comprobantes.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Dirección exacta del taller (privada)</label>
                                    <input type="text" class="form-control" name="negocio_direccion" value="{{ old('negocio_direccion', $configuraciones['negocio_direccion']) }}" required>
                                    <div class="form-text mt-2"><i class="fas fa-lock me-1"></i> Solo la ve el cliente con una cita confirmada en el taller y el panel.</div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold text-secondary">Teléfono de Soporte</label>
                                    <input type="text" class="form-control" name="negocio_telefono" value="{{ old('negocio_telefono', $configuraciones['negocio_telefono']) }}" required>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-4 text-primary"><i class="fas fa-image me-2"></i> Identidad Visual</h6>
                            <div class="row g-4 align-items-center mb-5">
                                <div class="col-md-2 text-center">
                                    <img src="{{ asset($configuraciones['negocio_logo']) }}" alt="Logo actual" class="img-thumbnail shadow-sm rounded-4 bg-white" style="max-height: 80px; width: 100%; object-fit: contain;">
                                    <span class="d-block small text-muted mt-2">Logo actual</span>
                                </div>
                                <div class="col-md-10">
                                    <label for="negocio_logo" class="form-label fw-bold">Cambiar logo</label>
                                    <input class="form-control" type="file" id="negocio_logo" name="negocio_logo" accept=".jpg,.jpeg,.png,.webp">
                                    <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> JPG, PNG o WEBP, máximo 2MB. Se usa en la tienda, el panel y los comprobantes.</div>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-4 text-primary"><i class="fas fa-ruler-combined me-2"></i> Modos de venta</h6>
                            <div class="row g-4 mb-5">
                                <div class="col-md-12">
                                    <input type="hidden" name="venta_por_talla" value="0">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="venta_por_talla" name="venta_por_talla" value="1" {{ old('venta_por_talla', $configuraciones['venta_por_talla']) === '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-secondary" for="venta_por_talla">Permitir venta por talla</label>
                                    </div>
                                    <div class="form-text mt-2">Apagado, la tienda solo acepta encargos a medida. Al activarlo vuelven las tallas, el stock y sus reportes.</div>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-4 text-primary"><i class="fas fa-calculator me-2"></i> Cálculos Comerciales</h6>
                            <div class="row g-4 mb-5">
                                <div class="col-md-3">
                                    <label for="iva_actual" class="form-label fw-bold text-secondary">IVA (%)</label>
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-percent text-primary"></i></span>
                                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="iva_actual" name="iva_actual" value="{{ old('iva_actual', $configuraciones['iva_actual']) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="umbral_bajo_stock" class="form-label fw-bold text-secondary">Umbral bajo stock</label>
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-boxes-stacked text-primary"></i></span>
                                        <input type="number" min="0" class="form-control" id="umbral_bajo_stock" name="umbral_bajo_stock" value="{{ old('umbral_bajo_stock', $configuraciones['umbral_bajo_stock']) }}" required>
                                    </div>
                                    <div class="form-text mt-2">Unidades de venta por debajo de las cuales se alerta.</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="comprobante_serie" class="form-label fw-bold text-secondary">Serie de comprobante</label>
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-receipt text-primary"></i></span>
                                        <input type="text" class="form-control" id="comprobante_serie" name="comprobante_serie" value="{{ old('comprobante_serie', $configuraciones['comprobante_serie']) }}" pattern="[0-9]{3}-[0-9]{3}" required>
                                    </div>
                                    <div class="form-text mt-2">Formato 000-000.</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="password_rotacion_dias" class="form-label fw-bold text-secondary">Rotación de contraseña</label>
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-key text-primary"></i></span>
                                        <input type="number" min="1" max="3650" class="form-control" id="password_rotacion_dias" name="password_rotacion_dias" value="{{ old('password_rotacion_dias', $configuraciones['password_rotacion_dias']) }}" required>
                                    </div>
                                    <div class="form-text mt-2">Días antes de avisar que la clave venció.</div>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-4 text-primary"><i class="fas fa-globe me-2"></i> Sistema</h6>
                            <div class="row g-4 mb-4">
                                <div class="col-md-5">
                                    <label for="zona_horaria" class="form-label fw-bold text-secondary">Zona horaria</label>
                                    <select class="form-select" id="zona_horaria" name="zona_horaria" required>
                                        @foreach($zonas as $zona)
                                            <option value="{{ $zona }}" {{ old('zona_horaria', $configuraciones['zona_horaria']) === $zona ? 'selected' : '' }}>{{ $zona }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text mt-2">Afecta fechas de pedidos, comprobantes y reportes.</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm rounded-pill px-5">
                                    Guardar Negocio <i class="fas fa-save ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade {{ $tab === 'taller' ? 'show active' : '' }} p-4 p-md-5 bg-white" id="taller" role="tabpanel" aria-labelledby="taller-tab">
                        <form action="{{ route('admin.configuracion.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <h6 class="fw-bold mb-4 text-primary"><i class="fas fa-clock me-2"></i> Horario de atención</h6>
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary" for="taller_dias">Días de atención (ISO)</label>
                                    <input type="text" class="form-control" id="taller_dias" name="taller_dias" value="{{ old('taller_dias', $configuraciones['taller_dias']) }}" placeholder="1,2,3,4,5,6">
                                    <div class="form-text">1 = lunes … 7 = domingo, separados por comas.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-secondary" for="taller_hora_inicio">Apertura</label>
                                    <input type="time" class="form-control" id="taller_hora_inicio" name="taller_hora_inicio" value="{{ old('taller_hora_inicio', $configuraciones['taller_hora_inicio']) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-secondary" for="taller_hora_fin">Cierre</label>
                                    <input type="time" class="form-control" id="taller_hora_fin" name="taller_hora_fin" value="{{ old('taller_hora_fin', $configuraciones['taller_hora_fin']) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-secondary" for="cita_duracion_min">Duración de cada cita (min)</label>
                                    <input type="number" min="15" max="240" class="form-control" id="cita_duracion_min" name="cita_duracion_min" value="{{ old('cita_duracion_min', $configuraciones['cita_duracion_min']) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-secondary" for="cita_anticipacion_max_dias">Anticipación máxima (días)</label>
                                    <input type="number" min="1" max="365" class="form-control" id="cita_anticipacion_max_dias" name="cita_anticipacion_max_dias" value="{{ old('cita_anticipacion_max_dias', $configuraciones['cita_anticipacion_max_dias']) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-secondary" for="cita_cancelacion_min_horas">Cancelación mínima (horas)</label>
                                    <input type="number" min="0" max="168" class="form-control" id="cita_cancelacion_min_horas" name="cita_cancelacion_min_horas" value="{{ old('cita_cancelacion_min_horas', $configuraciones['cita_cancelacion_min_horas']) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary" for="negocio_whatsapp">WhatsApp del taller</label>
                                    <input type="text" class="form-control" id="negocio_whatsapp" name="negocio_whatsapp" value="{{ old('negocio_whatsapp', $configuraciones['negocio_whatsapp']) }}" placeholder="593999999999">
                                    <div class="form-text">Formato internacional sin «+».</div>
                                </div>
                                <div class="col-12"><hr class="my-2"></div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="cita_domicilio_activa" value="0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="cita_domicilio_activa" name="cita_domicilio_activa" value="1" {{ old('cita_domicilio_activa', $configuraciones['cita_domicilio_activa']) === '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-secondary" for="cita_domicilio_activa">Permitir citas a domicilio</label>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold text-secondary" for="cita_domicilio_zona">Zona de visitas</label>
                                    <input type="text" class="form-control" id="cita_domicilio_zona" name="cita_domicilio_zona" value="{{ old('cita_domicilio_zona', $configuraciones['cita_domicilio_zona']) }}" placeholder="Solo dentro de Riobamba urbano">
                                    <div class="form-text">Se muestra al cliente al elegir domicilio.</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm rounded-pill px-5">
                                    Guardar Taller <i class="fas fa-save ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade {{ $tab === 'seguridad' ? 'show active' : '' }} p-4 p-md-5 bg-white" id="seguridad" role="tabpanel" aria-labelledby="seguridad-tab">
                        <div class="alert alert-info rounded-3 mb-5 border-0 shadow-sm d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x me-3 text-info"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Protección de Cuenta</h6>
                                <p class="mb-0 small text-dark">Modifique su usuario, correo y contraseña maestra aquí. Al cambiar usuario o contraseña, el sistema cerrará la sesión.</p>
                            </div>
                        </div>

                        <form action="{{ route('admin.configuracion.seguridad') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Nombre Completo del Administrador</label>
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-id-badge text-primary"></i></span>
                                        <input type="text" class="form-control" name="nombre_completo" value="{{ old('nombre_completo', auth()->user()->nombre_completo) }}" required autocomplete="name">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Correo Electrónico</label>
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-envelope text-primary"></i></span>
                                        <input type="email" class="form-control" name="correo" value="{{ old('correo', auth()->user()->correo) }}" required autocomplete="email">
                                    </div>
                                    <div class="form-text mt-2">También sirve para iniciar sesión.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Nombre de Usuario</label>
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-user-shield text-primary"></i></span>
                                        <input type="text" class="form-control" name="nombre_usuario" value="{{ old('nombre_usuario', auth()->user()->nombre_usuario) }}" required autocomplete="username">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Contraseña Actual</label>
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-primary"></i></span>
                                        <input type="password" class="form-control" name="current_password" required autocomplete="current-password" placeholder="Confirma tu contraseña">
                                    </div>
                                    <div class="form-text mt-2">Necesaria para guardar cualquier cambio de credenciales.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Nueva Contraseña</label>
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-key text-primary"></i></span>
                                        <input type="password" class="form-control" name="password" placeholder="Dejar en blanco para no cambiar" autocomplete="new-password">
                                    </div>
                                    <div class="form-text mt-2">Mínimo 8 caracteres.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Confirmar Nueva Contraseña</label>
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-check-double text-primary"></i></span>
                                        <input type="password" class="form-control" name="password_confirmation" placeholder="Repite la nueva contraseña" autocomplete="new-password">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                                <button type="submit" class="btn btn-brand-dark btn-lg fw-bold shadow-sm rounded-pill px-5">
                                    Actualizar Seguridad <i class="fas fa-lock ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade {{ $tab === 'historial' ? 'show active' : '' }} p-4 p-md-5 bg-white" id="historial" role="tabpanel" aria-labelledby="historial-tab">
                        <h6 class="fw-bold mb-4 text-primary"><i class="fas fa-clock-rotate-left me-2"></i> Últimos cambios de configuración</h6>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Fecha</th>
                                        <th>Usuario</th>
                                        <th>Clave</th>
                                        <th>Antes</th>
                                        <th class="pe-3">Después</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($historial as $cambio)
                                        <tr>
                                            <td class="ps-3 text-muted small">{{ $cambio->created_at?->format('d/m/Y H:i') }}</td>
                                            <td class="small">{{ $cambio->usuario->nombre_usuario ?? 'Sistema' }}</td>
                                            <td><code class="small">{{ $cambio->clave }}</code></td>
                                            <td class="small text-muted">{{ $cambio->valor_anterior ?? '—' }}</td>
                                            <td class="small fw-bold pe-3">{{ $cambio->valor_nuevo ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <i class="fas fa-clock-rotate-left fa-3x text-muted mb-3 opacity-50"></i>
                                                <h5 class="text-muted">Todavía no hay cambios registrados.</h5>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash === '#seguridad') {
            var tab = new bootstrap.Tab(document.querySelector('#seguridad-tab'));
            tab.show();
        }

        const tabs = document.querySelectorAll('#configTabs .nav-link');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                tabs.forEach(t => {
                    t.classList.remove('text-primary', 'border-bottom', 'border-primary', 'border-3');
                    t.classList.add('text-secondary', 'border-0');
                });
                e.target.classList.remove('text-secondary', 'border-0');
                e.target.classList.add('text-primary', 'border-bottom', 'border-primary', 'border-3');
            });
        });
    });
</script>
@endpush
