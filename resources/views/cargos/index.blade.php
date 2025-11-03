@extends('layouts.app')

@section('content')
<div class="container">
    


    {{-- Card principal para la gestión de cargos --}}
    <div class="card shadow-sm border-0">
        
        {{-- Card Header: Título y Botón de Crear --}}
        <div class="card-header bg-white border-0 border-bottom d-flex justify-content-between align-items-center">
            
            <h4 class="mb-0">Gestión de Cargos (Roles)</h4>
            
            @if (Auth::user()->hasPermissionTo('cargos', 'alta'))
                <a href="{{ route('cargos.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i> Crear Nuevo Cargo
                </a>
            @endif
        </div>

        {{-- Card Body: Contiene la tabla --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Nombre del Cargo</th>
                            <th style="width: 350px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cargos as $cargo)
                        <tr>
                            <td>{{ $cargo->id }}</td>
                            <td>{{ $cargo->nombre }}</td>
                            <td>
                                {{-- Botón de Permisos --}}
                                @if (Auth::user()->hasPermissionTo('cargos', 'editar'))
                                    <a href="{{ route('cargos.permisos.index', $cargo->id) }}" class="btn btn-sm btn-info me-1" title="Gestionar Permisos">
                                        <i class="fas fa-key me-1"></i> Permisos
                                    </a>
                                    {{-- Botón de Editar --}}
                                    <a href="{{ route('cargos.edit', $cargo->id) }}" class="btn btn-sm btn-warning me-1" title="Editar Cargo">
                                        <i class="fas fa-edit me-1"></i> Editar
                                    </a>
                                @endif

                                {{-- ====================================================== --}}
                                {{-- CAMBIO: Botón de Eliminar (ahora abre un modal)       --}}
                                {{-- ====================================================== --}}
                                @if (Auth::user()->hasPermissionTo('cargos', 'eliminar') && $cargo->id !== 1)
                                    {{-- Este botón ahora abre el modal --}}
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            title="Eliminar"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#confirmDeleteModal"
                                            data-cargo-nombre="{{ $cargo->nombre }}"
                                            data-form-action="{{ route('cargos.destroy', $cargo->id) }}">
                                        <i class="fas fa-trash me-1"></i> Eliminar
                                    </button>
                                @endif
                                {{-- ====================================================== --}}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted p-4">
                                No se encontraron cargos registrados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div> {{-- Fin card-body --}}

    </div> {{-- Fin card --}}
</div>


{{-- ========================================================== --}}
{{-- NUEVO: Modal de Confirmación de Eliminación                --}}
{{-- ========================================================== --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar el cargo: 
                <br>
                <strong id="modalCargoNombre" class="fs-5"></strong>?
                <br><br>
                <small class="text-muted">Esta acción no se puede deshacer.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                
                {{-- Este formulario se enviará al eliminar --}}
                <form id="deleteForm" method="POST" action=""> {{-- La 'action' la pondrá el JavaScript --}}
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Sí, Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


{{-- ========================================================== --}}
{{-- NUEVO: Script para pasar datos al modal                   --}}
{{-- ========================================================== --}}
@push('scripts')
<script>
    // Espera a que el DOM esté cargado
    document.addEventListener('DOMContentLoaded', function () {
        
        // Selecciona el modal
        var confirmDeleteModal = document.getElementById('confirmDeleteModal');
        
        // Escucha el evento 'show.bs.modal' (cuando el modal está a punto de mostrarse)
        confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
            
            // Obtiene el botón que disparó el modal
            var button = event.relatedTarget;
            
            // Extrae los datos del botón
            var cargoNombre = button.getAttribute('data-cargo-nombre');
            var formAction = button.getAttribute('data-form-action');
            
            // Busca los elementos dentro del modal
            var modalBodyNombre = confirmDeleteModal.querySelector('#modalCargoNombre');
            var deleteForm = confirmDeleteModal.querySelector('#deleteForm');
            
            // Actualiza el contenido del modal
            modalBodyNombre.textContent = cargoNombre;
            deleteForm.setAttribute('action', formAction);
        });
    });
</script>
@endpush