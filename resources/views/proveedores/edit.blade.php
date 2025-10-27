@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-header bg-warning text-dark">
            <h3 class="mb-0">Editar Proveedor: {{ $proveedore->nombre }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('proveedores.update', $proveedore->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Nombre de Contacto --}}
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre del Contacto</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre', $proveedore->nombre) }}" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                {{-- Nombre de la Empresa --}}
                <div class="mb-3">
                    <label for="empresa" class="form-label">Nombre de la Empresa</label>
                    <input type="text" class="form-control @error('empresa') is-invalid @enderror" id="empresa" name="empresa" value="{{ old('empresa', $proveedore->empresa) }}">
                    @error('empresa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Teléfono --}}
                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control @error('telefono') is-invalid @enderror" id="telefono" name="telefono" value="{{ old('telefono', $proveedore->telefono) }}">
                    @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Correo --}}
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control @error('correo') is-invalid @enderror" id="correo" name="correo" value="{{ old('correo', $proveedore->correo) }}">
                    @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">Cancelar</a>
                    
                    {{-- Botón Actualizar (Protegido en la ruta y en la vista por consistencia) --}}
                    @if (Auth::user()->hasPermissionTo('proveedores', 'editar'))
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-sync"></i> Actualizar Proveedor
                        </button>
                    @else
                        <span class="text-danger">No tienes permiso para actualizar este registro.</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection