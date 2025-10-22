@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Empleados</h2>
        
        {{-- Botón para CREAR (Solo visible si tiene permiso de 'alta') --}}
        @if (Auth::user()->hasPermissionTo('usuarios', 'alta'))
            <a href="{{ route('empleados.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Crear Nuevo Empleado
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
                    <th>Email</th>
                    <th>Cargo</th>
                    <th>Teléfono</th>
                    <th style="width: 150px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        {{ $user->cargo ? $user->cargo->nombre : 'Sin Cargo' }}
                    </td>
                    <td>
                        {{ $user->empleado ? $user->empleado->telefono : 'N/A' }}
                    </td>
                    <td>
                        {{-- Botones de Acción (Solo visibles si tiene el permiso) --}}
                        <div class="d-flex">
                            
                            {{-- Editar --}}
                            @if (Auth::user()->hasPermissionTo('usuarios', 'editar'))
                                <a href="{{ route('empleados.edit', $user->id) }}" class="btn btn-sm btn-warning me-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            
                            {{-- Eliminar --}}
                            @if (Auth::user()->hasPermissionTo('usuarios', 'eliminar') && $user->id !== 1 && $user->id !== Auth::id())
                                <form action="{{ route('empleados.destroy', $user->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar a {{ $user->name }}?');">
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