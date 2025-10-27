@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm mx-auto" style="max-width: 500px;">
        <div class="card-header bg-warning text-dark">
            <h3 class="mb-0">Editar Cliente: {{ $cliente->Nombre }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('clientes.update', $cliente->idCli) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="Nombre" class="form-label">Nombre del Cliente</label>
                    <input type="text" class="form-control @error('Nombre') is-invalid @enderror" id="Nombre" name="Nombre" value="{{ old('Nombre', $cliente->Nombre) }}" required>
                    @error('Nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
                    
                    {{-- Botón Actualizar (Solo visible si tiene permiso de 'editar') --}}
                    @if (Auth::user()->hasPermissionTo('clientes', 'editar'))
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-sync"></i> Actualizar Cliente
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