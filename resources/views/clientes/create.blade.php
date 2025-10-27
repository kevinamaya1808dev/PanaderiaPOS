@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm mx-auto" style="max-width: 500px;">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Registrar Nuevo Cliente</h3>
        </div>
        <div class="card-body">

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            <form action="{{ route('clientes.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="Nombre" class="form-label">Nombre del Cliente</label>
                    <input type="text" class="form-control @error('Nombre') is-invalid @enderror" id="Nombre" name="Nombre" value="{{ old('Nombre') }}" required>
                    @error('Nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
                    
                    {{-- Botón Guardar (Solo visible si tiene permiso de 'alta') --}}
                    @if (Auth::user()->hasPermissionTo('clientes', 'alta'))
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Cliente
                        </button>
                    @else
                        <span class="text-danger">No tienes permiso para registrar clientes.</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection