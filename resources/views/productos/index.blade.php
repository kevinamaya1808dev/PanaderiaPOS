@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Productos</h2>
        
        {{-- Botón para CREAR PRODUCTO (Solo visible si tiene permiso de 'alta' en 'productos') --}}
        @if (Auth::user()->hasPermissionTo('productos', 'alta'))
            <a href="{{ route('productos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Crear Nuevo Producto
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
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th style="width: 150px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                {{-- Aquí iría el loop de productos, asumiendo $productos fue pasado desde el controlador --}}
                @forelse ($productos as $producto)
                <tr>
                    <td>{{ $producto->id }}</td>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->categoria->nombre ?? 'N/A' }}</td>
                    <td>${{ number_format($producto->precio, 2) }}</td>
                    <td>
                        {{ $producto->inventario->stock ?? '0' }}
                    </td>
                    <td>
                        {{-- Grupo de botones de acción --}}
                        <div class="d-flex">
                            
                            {{-- Editar (Solo visible si tiene permiso de 'editar' en 'productos') --}}
                            @if (Auth::user()->hasPermissionTo('productos', 'editar'))
                                <a href="{{ route('productos.edit', $producto->id) }}" class="btn btn-sm btn-warning me-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif

                            {{-- Eliminar (Solo visible si tiene permiso de 'eliminar' en 'productos') --}}
                            @if (Auth::user()->hasPermissionTo('productos', 'eliminar'))
                                <form action="{{ route('productos.destroy', $producto->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar el producto {{ $producto->nombre }}?');">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="6">No hay productos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection