@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-header bg-warning text-dark">
            <h3 class="mb-0">Editar Empleado: {{ $empleado->name }}</h3>
        </div>
        <div class="card-body">

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            <form action="{{ route('empleados.update', $empleado->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Sección de Campos (omito por brevedad, asumo que son correctos) --}}
                {{-- Campos de Nombre, Email, Cargo, Password, Teléfono, Dirección... --}}

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Cancelar</a>
                    
                    {{-- Botón Actualizar (Solo visible si tiene permiso de 'editar') --}}
                    @if (Auth::user()->hasPermissionTo('usuarios', 'editar'))
                        <button type="submit" class="btn btn-success">Actualizar Empleado</button>
                    @else
                        <span class="text-danger">No tienes permiso para editar.</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection