<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// ===== CAMBIO: Añadir Categoria para poder usarla =====
use App\Models\Categoria;

class InventarioController extends Controller
{
    /**
     * Muestra la lista de inventario (Stock, Mínimos, Máximos).
     */
    public function index()
    {
        // Obtener todos los productos con su registro de inventario asociado
        $productos = Producto::with('inventario', 'categoria')->get();

        // ===== CAMBIO: Añadir la lista de categorías para el filtro =====
        $categorias = Categoria::all();

        // NOTA: El permiso 'inventario,mostrar' ya protege esta ruta.

        // ===== CAMBIO: Pasar $categorias a la vista =====
        return view('inventario.index', compact('productos', 'categorias'));
    }

    // Aquí irían métodos para añadir stock manualmente (ajustes o entradas rápidas)
    
    /**
     * Muestra el formulario para ajustar el stock de un producto.
     */
    public function edit(Producto $producto)
    {
        $producto->load('inventario');
        return view('inventario.edit', compact('producto'));
    }

    /**
     * Procesa la solicitud para actualizar el stock.
     */
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
            'cantidad_minima' => 'nullable|integer|min:0',
            'cantidad_maxima' => 'nullable|integer|min:0',
        ]);
        
        if (!$producto->inventario) {
            return redirect()->back()->with('error', 'Error: No se encontró registro de inventario para este producto.');
        }

        $producto->inventario->update([
            'stock' => $request->stock,
            'cantidad_minima' => $request->cantidad_minima,
            'cantidad_maxima' => $request->cantidad_maxima,
        ]);

        return redirect()->route('inventario.index')->with('success', 'Inventario de ' . $producto->nombre . ' actualizado exitosamente.');
    }
}