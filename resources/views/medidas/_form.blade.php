@php($m = $medida ?? null)
<div class="row g-4">
    <div class="col-md-6">
        <label for="nombre" class="form-label fw-bold">Nombre del perfil *</label>
        <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre', $m->nombre ?? '') }}" required maxlength="60" placeholder="Mis medidas">
        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="estatura" class="form-label fw-bold">Estatura (cm)</label>
        <input type="number" step="0.1" min="100" max="230" class="form-control @error('estatura') is-invalid @enderror" id="estatura" name="estatura" value="{{ old('estatura', $m->estatura ?? '') }}">
        @error('estatura')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="tomadas_en_taller" value="0">
            <input class="form-check-input" type="checkbox" id="tomadas_en_taller" name="tomadas_en_taller" value="1" {{ old('tomadas_en_taller', $m->tomadas_en_taller ?? false) ? 'checked' : '' }}>
            <label class="form-check-label fw-bold" for="tomadas_en_taller">Tomadas en el taller</label>
        </div>
    </div>
</div>

<h6 class="fw-bold border-bottom pb-2 mt-4 mb-3 text-primary"><i class="fas fa-vest me-2"></i> Saco (cm)</h6>
<div class="row g-3">
    @foreach(['pecho' => 'Pecho', 'cintura' => 'Cintura', 'cadera' => 'Cadera', 'hombros' => 'Hombros', 'espalda' => 'Espalda', 'largo_saco' => 'Largo del saco', 'largo_manga' => 'Largo de manga', 'cuello' => 'Cuello'] as $campo => $etiqueta)
        <div class="col-6 col-md-3">
            <label for="{{ $campo }}" class="form-label small fw-bold">{{ $etiqueta }}</label>
            <input type="number" step="0.1" min="10" max="250" class="form-control @error($campo) is-invalid @enderror" id="{{ $campo }}" name="{{ $campo }}" value="{{ old($campo, $m->{$campo} ?? '') }}">
            @error($campo)<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @endforeach
</div>

<h6 class="fw-bold border-bottom pb-2 mt-4 mb-3 text-primary"><i class="fas fa-ruler me-2"></i> Pantalón (cm)</h6>
<div class="row g-3">
    @foreach(['cintura_pantalon' => 'Cintura', 'cadera_pantalon' => 'Cadera', 'tiro' => 'Tiro', 'muslo' => 'Muslo', 'entrepierna' => 'Entrepierna', 'largo_pantalon' => 'Largo', 'bota' => 'Bota'] as $campo => $etiqueta)
        <div class="col-6 col-md-3">
            <label for="{{ $campo }}" class="form-label small fw-bold">{{ $etiqueta }}</label>
            <input type="number" step="0.1" min="10" max="250" class="form-control @error($campo) is-invalid @enderror" id="{{ $campo }}" name="{{ $campo }}" value="{{ old($campo, $m->{$campo} ?? '') }}">
            @error($campo)<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @endforeach
</div>

<div class="mt-4">
    <label for="notas" class="form-label fw-bold">Notas</label>
    <textarea class="form-control @error('notas') is-invalid @enderror" id="notas" name="notas" rows="3" maxlength="1000">{{ old('notas', $m->notas ?? '') }}</textarea>
    @error('notas')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<p class="text-muted small mt-3 mb-0">
    Para confeccionar necesitamos al menos pecho, cintura, largo de manga, entrepierna y largo de pantalón.
    <a href="{{ route('ayuda') }}#tomar-medidas">Cómo tomar tus medidas</a>.
</p>
