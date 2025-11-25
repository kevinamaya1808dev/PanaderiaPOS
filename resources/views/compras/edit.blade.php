@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 600px;">
        
        {{-- Cabecera --}}
        <div class="card-header bg-dark text-white border-0">
            <h4 class="mb-0"><i class="fas fa-edit me-2"></i> Editar Registro #{{ $compra->id }}</h4>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('compras.update', $compra->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Determinamos el tipo una sola vez con PHP --}}
                @php
                    $esCompra = !is_null($compra->proveedor_id); 
                @endphp

                <h6 class="text-muted">Detalles del Registro</h6>
                <hr class="mt-1 mb-4 border-secondary">

                {{-- ========================================== --}}
                {{-- 1. INDICADOR DE TIPO (Bloqueado/Solo Lectura) --}}
                {{-- ========================================== --}}
                <div class="mb-4 text-center">
                    <p class="text-muted small mb-1">Tipo de movimiento</p>
                    <div class="btn-group w-100" role="group">

                        {{-- Botón Compra (Visualmente activo si es compra, pero deshabilitado) --}}
                        <input type="radio" class="btn-check" disabled {{ $esCompra ? 'checked' : '' }}>
                        <label class="btn {{ $esCompra ? 'btn-panaderia-active' : 'btn-outline-secondary' }} opacity-100" style="cursor: default;">
                            <i class="fas fa-truck me-2"></i>Compra a Proveedor
                        </label>

                        {{-- Botón Gasto (Visualmente activo si es gasto, pero deshabilitado) --}}
                        <input type="radio" class="btn-check" disabled {{ !$esCompra ? 'checked' : '' }}>
                        <label class="btn {{ !$esCompra ? 'btn-panaderia-active' : 'btn-outline-secondary' }} opacity-100" style="cursor: default;">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Gasto General
                        </label>

                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- 2. CAMPOS ESPECÍFICOS (Renderizados por PHP) --}}
                {{-- ========================================== --}}

                @if($esCompra)
                    {{-- SI ES COMPRA: Solo mostramos Proveedor --}}
                    <div class="mb-3">
                        <label for="proveedor_id" class="form-label fw-bold" style="color: #c58d4c;">Proveedor</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white" style="color: #c58d4c;"><i class="fas fa-truck fa-fw"></i></span>
                            <select class="form-select @error('proveedor_id') is-invalid @enderror" id="proveedor_id" name="proveedor_id">
                                @foreach ($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}" 
                                        {{ (old('proveedor_id', $compra->proveedor_id) == $proveedor->id) ? 'selected' : '' }}>
                                        {{ $proveedor->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('proveedor_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        
                        {{-- input oculto para limpiar concepto por seguridad (opcional, depende de tu controlador) --}}
                        <input type="hidden" name="concepto" value="">
                    </div>
                @else
                    {{-- SI ES GASTO: Solo mostramos Concepto --}}
                    <div class="mb-3">
                        <label for="concepto" class="form-label fw-bold" style="color: #c58d4c;">Concepto del Gasto</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white" style="color: #c58d4c;"><i class="fas fa-pen fa-fw"></i></span>
                            <input type="text" class="form-control @error('concepto') is-invalid @enderror" 
                                   id="concepto" name="concepto" 
                                   value="{{ old('concepto', $compra->concepto) }}" 
                                   required>
                        </div>
                        @error('concepto') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                        {{-- input oculto para asegurar que no se envíe proveedor --}}
                        <input type="hidden" name="proveedor_id" value="">
                    </div>
                @endif

                {{-- ========================================== --}}
                {{-- 3. CAMPOS COMUNES --}}
                {{-- ========================================== --}}

                {{-- Total --}}
                <div class="mb-3">
                    <label for="total" class="form-label">Total ($)</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0.01" class="form-control fw-bold @error('total') is-invalid @enderror" 
                               id="total" name="total" 
                               value="{{ old('total', $compra->total) }}" required>
                    </div>
                    @error('total') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Método de Pago --}}
                <div class="mb-3">
                    <label for="metodo_pago" class="form-label">Método de Pago</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-money-bill-wave fa-fw"></i></span>
                        <select class="form-select @error('metodo_pago') is-invalid @enderror" id="metodo_pago" name="metodo_pago" required>
                            @foreach(['efectivo', 'tarjeta', 'transferencia'] as $metodo)
                                <option value="{{ $metodo }}" {{ old('metodo_pago', $compra->metodo_pago) == $metodo ? 'selected' : '' }}>
                                    {{ ucfirst($metodo) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Responsable --}}
                <div class="mb-3">
                    <label class="form-label fw-bold text-primary">Registrado por:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                        <input type="text" 
                               class="form-control bg-light" 
                               value="{{ $compra->user->name ?? 'Usuario desconocido' }}" 
                               readonly>
                    </div>
                </div>

                {{-- Descripción --}}
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción o Notas <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                              id="descripcion" name="descripcion" rows="2" required>{{ old('descripcion', $compra->descripcion) }}</textarea>
                    @error('descripcion') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Botones --}}
                <div class="d-flex justify-content-between mt-4 pt-2 border-top">
                    <a href="{{ route('compras.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    
                    @if (Auth::user()->hasPermissionTo('compras', 'editar'))
                        <button type="submit" class="btn btn-warning px-4 text-white">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar Registro
                        </button>
                    @endif
                </div>

            </form>
        </div>
    </div>
</div>

<style>
    /* Estilo para el botón ACTIVO y BLOQUEADO */
    .btn-panaderia-active {
        background-color: #c58d4c !important;
        color: white !important;
        border-color: #c58d4c !important;
        font-weight: bold;
    }
</style>
@endsection