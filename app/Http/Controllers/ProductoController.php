<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria; // <-- CAMBIO 1: Asegúrate de que Categoria esté importado
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    /**
     * Muestra la lista de productos con su categoría y stock.
     */
    public function index()
    {
        // Cargar las relaciones para evitar consultas N+1
        $productos = Producto::with(['categoria', 'inventario'])->get();
        
        // ===== CAMBIO: Añadir esta línea para obtener las categorías =====
        $categorias = Categoria::all(); 

        // ===== CAMBIO: Añadir 'categorias' al compact =====
        return view('productos.index', compact('productos', 'categorias'));
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create()
    {
        // (Tu código create... sin cambios)
        $categorias = Categoria::all();
        return view('productos.create', compact('categorias'));
    }

    /**
     * Almacena un nuevo producto y su registro inicial de inventario.
     */
    public function store(Request $request)
    {
        // (Tu código store... sin cambios)
        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0.01',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'stock_inicial' => 'required|integer|min:0',
            'cantidad_minima' => 'required|integer|min:0',
        ]);
        DB::beginTransaction();
        try {
            $datosProducto = $request->only(['categoria_id', 'nombre', 'descripcion', 'precio']);
            if ($request->hasFile('imagen')) {
                $path = $request->file('imagen')->store('productos', 'public');
                $datosProducto['imagen'] = $path;
            }
            $producto = Producto::create($datosProducto);
            Inventario::create([
                'producto_id' => $producto->id,
                'stock' => $request->stock_inicial,
                'cantidad_minima' => $request->cantidad_minima,
                'cantidad_maxima' => 99999, 
            ]);
            DB::commit();
            return redirect()->route('productos.index')->with('success', 'Producto e Inventario inicial creados exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al crear el producto: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para editar el producto y su inventario.
     */
    public function edit(Producto $producto)
    {
        // (Tu código edit... sin cambios)
        $categorias = Categoria::all();
        $producto->load('inventario'); 
        return view('productos.edit', compact('producto', 'categorias'));
    }

    /**
     * Actualiza el producto y su inventario.
     */
    public function update(Request $request, Producto $producto)
    {
        // (Tu código update... sin cambios)
        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => ['required', 'string', 'max:255', Rule::unique('productos')->ignore($producto->id)],
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0.01',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cantidad_minima' => 'required|integer|min:0',
        ]);
        DB::beginTransaction();
        try {
            $datosProducto = $request->only(['categoria_id', 'nombre', 'descripcion', 'precio']);
            if ($request->hasFile('imagen')) {
                if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                    Storage::disk('public')->delete($producto->imagen);
                }
                $path = $request->file('imagen')->store('productos', 'public');
                $datosProducto['imagen'] = $path;
            }
            $producto->update($datosProducto);
            $producto->inventario->update([
                'cantidad_minima' => $request->cantidad_minima,
            ]);
            DB::commit();
            return redirect()->route('productos.index')->with('success', 'Producto y stock actualizados exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al actualizar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Elimina el producto y su registro de inventario asociado.
     */
    public function destroy(Producto $producto)
    {
        // (Tu código destroy... sin cambios)
        DB::beginTransaction();
        try {
            if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
            }
            if ($producto->inventario) {
                $producto->inventario->delete();
            }
            $producto->delete();
            DB::commit();
            return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('productos.index')->with('error', 'Ocurrió un error al eliminar el producto.');
        }
    }
}