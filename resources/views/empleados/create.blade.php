@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Crear Nuevo Empleado</h3>
        </div>
        <div class="card-body">

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            <form action="{{ route('empleados.store') }}" method="POST">
                @csrf

                {{-- Sección de Campos (omito por brevedad, asumo que son correctos) --}}
                <h5 class="mt-3 mb-3 text-primary">Datos de Acceso</h5>

                {{-- Campos de Nombre, Email, Cargo, Password... --}}
                
                <h5 class="mt-4 mb-3 text-primary">Información Adicional</h5>

                {{-- Campos de Teléfono, Dirección... --}}
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Cancelar</a>
                    
                    {{-- Botón Guardar (Solo visible si tiene permiso de 'alta') --}}
                    @if (Auth::user()->hasPermissionTo('usuarios', 'alta'))
                        <button type="submit" class="btn btn-success">Guardar Empleado</button>
                    @else
                        <span class="text-danger">No tienes permiso para guardar.</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection