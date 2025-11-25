@extends('layouts.app')

@section('content')
<div class="container">
    
    {{-- Barra de Título y Botón Crear --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Registro de Compras</h2>
        
        @if (Auth::user()->hasPermissionTo('compras', 'alta'))
            <a href="{{ route('compras.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Registrar Nueva Compra
            </a>
        @endif
    </div>

    {{-- Mensajes de Éxito/Error --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tabla --}}
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Proveedor / Concepto</th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Método Pago</th>
                    <th>Responsable</th>
                    <th>Total</th>
                    <th style="width: 180px;">Acciones</th> 
                </tr>
            </thead>
            <tbody>
                @forelse ($compras as $compra)
                <tr>
                    <td class="fw-bold">{{ $compra->id }}</td>

                    {{-- PROVEEDOR / GASTO GENERAL --}}
                    <td>
                        @if($compra->proveedor)
                            {{ $compra->proveedor->nombre }}
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
                                 style="width: 25px; height: 25px; font-size: 0.7rem; font-weight: bold;">
                                {{ strtoupper(substr($compra->user->name ?? 'S', 0, 1)) }}
                            </div>
                            <span class="text-dark fw-semibold">{{ $compra->user->name ?? 'Sistema' }}</span>
                        </div>
                    </td>

                    <td class="fw-bold text-danger">${{ number_format($compra->total, 2) }}</td>
                    
                    <td>
                        @if (Auth::user()->hasPermissionTo('compras', 'editar'))
                            <a href="{{ route('compras.edit', $compra->id) }}" class="btn btn-sm btn-warning me-1" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endif

                        @if (Auth::user()->hasPermissionTo('compras', 'eliminar'))
                            <button type="button" class="btn btn-sm btn-danger" 
                                    title="Eliminar"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#confirmDeleteModal"
                                    data-item-nombre="la compra #{{ $compra->id }}"
                                    data-form-action="{{ route('compras.destroy', $compra->id) }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center p-4 text-muted">No hay compras registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-3">
        {{ $compras->links() }}
    </div>
</div>

{{-- Modal de Confirmación --}}
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
