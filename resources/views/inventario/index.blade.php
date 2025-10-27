@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Inventario y Stock</h2>
        <!-- NOTA: La funcionalidad de "Añadir Stock" iría aquí, protegida con 'alta' -->
    </div>

    {{-- Mensajes de Sesión --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="alert alert-warning">
        <i class="fas fa-info-circle me-2"></i> Esta vista muestra el stock actual y permite ajustar los límites (mínimo/máximo).
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
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
                        if ($stock < $minimo) {
                            $alerta_clase = 'bg-danger text-white';
                        } elseif ($stock < $minimo * 1.5) { // Advertencia cerca del mínimo
                            $alerta_clase = 'bg-warning';
                        }
                    @endphp
                    <tr>
                        <td>{{ $producto->id }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $producto->categoria->nombre ?? 'N/A' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $alerta_clase }} p-2">{{ $stock }}</span>
                        </td>
                        <td class="text-center">{{ $minimo }}</td>
                        <td class="text-center">{{ $inventario->cantidad_maxima ?? 0 }}</td>
                        <td class="text-center">
                            @if ($stock < $minimo)
                                <span class="badge bg-danger">STOCK BAJO</span>
                            @else
                                <span class="badge bg-success">Normal</span>
                            @endif
                        </td>
                        <td>
                            {{-- Botón Editar (Solo visible si tiene permiso de 'editar') --}}
                            @if (Auth::user()->hasPermissionTo('inventario', 'editar'))
                                <a href="{{ route('inventario.edit', $producto->id) }}" class="btn btn-sm btn-warning me-1" title="Ajustar Stock/Límites">
                                    <i class="fas fa-sliders-h"></i> Ajustar
                                </a>
                            @else
                                <span class="text-muted">Sin permiso</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No hay productos registrados en el inventario.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
