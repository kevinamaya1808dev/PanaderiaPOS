@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Cargos (Roles)</h2>
        
        {{-- Botón para CREAR CARGO (Solo visible si tiene permiso de 'alta') --}}
        @if (Auth::user()->hasPermissionTo('cargos', 'alta'))
            <a href="{{ route('cargos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Crear Nuevo Cargo
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
                    <th>Nombre del Cargo</th>
                    <th style="width: 300px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cargos as $cargo)
                <tr>
                    <td>{{ $cargo->id }}</td>
                    <td>{{ $cargo->nombre }}</td>
                    <td>
                        {{-- Enlace CLAVE a la matriz de permisos (Requiere permiso de 'editar') --}}
                        @if (Auth::user()->hasPermissionTo('cargos', 'editar'))
                            <a href="{{ route('cargos.permisos.index', $cargo->id) }}" class="btn btn-sm btn-info me-1" title="Gestionar Permisos">
                                <i class="fas fa-key"></i> Permisos
                            </a>

                            {{-- Enlace para editar el nombre --}}
                            <a href="{{ route('cargos.edit', $cargo->id) }}" class="btn btn-sm btn-warning me-1" title="Editar Cargo">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endif

                        {{-- Formulario para eliminar (Requiere permiso de 'eliminar' Y no es Super Admin) --}}
                        @if (Auth::user()->hasPermissionTo('cargos', 'eliminar') && $cargo->id !== 1)
                            <form action="{{ route('cargos.destroy', $cargo->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar el cargo {{ $cargo->nombre }}?');">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection