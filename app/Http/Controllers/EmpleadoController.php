<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cargo;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class EmpleadoController extends Controller
{
    /**
     * Muestra una lista de todos los empleados y sus cargos.
     */
    public function index()
    {
        $users = User::with(['cargo', 'empleado'])->get();
        return view('empleados.index', compact('users'));
    }

    /**
     * Muestra el formulario para crear un nuevo empleado.
     */
    public function create()
    {
        $cargos = Cargo::all();
        return view('empleados.create', compact('cargos'));
    }

    /**
     * Almacena un nuevo empleado y usuario en la base de datos.
     * MODIFICADO: Ahora soporta empleados SIN acceso al sistema.
     */
    public function store(Request $request)
    {
        // 1. Definir reglas básicas (aplican para TODOS)
        $rules = [
            'name' => 'required|string|max:255',
            'cargo_id' => 'required|exists:cargos,id',
            'telefono' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
        ];

        // 2. Validación Condicional: Solo pedimos credenciales si marcó el checkbox
        if ($request->has('requiere_acceso')) {
            $rules['email'] = 'required|string|email|max:255|unique:users';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        DB::beginTransaction();

        try {
            // 3. Preparar datos del Usuario
            $userData = [
                'name' => $request->name,
                'cargo_id' => $request->cargo_id,
            ];

            // Si requiere acceso, guardamos email y pass encriptada
            if ($request->has('requiere_acceso')) {
                $userData['email'] = $request->email;
                $userData['password'] = Hash::make($request->password);
            } else {
                // Si NO requiere acceso, dejamos estos campos explícitamente nulos
                $userData['email'] = null;
                $userData['password'] = null;
            }

            // Crear el registro en 'users'
            $user = User::create($userData);

            // 4. Crear el registro en 'empleados'
            Empleado::create([
                'idUserFK' => $user->id,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                // Guardamos el estado del acceso (1 o 0)
                'requiere_acceso' => $request->has('requiere_acceso') ? 1 : 0,
            ]);

            DB::commit();

            $mensaje = $request->has('requiere_acceso') 
                ? 'Empleado con acceso al sistema creado exitosamente.' 
                : 'Empleado registrado correctamente (Sin credenciales de acceso).';

            return redirect()->route('empleados.index')->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al crear el empleado: ' . $e->getMessage());
        }
    }

    /**
     * Muestra los datos de un empleado específico.
     */
    public function show(User $empleado)
    {
        $empleado->load('cargo', 'empleado');
        return view('empleados.show', compact('empleado'));
    }

    /**
     * Muestra el formulario para editar un empleado.
     */
    public function edit(User $empleado)
    {
        $cargos = Cargo::all();
        $empleado->load('empleado');
        return view('empleados.edit', compact('empleado', 'cargos'));
    }

    /**
     * Actualiza un empleado en la base de datos.
     */
    public function update(Request $request, User $empleado)
    {
        // TODO: Si deseas implementar la lógica de acceso también en la edición,
        // deberás modificar este método similar al store.
        
        $request->validate([
            'name' => 'required|string|max:255',
            // Validamos email solo si se envía o si el usuario ya tenía uno
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($empleado->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'cargo_id' => 'required|exists:cargos,id',
            'telefono' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $userData = [
                'name' => $request->name,
                'cargo_id' => $request->cargo_id,
            ];

            // Solo actualizamos email si viene en el request (o mantenemos el existente)
            if ($request->filled('email')) {
                $userData['email'] = $request->email;
            }

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $empleado->update($userData);

            if ($empleado->empleado) {
                 $empleado->empleado->update([
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                ]);
            }
            
            DB::commit();

            return redirect()->route('empleados.index')->with('success', 'Empleado actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al actualizar el empleado.');
        }
    }

    /**
     * Elimina un empleado y su usuario asociado.
     */
    public function destroy(User $empleado)
    {
        if (Auth::id() === $empleado->id) {
            return redirect()->route('empleados.index')->with('error', 'No puedes eliminar tu propia cuenta mientras estás logueado.');
        }

        if ($empleado->id === 1) {
            return redirect()->route('empleados.index')->with('error', 'El Super Administrador no puede ser eliminado.');
        }
        
        DB::beginTransaction();
        try {
            $empleado->delete(); 
            DB::commit();

            return redirect()->route('empleados.index')->with('success', 'Empleado y usuario eliminados exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('empleados.index')->with('error', 'Ocurrió un error al eliminar el empleado.');
        }
    }
}