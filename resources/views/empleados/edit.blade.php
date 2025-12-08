@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Card centrado. Ancho de 700px --}}
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 700px;">
        
        <div class="card-header bg-dark text-white border-0">
            <h4 class="mb-0">Editar Empleado: {{ $empleado->name }}</h4>
        </div>

        <div class="card-body p-4">

            {{-- Formulario con ID para el script y autocomplete off --}}
            <form action="{{ route('empleados.update', $empleado->id) }}" method="POST" autocomplete="off" id="formEditEmpleado">
                @csrf
                @method('PUT')

                {{-- SECCIÓN DATOS DE ACCESO --}}
                <h6 class="text-muted">Datos de Acceso</h6>
                <hr class="mt-1 mb-3 border-secondary">

                {{-- NOMBRE COMPLETO --}}
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user fa-fw"></i></span>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $empleado->name) }}" 
                               autocomplete="off"
                               required
                               oninvalid="this.setCustomValidity('Por favor, ingresa el nombre completo.')"
                               oninput="this.setCustomValidity('')">
                    </div>
                    @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- EMAIL --}}
                <div class="mb-3">
                    <label for="email_prefix" class="form-label">Usuario de Acceso</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope fa-fw"></i></span>
                        
                        {{-- PHP para obtener solo la parte antes del @ --}}
                        @php
                            $emailParts = explode('@', $empleado->email);
                            $emailPrefix = $emailParts[0] ?? ''; 
                        @endphp

                        {{-- Input VISIBLE --}}
                        <input type="text" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email_prefix" 
                               name="email_prefix" 
                               value="{{ old('email_prefix', $emailPrefix) }}" 
                               autocomplete="off"
                               required
                               oninvalid="this.setCustomValidity('Por favor, ingresa el usuario.')"
                               oninput="this.setCustomValidity('')">
                               
                        {{-- Parte FIJA --}}
                        <span class="input-group-text bg-light text-secondary fw-bold">@panaderia.com</span>
                    </div>

                    {{-- Input OCULTO --}}
                    <input type="hidden" name="email" id="hidden_email" value="{{ old('email', $empleado->email) }}">
                    
                    <small class="text-muted" style="font-size: 0.8rem;">Modifica el usuario si es necesario (el dominio es fijo).</small>

                    @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- CARGO (ROL) --}}
                <div class="mb-3">
                    <label for="cargo_id" class="form-label">Cargo (Rol)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-tag fa-fw"></i></span>
                        <select class="form-select @error('cargo_id') is-invalid @enderror" 
                                id="cargo_id" 
                                name="cargo_id" 
                                required
                                oninvalid="this.setCustomValidity('Selecciona un cargo.')"
                                oninput="this.setCustomValidity('')">
                            <option value="" disabled>Seleccione un Cargo</option>
                            
                            @foreach ($cargos as $cargo)
                                @if($cargo->nombre !== 'Super Administrador' || $empleado->cargo_id == $cargo->id)
                                    <option value="{{ $cargo->id }}" {{ old('cargo_id', $empleado->cargo_id) == $cargo->id ? 'selected' : '' }}>
                                        {{ $cargo->nombre }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    @error('cargo_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- CONTRASEÑA (OPCIONAL) --}}
                <div class="mb-3">
                    <label for="password" class="form-label">Nueva Contraseña (Opcional)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock fa-fw"></i></span>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               autocomplete="new-password"
                               aria-describedby="passwordHelp">
                        
                        {{-- BOTÓN DE OJO --}}
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="passwordHelp" class="form-text">Dejar en blanco para conservar la contraseña actual.</div>
                    
                    {{-- TRADUCCIÓN DE ERRORES DEL SERVIDOR (Aquí está el truco para que salga en español) --}}
                    @error('password') 
                        <div class="invalid-feedback d-block">
                            @if(str_contains($message, 'match'))
                                Las contraseñas no coinciden.
                            @elseif(str_contains($message, 'characters'))
                                La contraseña debe tener al menos 8 caracteres.
                            @else
                                {{ $message }}
                            @endif
                        </div> 
                    @enderror
                </div>

                {{-- CONFIRMAR CONTRASEÑA --}}
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock fa-fw"></i></span>
                        <input type="password" 
                               class="form-control" 
                               id="password_confirmation" 
                               name="password_confirmation"
                               autocomplete="new-password">

                        {{-- BOTÓN DE OJO CONFIRMACIÓN --}}
                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                {{-- SECCIÓN INFORMACIÓN ADICIONAL --}}
                <h6 class="text-muted mt-4">Información Adicional</h6>
                <hr class="mt-1 mb-3 border-secondary">

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone fa-fw"></i></span>
                        <input type="text" class="form-control @error('telefono') is-invalid @enderror" id="telefono" name="telefono" value="{{ old('telefono', $empleado->empleado->telefono ?? '') }}" autocomplete="off">
                    </div>
                    @error('telefono') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-map-marker-alt fa-fw"></i></span>
                        <input type="text" class="form-control @error('direccion') is-invalid @enderror" id="direccion" name="direccion" value="{{ old('direccion', $empleado->empleado->direccion ?? '') }}" autocomplete="off">
                    </div>
                    @error('direccion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                
                {{-- (SE ELIMINÓ EL CAMPO FECHA DE CONTRATACIÓN) --}}

                {{-- Botones de Acción --}}
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('empleados.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    
                    @if (Auth::user()->hasPermissionTo('usuarios', 'editar'))
                        <button type="submit" class="btn btn-success">
                             <i class="fas fa-sync me-1"></i> Actualizar Empleado
                        </button>
                    @else
                        <span class="text-danger">No tienes permiso para editar.</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SCRIPT: EMAIL + TOGGLE PASSWORD --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const emailPrefixInput = document.getElementById('email_prefix');
        const hiddenEmailInput = document.getElementById('hidden_email');
        const dominio = '@panaderia.com';

        // 1. Función email
        function updateFullEmail() {
            if(emailPrefixInput.value) {
                hiddenEmailInput.value = emailPrefixInput.value.trim() + dominio;
            } else {
                hiddenEmailInput.value = '';
            }
        }

        // 2. Función Ojo (Toggle Password)
        function setupPasswordToggle(inputId, buttonId) {
            const input = document.getElementById(inputId);
            const button = document.getElementById(buttonId);
            
            if(input && button){
                button.addEventListener('click', function() {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            }
        }

        // Inicializar
        emailPrefixInput.addEventListener('input', updateFullEmail);
        updateFullEmail();

        setupPasswordToggle('password', 'togglePassword');
        setupPasswordToggle('password_confirmation', 'toggleConfirmPassword');
    });
</script>
@endsection