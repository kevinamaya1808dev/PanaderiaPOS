@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-header bg-warning text-dark">
            <h3 class="mb-0">Ajuste de Inventario: {{ $producto->nombre }}</h3>
            <small>Categoría: {{ $producto->categoria->nombre ?? 'N/A' }}</small>
        </div>
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('inventario.update', $producto->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Stock Actual --}}
                <div class="mb-3">
                    <label for="stock" class="form-label fw-bold">Stock Actual</label>
                    <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $producto->inventario->stock ?? 0) }}" required min="0">
                    <div class="form-text">Cantidad actual disponible del producto.</div>
                    @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <hr>

                {{-- Cantidad Mínima --}}
                <div class="mb-3">
                    <label for="cantidad_minima" class="form-label">Cantidad Mínima de Alerta</label>
                    <input type="number" class="form-control @error('cantidad_minima') is-invalid @enderror" id="cantidad_minima" name="cantidad_minima" value="{{ old('cantidad_minima', $producto->inventario->cantidad_minima ?? 0) }}" min="0">
                    <div class="form-text">Se generará una alerta si el stock cae por debajo de este valor.</div>
                    @error('cantidad_minima') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Cantidad Máxima --}}
                <div class="mb-3">
                    <label for="cantidad_maxima" class="form-label">Cantidad Máxima (Opcional)</label>
                    <input type="number" class="form-control @error('cantidad_maxima') is-invalid @enderror" id="cantidad_maxima" name="cantidad_maxima" value="{{ old('cantidad_maxima', $producto->inventario->cantidad_maxima ?? 0) }}" min="0">
                    @error('cantidad_maxima') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('inventario.index') }}" class="btn btn-secondary">Cancelar</a>
                    
                    {{-- Botón Actualizar (Protegido por la ruta) --}}
                    @if (Auth::user()->hasPermissionTo('inventario', 'editar'))
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-sync"></i> Guardar Ajustes
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
```

### **Paso 4: Actualizar Navegación**

**Modifica `resources/views/layouts/navigation.blade.php`** para activar el enlace:

Busca la línea del módulo **Inventario** y reemplázala:

```html
{{-- INVENTARIO (Módulo: inventario) --}}
@if (Auth::user()->hasPermissionTo('inventario', 'mostrar'))
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('inventario.index') ? 'active bg-secondary' : '' }}" href="{{ route('inventario.index') }}">
            <i class="fas fa-warehouse me-2"></i> Inventario
        </a>
    </li>
@endif
