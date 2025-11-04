@extends('layouts.app')

@section('content')
<div class="container">
    
    {{-- Barra de Título y Búsqueda --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Inventario y Stock</h2>
        
        {{-- Barra de Búsqueda Grande con Lupa --}}
        <div class="input-group input-group-lg" style="width: 350px;">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="search" id="inventory-search" class="form-control" placeholder="Buscar producto">
        </div>
    </div>

    {{-- Alerta informativa --}}
    <div class="alert alert-warning">
        <i class="fas fa-info-circle me-2"></i> Esta vista muestra el stock actual y permite ajustar los límites (mínimo/máximo).
    </div>

    {{-- Tabla --}}
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Producto</th>
                    <th style="width: 100px;">Imagen</th>
                    <th>Categoría</th>
                    <th class="text-center">Stock Actual</th>
                    <th class="text-center">Mínimo</th>
                    <th class="text-center">Máximo</th>
                    <th class="text-center" style="width: 150px;">Estado</th>
                    <th style="width: 150px;">Ajustar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productos as $producto)
                    @php
                        $inventario = $producto->inventario;
                        $stock = $inventario->stock ?? 0;
                        $minimo = $inventario->cantidad_minima ?? 0;
                        $alerta_clase = '';
                        
                        if ($stock <= 0) {
                            $alerta_clase = 'bg-dark text-white'; // Agotado
                        } elseif ($stock < $minimo) {
                            $alerta_clase = 'bg-danger text-white'; // Stock bajo
                        } elseif ($stock < $minimo * 1.5) { 
                            $alerta_clase = 'bg-warning'; // Advertencia
                        } else {
                            $alerta_clase = 'bg-light text-dark border'; // Normal
                        }
                    @endphp
                    <tr>
                        <td>{{ $producto->id }}</td>
                        <td>{{ $producto->nombre }}</td>
                                                <td>
                            <img src="{{ $producto->imagen ? asset('storage/' . $producto->imagen) : 'https://placehold.co/100x100/EBF5FB/333333?text=N/A' }}" 
                                 alt="{{ $producto->nombre }}" 
                                 class="img-fluid rounded"
                                 style="width: 60px; height: 60px; object-fit: cover;">
                        </td>
                        <td>{{ $producto->categoria->nombre ?? 'N/A' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $alerta_clase }} fs-6">{{ $stock }}</span>
                        </td>
                        <td class="text-center">{{ $minimo }}</td>
                        <td class="text-center">{{ $inventario->cantidad_maxima ?? 'N/A' }}</td>
                        <td class="text-center">
                            @if ($stock <= 0)
                                <span class="badge bg-dark">AGOTADO</span>
                            @elseif ($stock < $minimo)
                                <span class="badge bg-danger">STOCK BAJO</span>
                            @else
                                <span class="badge bg-success">NORMAL</span>
                            @endif
                        </td>
                        <td>
                            @if (Auth::user()->hasPermissionTo('inventario', 'editar'))
                                <a href="{{ route('inventario.edit', $producto->id) }}" class="btn btn-sm btn-warning me-1" title="Ajustar Stock/Límites">
                                    <i class="fas fa-sliders-h me-1"></i> Ajustar
                                </a>
                            @else
                                <span class="text-muted">Sin permiso</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted p-4">
                            No hay productos registrados en el inventario.
                        </td>
                    </tr>
                @endforelse {{-- Esta es la línea que faltaba --}}
            </tbody>
        </table>
    </div>
</div>
@endsection


{{-- Script para la barra de búsqueda --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('inventory-search');
        
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const filter = searchInput.value.toLowerCase().trim();
                const rows = document.querySelectorAll('table tbody tr');
                
                rows.forEach(row => {
                    const productNameCell = row.querySelector('td:nth-child(3)'); // Columna de Producto
                    
                    if (productNameCell) {
                        const text = productNameCell.textContent || productNameCell.innerText;
                        if (text.toLowerCase().indexOf(filter) > -1) {
                            row.style.display = ''; 
                        } else {
                            row.style.display = 'none'; 
                        }
                    }
                });
            });
        }
    });
</script>
@endpush