@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                {{-- CAMBIO: Título dinámico --}}
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-edit me-2"></i> Editar Producto: {{ $producto->nombre }}
                </div>
                <div class="card-body">
                    
                    {{-- ¡MUY IMPORTANTE! 
                         1. Apuntar a la ruta 'update' con el ID del producto
                         2. Añadir método 'PUT'
                         3. Añadir 'enctype' para la imagen --}}
                    <form action="{{ route('productos.update', $producto->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <h5 class="text-muted mb-3">Detalles del Producto</h5>

                        {{-- Campo Nombre --}}
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Producto</label>
                            {{-- CAMBIO: Cargar valor del producto --}}
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre', $producto->nombre) }}" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Campo Descripción --}}
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                            {{-- CAMBIO: Cargar valor del producto --}}
                            <textarea class="form-control" id="descripcion" name="descripcion">{{ old('descripcion', $producto->descripcion) }}</textarea>
                        </div>

                        <div class="row">
                            {{-- Campo Precio --}}
                            <div class="col-md-6 mb-3">
                                <label for="precio" class="form-label">Precio de Venta</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    {{-- CAMBIO: Cargar valor del producto --}}
                                    <input type="number" class="form-control @error('precio') is-invalid @enderror" id="precio" name="precio" value="{{ old('precio', $producto->precio) }}" step="0.01" min="0" required>
                                </div>
                                @error('precio')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Campo Categoría --}}
                            <div class="col-md-6 mb-3">
                                <label for="categoria_id" class="form-label">Categoría</label>
                                <select class="form-select @error('categoria_id') is-invalid @enderror" id="categoria_id" name="categoria_id" required>
                                    <option value="" disabled>Seleccione una categoría</option>
                                    @foreach($categorias as $categoria)
                                        {{-- CAMBIO: Marcar la categoría actual del producto --}}
                                        <option value="{{ $categoria->id }}" {{ old('categoria_id', $producto->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categoria_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- ¡NUEVO CAMPO! Para la Imagen --}}
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Actualizar Imagen (Opcional)</label>
                            <input type="file" class="form-control @error('imagen') is-invalid @enderror" id="imagen" name="imagen" accept="image/png,image/jpeg,image/webp">
                            <small class="text-muted">Dejar en blanco para conservar la imagen actual.</small>
                            @error('imagen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ¡NUEVO! Vista previa de la imagen actual --}}
                        @if ($producto->imagen)
                            <div class="mb-3">
                                <label class="form-label d-block">Imagen Actual</label>
                                <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                            </div>
                        @endif

                        <hr>

                        <h5 class="text-muted mb-3">Inventario y Stock</h5>

                        <div class="row">
                            {{-- CAMBIO: Campo de Stock Actual (basado en tu captura) --}}
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Stock Actual</label>
                                {{-- CAMBIO: Cargar valor del inventario --}}
                                <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $producto->inventario->stock ?? 0) }}" min="0" required>
                                <small class="text-muted">Este campo se ajusta con Compras/Ventas.</small>
                                @error('stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- CAMBIO: Campo de Stock Mínimo (basado en tu captura) --}}
                            <div class="col-md-6 mb-3">
                                <label for="cantidad_minima" class="form-label">Stock Mínimo (Alerta)</label>
                                {{-- CAMBIO: Cargar valor del inventario --}}
                                <input type="number" class="form-control @error('cantidad_minima') is-invalid @enderror" id="cantidad_minima" name="cantidad_minima" value="{{ old('cantidad_minima', $producto->inventario->cantidad_minima ?? 5) }}" min="0" required>
                                @error('cantidad_minima')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('productos.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                            {{-- CAMBIO: Texto del botón --}}
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i> Actualizar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
