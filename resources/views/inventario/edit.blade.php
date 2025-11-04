@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Card centrado --}}
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 600px;">
        
        {{-- CAMBIO: Cabecera oscura y h4 para el título --}}
        <div class="card-header bg-dark text-white border-0">
            <h4 class="mb-0">Ajuste de Inventario: {{ $producto->nombre }}</h4>
            <small>Categoría: {{ $producto->categoria->nombre ?? 'N/A' }}</small>
        </div>

        {{-- CAMBIO: card-body con p-4 y sin alerta de sesión --}}
        <div class="card-body p-4">

            <form action="{{ route('inventario.update', $producto->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- CAMBIO: Sección con h6 y hr --}}
                <h6 class="text-muted">Stock Actual</h6>
                <hr class="mt-1 mb-3 border-secondary">

                {{-- Stock Actual --}}
                <div class="mb-3">
                    <label for="stock" class="form-label fw-bold">Ajustar Stock Actual</label>
                    {{-- CAMBIO: Input group con ícono --}}
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-boxes fa-fw"></i></span>
                        <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $producto->inventario->stock ?? 0) }}" required min="0">
                    </div>
                    <div class="form-text">Cantidad actual disponible del producto.</div>
                    @error('stock') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                
                {{-- CAMBIO: Sección con h6 y hr --}}
                <h6 class="text-muted mt-4">Límites de Alerta</h6>
                <hr class="mt-1 mb-3 border-secondary">

                {{-- Cantidad Mínima --}}
                <div class="mb-3">
                    <label for="cantidad_minima" class="form-label">Cantidad Mínima</label>
                    {{-- CAMBIO: Input group con ícono --}}
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-exclamation-triangle fa-fw"></i></span>
                        <input type="number" class="form-control @error('cantidad_minima') is-invalid @enderror" id="cantidad_minima" name="cantidad_minima" value="{{ old('cantidad_minima', $producto->inventario->cantidad_minima ?? 0) }}" min="0">
                    </div>
                    <div class="form-text">Alerta si el stock cae por debajo de este valor.</div>
                    @error('cantidad_minima') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- Cantidad Máxima --}}
                <div class="mb-3">
                    <label for="cantidad_maxima" class="form-label">Cantidad Máxima (Opcional)</label>
                    {{-- CAMBIO: Input group con ícono --}}
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-archive fa-fw"></i></span>
                        <input type="number" class="form-control @error('cantidad_maxima') is-invalid @enderror" id="cantidad_maxima" name="cantidad_maxima" value="{{ old('cantidad_maxima', $producto->inventario->cantidad_maxima ?? 0) }}" min="0">
                    </div>
                    @error('cantidad_maxima') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                
                {{-- Botones --}}
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('inventario.index') }}" class="btn btn-secondary">
                        {{-- CAMBIO: Ícono añadido --}}
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    
                    @if (Auth::user()->hasPermissionTo('inventario', 'editar'))
                        <button type="submit" class="btn btn-success">
                            {{-- CAMBIO: Ícono estandarizado --}}
                            <i class="fas fa-save me-1"></i> Guardar Ajustes
                        </button>
                    @else
                         <span class="text-danger">No tienes permiso para editar el inventario.</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection