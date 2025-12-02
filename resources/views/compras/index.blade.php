@extends('layouts.app')

@section('content')
<div class="container">
    
    {{-- Barra de Título y Botón Crear --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-shopping-cart text-secondary me-2"></i> Registro de Compras
        </h2>
        
        @if (Auth::user()->hasPermissionTo('compras', 'alta'))
            <a href="{{ route('compras.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Registrar Nueva Compra
            </a>
        @endif
    </div>

    {{-- Tabla libre en el contenedor (Sin Card) --}}
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            {{-- Encabezado Oscuro --}}
            <thead class="table-dark">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Proveedor / Concepto</th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Método Pago</th>
                    <th>Responsable</th>
                    <th>Total</th>
                    {{-- Ancho ajustado para botones con texto --}}
                    <th class="text-end pe-4" style="width: 220px;">Acciones</th> 
                </tr>
            </thead>
            <tbody>
                @forelse ($compras as $compra)
                <tr>
                    <td class="ps-4 fw-bold">{{ $compra->id }}</td>

                    {{-- PROVEEDOR / GASTO GENERAL --}}
                    <td>
                        @if($compra->proveedor)
                            <span class="fw-bold">{{ $compra->proveedor->nombre }}</span>
                        @else
                            <span class="text-danger fw-bold">Gasto General</span>
                            <br>
                            <small class="text-muted">
                                {{ $compra->concepto ?? 'Sin concepto' }}
                            </small>
                        @endif
                    </td>

                    <td>
                        {{ $compra->created_at->format('d/m/Y') }}
                    </td>

                    <td>
                        {{ Str::limit($compra->descripcion ?? '-', 30) }}
                    </td>

                    <td>
                        <span class="badge {{ $compra->metodo_pago == 'efectivo' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($compra->metodo_pago) }}
                        </span>
                    </td>

                    {{-- RESPONSABLE --}}
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                 style="width: 30px; height: 30px; font-size: 0.8rem;">
                                {{ strtoupper(substr($compra->user->name ?? 'S', 0, 1)) }}
                            </div>
                            <span class="text-dark">{{ $compra->user->name ?? 'Sistema' }}</span>
                        </div>
                    </td>

                    <td class="fw-bold text-danger">${{ number_format($compra->total, 2) }}</td>
                    
                    <td class="text-end pe-4">
                        @if (Auth::user()->hasPermissionTo('compras', 'editar'))
                            {{-- Botón Editar Sólido (Amarillo) --}}
                            <a href="{{ route('compras.edit', $compra->id) }}" class="btn btn-sm btn-warning me-1" title="Editar">
                                <i class="fas fa-edit me-1"></i> Editar
                            </a>
                        @endif

                        @if (Auth::user()->hasPermissionTo('compras', 'eliminar'))
                            {{-- Botón Eliminar Sólido (Rojo) --}}
                            <button type="button" class="btn btn-sm btn-danger" 
                                    title="Eliminar"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#confirmDeleteModal"
                                    data-item-nombre="la compra #{{ $compra->id }}"
                                    data-form-action="{{ route('compras.destroy', $compra->id) }}">
                                <i class="fas fa-trash me-1"></i> Eliminar
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-shopping-basket fa-3x mb-3 opacity-50"></i><br>
                        No hay compras registradas en el sistema.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Paginación fuera de card --}}
    <div class="mt-3">
        {{ $compras->links('pagination::bootstrap-5') }}
    </div>
</div>

{{-- Modal de Confirmación (Sin cambios de lógica) --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de eliminar: <strong id="modalItemNombre"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Sí, Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var confirmDeleteModal = document.getElementById('confirmDeleteModal');
        if (confirmDeleteModal) {
            confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var itemNombre = button.getAttribute('data-item-nombre'); 
                var formAction = button.getAttribute('data-form-action');
                confirmDeleteModal.querySelector('#modalItemNombre').textContent = itemNombre;
                confirmDeleteModal.querySelector('#deleteForm').setAttribute('action', formAction);
            });
        }
    });
</script>
@endpush