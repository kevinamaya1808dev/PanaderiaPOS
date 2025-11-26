@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Card centrado --}}
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 700px;">
        
        <div class="card-header bg-dark text-white border-0">
            <h4 class="mb-0">Crear Nuevo Empleado</h4>
        </div>

        <div class="card-body p-4">

            <form action="{{ route('empleados.store') }}" method="POST" autocomplete="off" id="formEmpleado">
                @csrf

                {{-- NOMBRE DEL EMPLEADO (Siempre visible) --}}
                <div class="mb-4">
                    <label for="name" class="form-label fw-bold">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user fa-fw"></i></span>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="Ej. Juan Pérez"
                               autocomplete="off" 
                               required>
                    </div>
                    @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- INTERRUPTOR DE ACCESO AL SISTEMA --}}
                <div class="card bg-light mb-4 border-0">
                    <div class="card-body py-3">
                        <div class="form-check form-switch">
                            {{-- Si hubo error y estaba marcado, mantenerlo marcado con old() --}}
                            <input class="form-check-input" type="checkbox" id="checkAcceso" name="requiere_acceso" value="1" {{ old('requiere_acceso') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-primary" for="checkAcceso">
                                <i class="fas fa-key me-1"></i> ¿Dar acceso al sistema?
                            </label>
                            <div class="form-text small text-muted">
                                Marca esto <strong>SOLO</strong> si el empleado necesita acceso al sistema.
                                <br>Para empleados sin acceso, déjalo desmarcado.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ÁREA DE LOGIN (Se oculta/muestra con JS) --}}
                <div id="areaLogin" style="display: none;">
                    <h6 class="text-muted">Credenciales de Acceso</h6>
                    <hr class="mt-1 mb-3 border-secondary">

                    {{-- EMAIL (USUARIO) --}}
                    <div class="mb-3">
                        <label for="email_prefix" class="form-label">Usuario de Acceso</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope fa-fw"></i></span>
                            
                            {{-- Input visual --}}
                            <input type="text" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email_prefix" 
                                   name="email_prefix" 
                                   value="{{ old('email_prefix') }}" 
                                   placeholder="ej. juan.perez"
                                   autocomplete="off">
                            
                            {{-- Parte fija --}}
                            <span class="input-group-text bg-light text-secondary fw-bold">@panaderia.com</span>
                        </div>

                        {{-- Input OCULTO real --}}
                        <input type="hidden" name="email" id="hidden_email" value="{{ old('email') }}">

                        <small class="text-muted" style="font-size: 0.8rem;">El dominio se agrega automáticamente.</small>
                        
                        @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        @error('email_prefix') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    {{-- CONTRASEÑA --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock fa-fw"></i></span>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Mínimo 8 caracteres"
                                   autocomplete="new-password">
                        </div>
                        @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    {{-- CONFIRMAR CONTRASEÑA --}}
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock fa-fw"></i></span>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   autocomplete="new-password">
                        </div>
                    </div>
                </div>
                {{-- FIN ÁREA LOGIN --}}

                {{-- CARGO (Siempre visible, todos tienen puesto) --}}
                <div class="mb-3">
                    <label for="cargo_id" class="form-label">Cargo / Puesto</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-tag fa-fw"></i></span>
                        <select class="form-select @error('cargo_id') is-invalid @enderror" id="cargo_id" name="cargo_id" required>
                            <option value="" disabled selected>Selecciona un Cargo</option>
                            @foreach ($cargos as $cargo)
                                @if($cargo->nombre !== 'Super Administrador')
                                    <option value="{{ $cargo->id }}" {{ old('cargo_id') == $cargo->id ? 'selected' : '' }}>
                                        {{ $cargo->nombre }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    @error('cargo_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- SECCIÓN INFORMACIÓN ADICIONAL --}}
                <h6 class="text-muted mt-4">Información Adicional</h6>
                <hr class="mt-1 mb-3 border-secondary">

                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-map-marker-alt fa-fw"></i></span>
                        <input type="text" class="form-control @error('direccion') is-invalid @enderror" id="direccion" name="direccion" value="{{ old('direccion') }}" autocomplete="off">
                    </div>
                    @error('direccion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone fa-fw"></i></span>
                        <input type="text" class="form-control @error('telefono') is-invalid @enderror" id="telefono" name="telefono" value="{{ old('telefono') }}" autocomplete="off">
                    </div>
                    @error('telefono') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="fecha_contratacion" class="form-label">Fecha de Contratación</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-alt fa-fw"></i></span>
                        <input type="date" class="form-control @error('fecha_contratacion') is-invalid @enderror" id="fecha_contratacion" name="fecha_contratacion" value="{{ old('fecha_contratacion') }}">
                    </div>
                    @error('fecha_contratacion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                
                {{-- Botones de Acción --}}
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('empleados.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    
                    @if (Auth::user()->hasPermissionTo('usuarios', 'alta'))
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Guardar Empleado
                        </button>
                    @else
                        <span class="text-danger">No tienes permiso para guardar.</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SCRIPT INTEGRADO: EMAIL + INTERRUPTOR --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos del DOM
        const checkAcceso = document.getElementById('checkAcceso');
        const areaLogin = document.getElementById('areaLogin');
        
        // Inputs que deben ser obligatorios SOLO si hay acceso
        const emailPrefixInput = document.getElementById('email_prefix');
        const passwordInput = document.getElementById('password');
        const passwordConfInput = document.getElementById('password_confirmation');
        
        // Campo oculto de email
        const hiddenEmailInput = document.getElementById('hidden_email');
        const dominio = '@panaderia.com';

        // 1. FUNCIÓN: Actualizar email completo
        function updateFullEmail() {
            if(emailPrefixInput.value) {
                hiddenEmailInput.value = emailPrefixInput.value.trim() + dominio;
            } else {
                hiddenEmailInput.value = '';
            }
        }

        // 2. FUNCIÓN: Mostrar/Ocultar campos de login
        function toggleLoginFields() {
            if (checkAcceso.checked) {
                // MOSTRAR
                areaLogin.style.display = 'block';
                // Hacer obligatorios
                emailPrefixInput.required = true;
                passwordInput.required = true;
                passwordConfInput.required = true;
            } else {
                // OCULTAR
                areaLogin.style.display = 'none';
                // Quitar obligatoriedad
                emailPrefixInput.required = false;
                passwordInput.required = false;
                passwordConfInput.required = false;
                
                // Opcional: Limpiar valores si se desmarca para no enviar basura
                emailPrefixInput.value = '';
                passwordInput.value = '';
                passwordConfInput.value = '';
                hiddenEmailInput.value = '';
            }
        }

        // Listeners
        emailPrefixInput.addEventListener('input', updateFullEmail);
        checkAcceso.addEventListener('change', toggleLoginFields);

        // Ejecutar al inicio (para cargar estado 'old' o limpiar)
        updateFullEmail();
        toggleLoginFields();
    });
</script>
@endsection