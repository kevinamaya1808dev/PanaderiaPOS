@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Categorías</h2>
        
        {{-- Botón para CREAR CATEGORÍA (Solo visible si tiene permiso de 'alta' en 'productos') --}}
        @if (Auth::user()->hasPermissionTo('productos', 'alta'))
            <a href="{{ route('categorias.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Crear Nueva Categoría
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
                    <th style="width: 150px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categorias as $categoria)
                <tr>
                    <td>{{ $categoria->id }}</td>
                    <td>{{ $categoria->nombre }}</td>
                    <td>
                        <div class="d-flex">
                            
                            {{-- Editar --}}
                            @if (Auth::user()->hasPermissionTo('productos', 'editar'))
                                <a href="{{ route('categorias.edit', $categoria->id) }}" class="btn btn-sm btn-warning me-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif

                            {{-- Eliminar --}}
                            @if (Auth::user()->hasPermissionTo('productos', 'eliminar'))
                                <form action="{{ route('categorias.destroy', $categoria->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar la categoría {{ $categoria->nombre }}?');">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection