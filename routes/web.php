<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AuthController; 
use App\Http\Controllers\CargoController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas (Autenticación)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Rutas de Login y Logout
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Requiere Sesión y Middleware de Permisos)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    
    // 1. DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. PERFIL Y LOGOUT
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ==========================================================
    // MÓDULOS CON PROTECCIÓN GRANULAR (RBAC)
    // ==========================================================
    
    // MÓDULO: EMPLEADOS (Alias: usuarios)
    // Listado (mostrar)
    Route::get('empleados', [EmpleadoController::class, 'index'])->name('empleados.index')->middleware('permiso:usuarios,mostrar');
    // Alta (alta)
    Route::get('empleados/create', [EmpleadoController::class, 'create'])->name('empleados.create')->middleware('permiso:usuarios,alta');
    Route::post('empleados', [EmpleadoController::class, 'store'])->name('empleados.store')->middleware('permiso:usuarios,alta');
    // Edición (editar)
    Route::get('empleados/{empleado}/edit', [EmpleadoController::class, 'edit'])->name('empleados.edit')->middleware('permiso:usuarios,editar');
    Route::put('empleados/{empleado}', [EmpleadoController::class, 'update'])->name('empleados.update')->middleware('permiso:usuarios,editar');
    // Eliminación (eliminar)
    Route::delete('empleados/{empleado}', [EmpleadoController::class, 'destroy'])->name('empleados.destroy')->middleware('permiso:usuarios,eliminar');


    // MÓDULO: CARGOS (Alias: cargos)
    // Listado (mostrar)
    Route::get('cargos', [CargoController::class, 'index'])->name('cargos.index')->middleware('permiso:cargos,mostrar');
    // Alta (alta)
    Route::get('cargos/create', [CargoController::class, 'create'])->name('cargos.create')->middleware('permiso:cargos,alta');
    Route::post('cargos', [CargoController::class, 'store'])->name('cargos.store')->middleware('permiso:cargos,alta');
    // Edición (editar) - También aplica a la matriz de permisos
    Route::get('cargos/{cargo}/edit', [CargoController::class, 'edit'])->name('cargos.edit')->middleware('permiso:cargos,editar');
    Route::put('cargos/{cargo}', [CargoController::class, 'update'])->name('cargos.update')->middleware('permiso:cargos,editar');
    // Eliminación (eliminar)
    Route::delete('cargos/{cargo}', [CargoController::class, 'destroy'])->name('cargos.destroy')->middleware('permiso:cargos,eliminar');

    // PERMISOS (Requiere permiso de 'editar' en el módulo 'cargos' para la matriz)
    Route::get('cargos/{cargo}/permisos', [PermisoController::class, 'index'])->name('cargos.permisos.index')->middleware('permiso:cargos,editar'); 
    Route::put('cargos/{cargo}/permisos', [PermisoController::class, 'update'])->name('cargos.permisos.update')->middleware('permiso:cargos,editar');


    // MÓDULO: CATEGORÍAS (Alias: productos)
    // Listado (mostrar)
    Route::get('categorias', [CategoriaController::class, 'index'])->name('categorias.index')->middleware('permiso:productos,mostrar');
    // Alta (alta)
    Route::get('categorias/create', [CategoriaController::class, 'create'])->name('categorias.create')->middleware('permiso:productos,alta');
    Route::post('categorias', [CategoriaController::class, 'store'])->name('categorias.store')->middleware('permiso:productos,alta');
    // Edición (editar)
    Route::get('categorias/{categoria}/edit', [CategoriaController::class, 'edit'])->name('categorias.edit')->middleware('permiso:productos,editar');
    Route::put('categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update')->middleware('permiso:productos,editar');
    // Eliminación (eliminar)
    Route::delete('categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy')->middleware('permiso:productos,eliminar');
    
    
    // MÓDULO: PRODUCTOS (Alias: productos) - Las rutas de productos también deben protegerse
    // Listado (mostrar)
    Route::get('productos', [ProductoController::class, 'index'])->name('productos.index')->middleware('permiso:productos,mostrar');
    // Alta (alta)
    Route::get('productos/create', [ProductoController::class, 'create'])->name('productos.create')->middleware('permiso:productos,alta');
    Route::post('productos', [ProductoController::class, 'store'])->name('productos.store')->middleware('permiso:productos,alta');
    // Edición (editar)
    Route::get('productos/{producto}/edit', [ProductoController::class, 'edit'])->name('productos.edit')->middleware('permiso:productos,editar');
    Route::put('productos/{producto}', [ProductoController::class, 'update'])->name('productos.update')->middleware('permiso:productos,editar');
    // Eliminación (eliminar)
    Route::delete('productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy')->middleware('permiso:productos,eliminar');


    // MÓDULO: CLIENTES (Alias: clientes)
    // Listado (mostrar)
    Route::get('clientes', [ClienteController::class, 'index'])->name('clientes.index')->middleware('permiso:clientes,mostrar');
    // Alta (alta)
    Route::get('clientes/create', [ClienteController::class, 'create'])->name('clientes.create')->middleware('permiso:clientes,alta');
    Route::post('clientes', [ClienteController::class, 'store'])->name('clientes.store')->middleware('permiso:clientes,alta');
    // Edición (editar)
    Route::get('clientes/{cliente}/edit', [ClienteController::class, 'edit'])->name('clientes.edit')->middleware('permiso:clientes,editar');
    Route::put('clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update')->middleware('permiso:clientes,editar');
    // Eliminación (eliminar)
    Route::delete('clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy')->middleware('permiso:clientes,eliminar');

    // Aquí irían Proveedores, Compras, Ventas, Cajas...
});