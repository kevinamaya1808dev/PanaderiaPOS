@extends('layouts.app')

@section('content')
<div class="container">
    
    {{-- Título --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-history text-secondary me-2"></i> Historial de Turnos y Cajas
        </h2>
    </div>

    {{-- Tabla libre en el contenedor (Sin Card, igual que Empleados) --}}
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            {{-- Encabezado Oscuro --}}
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Turno (Estimado)</th>
                    <th>Empleado</th>
                    <th>Estado</th>
                    <th>Saldo Inicial</th>
                    <th>Saldo Final</th>
                    <th class="text-end" style="width: 150px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                {{-- Lógica original intacta --}}
                @forelse($cajas as $caja)
                    <tr>
                        <td class="fw-bold">
                            {{ $caja->fecha_hora_apertura->format('d/m/Y') }}
                        </td>
                        <td>
                            @if($caja->fecha_hora_apertura->hour < 14)
                                <span class="badge bg-info text-dark">
                                    <i class="fas fa-sun me-1"></i> Matutino
                                </span>
                            @else
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-moon me-1"></i> Vespertino
                                </span>
                            @endif
                            <br>
                            <small class="text-muted">{{ $caja->fecha_hora_apertura->format('h:i A') }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 35px; height: 35px; min-width: 35px;">
                                    {{ strtoupper(substr($caja->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $caja->user->name ?? 'Usuario Eliminado' }}</div>
                                    <small class="text-muted">ID: {{ $caja->user_id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($caja->estado === 'abierta')
                                <span class="badge bg-success">Activa (Abierta)</span>
                            @else
                                <span class="badge bg-secondary">Cerrada</span>
                                <div class="small text-muted">
                                    {{ $caja->fecha_hora_cierre ? $caja->fecha_hora_cierre->format('h:i A') : '' }}
                                </div>
                            @endif
                        </td>
                        <td>${{ number_format($caja->saldo_inicial, 2) }}</td>
                        <td class="fw-bold text-success">
                            {{ $caja->saldo_final ? '$'.number_format($caja->saldo_final, 2) : '--' }}
                        </td>
                        <td class="text-end">
                            {{-- Botón estilo sólido para coincidir con el resto del sistema --}}
                            <a href="{{ route('historial_cajas.show', $caja->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye me-1"></i> Ver Detalle
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i><br>
                            No hay registros de cajas en el sistema.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación limpia --}}
    <div class="mt-3">
        {{ $cajas->links('pagination::bootstrap-5') }} 
    </div>
</div>
@endsection