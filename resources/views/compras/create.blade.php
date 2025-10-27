@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Registrar Nueva Compra</h3>
        </div>
        <div class="card-body">

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            <form action="{{ route('compras.store') }}" method="POST">
                @csrf

                {{-- Proveedor --}}
                <div class="mb-3">
                    <label for="proveedor_id" class="form-label">Proveedor</label>
                    <select class="form-select @error('proveedor_id') is-invalid @enderror" id="proveedor_id" name="proveedor_id" required>
                        <option value="">Seleccione un Proveedor</option>
                        @foreach ($proveedores as $proveedor)
                            <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                {{ $proveedor->nombre }} ({{ $proveedor->empresa }})
                            </option>
                        @endforeach
                    </select>
                    @error('proveedor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                {{-- Total --}}
                <div class="mb-3">
                    <label for="total" class="form-label">Total de la Compra</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0.01" class="form-control @error('total') is-invalid @enderror" id="total" name="total" value="{{ old('total') }}" required>
                        @error('total') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Método de Pago --}}
                <div class="mb-3">
                    <label for="metodo_pago" class="form-label">Método de Pago</label>
                    <select class="form-select @error('metodo_pago') is-invalid @enderror" id="metodo_pago" name="metodo_pago" required>
                        <option value="">Seleccione...</option>
                        @foreach (['efectivo', 'tarjeta', 'credito', 'transferencia'] as $metodo)
                            <option value="{{ $metodo }}" {{ old('metodo_pago') == $metodo ? 'selected' : '' }}>
                                {{ ucfirst($metodo) }}
                            </option>
                        @endforeach
                    </select>
                    @error('metodo_pago') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Descripción --}}
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción o Concepto (Opcional)</label>
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion">{{ old('descripcion') }}</textarea>
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('compras.index') }}" class="btn btn-secondary">Cancelar</a>
                    
                    {{-- Botón Guardar (Protegido en la ruta) --}}
                    @if (Auth::user()->hasPermissionTo('compras', 'alta'))
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Compra
                        </button>
                    @else
                        <span class="text-danger">No tienes permiso para registrar compras.</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
