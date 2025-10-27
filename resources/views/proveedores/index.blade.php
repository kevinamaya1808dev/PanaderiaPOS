@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Proveedores</h2>
        
        {{-- Botón para CREAR (Solo visible si tiene permiso de 'alta') --}}
        @if (Auth::user()->hasPermissionTo('proveedores', 'alta'))
            <a href="{{ route('proveedores.create') }}" class="btn btn-primary">
                <i class="fas fa-truck me-2"></i> Registrar Nuevo Proveedor
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
                    <th>Nombre Contacto</th>
                    <th>Empresa</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th style="width: 150px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($proveedores as $proveedor)
                <tr>
                    <td>{{ $proveedor->id }}</td>
                    <td>{{ $proveedor->nombre }}</td>
                    <td>{{ $proveedor->empresa ?? 'N/A' }}</td>
                    <td>{{ $proveedor->telefono ?? 'N/A' }}</td>
                    <td>{{ $proveedor->correo ?? 'N/A' }}</td>
                    <td>
                        <div class="d-flex">
                            
                            {{-- Editar (Solo visible si tiene permiso de 'editar') --}}
                            @if (Auth::user()->hasPermissionTo('proveedores', 'editar'))
                                <a href="{{ route('proveedores.edit', $proveedor->id) }}" class="btn btn-sm btn-warning me-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif

                            {{-- Eliminar (Solo visible si tiene permiso de 'eliminar') --}}
                            @if (Auth::user()->hasPermissionTo('proveedores', 'eliminar'))
                                <form action="{{ route('proveedores.destroy', $proveedor->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar a {{ $proveedor->nombre }}?');">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="6">No hay proveedores registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection