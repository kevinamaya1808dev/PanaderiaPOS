@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 600px;">
        
        {{-- Cabecera --}}
        <div class="card-header bg-dark text-white border-0">
            <h4 class="mb-0"><i class="fas fa-exchange-alt me-2"></i> Registrar Compra</h4>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('compras.store') }}" method="POST" id="form-movimientos">
                @csrf

                <h6 class="text-muted">Detalles del Registro</h6>
                <hr class="mt-1 mb-4 border-secondary">

                {{-- ========================================== --}}
                {{-- 1. BOTONES SWITCH (Color #c58d4c) --}}
                {{-- ========================================== --}}
                <div class="mb-4 text-center">
                    <div class="btn-group w-100" role="group">
                        {{-- Opción Compra --}}
                        <input type="radio" class="btn-check" name="tipo_movimiento" id="tipo_compra" value="compra" checked autocomplete="off">
                        <label class="btn btn-outline-panaderia" for="tipo_compra">
                            <i class="fas fa-truck me-2"></i>Compra a Proveedor
                        </label>
                      
                        {{-- Opción Gasto --}}
                        <input type="radio" class="btn-check" name="tipo_movimiento" id="tipo_gasto" value="gasto" autocomplete="off">
                        <label class="btn btn-outline-panaderia" for="tipo_gasto">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Gasto General
                        </label>
                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- 2. ÁREA DINÁMICA --}}
                {{-- ========================================== --}}

                {{-- OPCIÓN A: Proveedor --}}
                <div class="mb-3" id="div-proveedor">
                    <label for="proveedor_id" class="form-label fw-bold" style="color: #c58d4c;">Proveedor</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="color: #c58d4c;"><i class="fas fa-truck fa-fw"></i></span>
                        <select class="form-select" id="proveedor_id" name="proveedor_id">
                            <option value="" selected disabled>Seleccione un Proveedor</option>
                            @foreach ($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- OPCIÓN B: Gasto General --}}
                <div class="mb-3" id="div-gasto" style="display: none;">
                    <label for="nombre_gasto" class="form-label fw-bold" style="color: #c58d4c;">Concepto del Gasto</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="color: #c58d4c;"><i class="fas fa-pen fa-fw"></i></span>
                        <input type="text" class="form-control" id="nombre_gasto" name="nombre_gasto" placeholder="Ej: Pago de Luz, Internet..." disabled>
                    </div>
                </div>
                
                {{-- ========================================== --}}
                {{-- 3. CAMPOS COMUNES --}}
                {{-- ========================================== --}}

                {{-- Total --}}
                <div class="mb-3">
                    <label for="total" class="form-label">Total ($)</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0.01" class="form-control fw-bold" id="total" name="total" placeholder="0.00" required>
                    </div>
                </div>

                {{-- Método de Pago (SOLO EFECTIVO) --}}
                <div class="mb-3">
                    <label for="metodo_pago" class="form-label">Método de Pago</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-money-bill-wave fa-fw"></i></span>
                        <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                            <option value="efectivo" selected>Efectivo</option>
                        </select>
                    </div>
                </div>

                {{-- Descripción (OBLIGATORIA) --}}
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción o Notas <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="2" placeholder="Detalles obligatorios..." required></textarea>
                </div>

                {{-- Botones de Acción --}}
                <div class="d-flex justify-content-between mt-4 pt-2 border-top">
                    <a href="{{ route('compras.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    {{-- Botón VERDE (Original) --}}
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-save me-1"></i> Guardar Registro
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ESTILOS PERSONALIZADOS PARA EL COLOR #c58d4c (Solo Switch) --}}
<style>
    .btn-outline-panaderia {
        color: #c58d4c;
        border-color: #c58d4c;
    }
    .btn-outline-panaderia:hover {
        background-color: #c58d4c;
        color: white;
    }
    .btn-check:checked + .btn-outline-panaderia {
        background-color: #c58d4c;
        color: white;
        border-color: #c58d4c;
    }
</style>

{{-- SCRIPT --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const radioCompra = document.getElementById('tipo_compra');
        const radioGasto = document.getElementById('tipo_gasto');
        
        const divProveedor = document.getElementById('div-proveedor');
        const divGasto = document.getElementById('div-gasto');
        
        const inputProveedor = document.getElementById('proveedor_id');
        const inputGasto = document.getElementById('nombre_gasto');

        function toggleCampos() {
            if (radioCompra.checked) {
                divProveedor.style.display = 'block';
                divGasto.style.display = 'none';
                inputProveedor.disabled = false;
                inputProveedor.required = true;
                inputGasto.disabled = true;
                inputGasto.required = false;
                inputGasto.value = '';
            } else {
                divProveedor.style.display = 'none';
                divGasto.style.display = 'block';
                inputProveedor.disabled = true;
                inputProveedor.required = false;
                inputProveedor.value = '';
                inputGasto.disabled = false;
                inputGasto.required = true;
                inputGasto.focus();
            }
        }

        radioCompra.addEventListener('change', toggleCampos);
        radioGasto.addEventListener('change', toggleCampos);
    });
</script>
@endsection