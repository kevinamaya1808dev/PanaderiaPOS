@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-plus-circle me-2"></i> Crear Nuevo Producto
                </div>
                <div class="card-body">
                    
                    {{-- Formulario con enctype para subir archivos --}}
                    <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <h5 class="text-muted mb-3">Detalles del Producto</h5>

                        {{-- Campo Nombre --}}
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Producto</label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Campo Descripción --}}
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                            <textarea class="form-control" id="descripcion" name="descripcion">{{ old('descripcion') }}</textarea>
                        </div>

                        <div class="row">
                            {{-- Campo Precio --}}
                            <div class="col-md-6 mb-3">
                                <label for="precio" class="form-label">Precio de Venta</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('precio') is-invalid @enderror" id="precio" name="precio" value="{{ old('precio') }}" step="0.01" min="0" required>
                                </div>
                                @error('precio')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Campo Categoría --}}
                            <div class="col-md-6 mb-3">
                                <label for="categoria_id" class="form-label">Categoría</label>
                                <select class="form-select @error('categoria_id') is-invalid @enderror" id="categoria_id" name="categoria_id" required>
                                    <option value="" disabled selected>Seleccione una categoría</option>
                                    
                                    {{-- ¡CORRECCIÓN AQUÍ! Bucle descomentado --}}
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                    
                                </select>
                                @error('categoria_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Campo Imagen --}}
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Imagen del Producto (Opcional)</label>
                            <input type="file" class="form-control @error('imagen') is-invalid @enderror" id="imagen" name="imagen" accept="image/png,image/jpeg,image/webp">
                            @error('imagen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <h5 class="text-muted mb-3">Inventario y Stock Inicial</h5>

                        {{-- ¡CAMPOS AÑADIDOS DE VUELTA! --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="stock_inicial" class="form-label">Stock Inicial</label>
                                <input type="number" class="form-control @error('stock_inicial') is-invalid @enderror" id="stock_inicial" name="stock_inicial" value="{{ old('stock_inicial', 0) }}" min="0" required>
                                @error('stock_inicial')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cantidad_minima" class="form-label">Stock Mínimo (Alerta)</label>
                                <input type="number" class="form-control @error('cantidad_minima') is-invalid @enderror" id="cantidad_minima" name="cantidad_minima" value="{{ old('cantidad_minima', 5) }}" min="0" required>
                                @error('cantidad_minima')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('productos.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Guardar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

