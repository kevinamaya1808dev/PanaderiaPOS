@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Registro de Compras</h2>
        
        {{-- Botón para CREAR (Solo visible si tiene permiso de 'alta') --}}
        @if (Auth::user()->hasPermissionTo('compras', 'alta'))
            <a href="{{ route('compras.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Registrar Nueva Compra
            </a>
        @endif
    </div>

    {{-- Mensajes de Sesión --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Proveedor</th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Método Pago</th>
                    <th>Total</th>
                    <th style="width: 150px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($compras as $compra)
                <tr>
                    <td>{{ $compra->id }}</td>
                    <td>{{ $compra->proveedor->nombre ?? 'N/A' }}</td>
                    <td>{{ $compra->created_at->format('Y-m-d') }}</td>
                    <td>{{ $compra->descripcion ?? 'Sin descripción' }}</td>
                    <td><span class="badge bg-secondary">{{ ucfirst($compra->metodo_pago) }}</span></td>
                    <td>${{ number_format($compra->total, 2) }}</td>
                    <td>
                        <div class="d-flex">
                            
                            {{-- Editar (Solo visible si tiene permiso de 'editar') --}}
                            @if (Auth::user()->hasPermissionTo('compras', 'editar'))
                                <a href="{{ route('compras.edit', $compra->id) }}" class="btn btn-sm btn-warning me-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif

                            {{-- Eliminar (Solo visible si tiene permiso de 'eliminar') --}}
                            @if (Auth::user()->hasPermissionTo('compras', 'eliminar'))
                                <form action="{{ route('compras.destroy', $compra->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este registro de compra?');">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="7">No hay registros de compras.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
