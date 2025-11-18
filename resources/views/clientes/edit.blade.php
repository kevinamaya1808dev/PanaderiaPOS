@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Card centrado --}}
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 500px;">
        
        {{-- CAMBIO: Cabecera oscura y h4 para el título --}}
        <div class="card-header bg-dark text-white border-0">
            <h4 class="mb-0">Editar Expendio: {{ $cliente->Nombre }}</h4>
        </div>

        {{-- CAMBIO: card-body con p-4 --}}
        <div class="card-body p-4">
            <form action="{{ route('clientes.update', $cliente->idCli) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="Nombre" class="form-label">Nombre del Expendio</label>
                    {{-- CAMBIO: Input group con ícono --}}
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user fa-fw"></i></span>
                        <input type="text" class="form-control @error('Nombre') is-invalid @enderror" id="Nombre" name="Nombre" value="{{ old('Nombre', $cliente->Nombre) }}" required>
                    </div>
                    @error('Nombre') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                
                {{-- Botones --}}
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                        {{-- CAMBIO: Ícono añadido --}}
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    
                    @if (Auth::user()->hasPermissionTo('clientes', 'editar'))
                        <button type="submit" class="btn btn-success">
                            {{-- CAMBIO: Ícono estandarizado --}}
                            <i class="fas fa-sync me-1"></i> Actualizar Expendio
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