@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">
        Gestión de Permisos para el Cargo: <span class="badge bg-primary">{{ $cargo->nombre }}</span>
    </h2>

    <div class="alert alert-info">
        Define qué acciones de CRUD (Mostrar, Alta, Detalle, Editar, Eliminar) este cargo puede realizar en cada módulo del sistema.
    </div>

    {{-- Mostrar mensajes de sesión --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    {{-- Formulario que enviará toda la matriz al PermisoController@update --}}
    <form action="{{ route('cargos.permisos.update', $cargo->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th style="width: 25%;">Módulo</th>
                        <th class="text-center">Mostrar (Ver en menú)</th>
                        <th class="text-center">Detalle (Ver registro)</th>
                        <th class="text-center">Alta (Crear)</th>
                        <th class="text-center">Editar (Modificar)</th>
                        <th class="text-center">Eliminar (Borrar)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Iterar sobre todos los módulos disponibles (Controlador pasa $modulos) --}}
                    @foreach ($modulos as $modulo)
                    {{-- Obtener el permiso existente para este módulo y cargo --}}
                    @php
                        // $permisosActuales es un array indexado por modulo_id
                        $permiso = $permisosActuales->get($modulo->id);
                    @endphp

                    <tr>
                        <td class="fw-bold">{{ ucfirst($modulo->nombre) }}</td>

                        {{-- Definición de Acciones (Las claves deben coincidir con las columnas de la tabla 'permisos') --}}
                        @foreach (['mostrar', 'detalle', 'alta', 'editar', 'eliminar'] as $accion)
                            <td class="text-center">
                                <div class="form-check">
                                    {{-- El nombre del input es crucial: nombre_accion[modulo_id] --}}
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        name="{{ $accion }}[{{ $modulo->id }}]" 
                                        value="1" 
                                        id="check-{{ $modulo->id }}-{{ $accion }}"
                                        {{-- Marcar si el permiso existe y la acción está activa (1) --}}
                                        {{ ($permiso && $permiso->$accion) ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label visually-hidden" for="check-{{ $modulo->id }}-{{ $accion }}">{{ ucfirst($accion) }}</label>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 pb-5 d-flex justify-content-between">
            <a href="{{ route('cargos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Cargos
            </a>
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save"></i> Guardar Permisos
            </button>
        </div>
    </form>
</div>
@endsection