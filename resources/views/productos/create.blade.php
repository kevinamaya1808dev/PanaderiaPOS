@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm mx-auto" style="max-width: 700px;">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Crear Nuevo Producto</h3>
        </div>
        <div class="card-body">

            <form action="{{ route('productos.store') }}" method="POST">
                @csrf

                {{-- Sección de Producto --}}
                <h5 class="mt-3 mb-3 text-primary">Detalles del Producto</h5>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre del Producto</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="categoria_id" class="form-label">Categoría</label>
                        <select class="form-select @error('categoria_id') is-invalid @enderror" id="categoria_id" name="categoria_id" required>
                            <option value="">Seleccione Categoría</option>
                            {{-- La variable $categorias debe ser pasada desde el ProductoController@create --}}
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('categoria_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="precio" class="form-label">Precio de Venta ($)</label>
                        <input type="number" step="0.01" min="0.01" class="form-control @error('precio') is-invalid @enderror" id="precio" name="precio" value="{{ old('precio') }}" required>
                        @error('precio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion">{{ old('descripcion') }}</textarea>
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <hr class="my-4">

                {{-- Sección de Inventario Inicial --}}
                <h5 class="mb-3 text-success">Inventario y Stock Inicial</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="stock_inicial" class="form-label">Stock Inicial</label>
                        <input type="number" min="0" class="form-control @error('stock_inicial') is-invalid @enderror" id="stock_inicial" name="stock_inicial" value="{{ old('stock_inicial', 0) }}" required>
                        @error('stock_inicial') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="cantidad_minima" class="form-label">Stock Mínimo (Alerta)</label>
                        <input type="number" min="0" class="form-control @error('cantidad_minima') is-invalid @enderror" id="cantidad_minima" name="cantidad_minima" value="{{ old('cantidad_minima', 5) }}" required>
                        @error('cantidad_minima') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Producto y Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection