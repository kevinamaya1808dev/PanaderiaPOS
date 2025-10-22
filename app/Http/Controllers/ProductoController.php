<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    /**
     * Muestra la lista de productos con su categoría y stock.
     */
    public function index()
    {
        // Cargar las relaciones para evitar consultas N+1
        $productos = Producto::with(['categoria', 'inventario'])->get();
        return view('productos.index', compact('productos'));
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create()
    {
        $categorias = Categoria::all();
        return view('productos.create', compact('categorias'));
    }

    /**
     * Almacena un nuevo producto y su registro inicial de inventario.
     */
    public function store(Request $request)
    {
        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0.01',
            // Campos de Inventario
            'stock_inicial' => 'required|integer|min:0',
            'cantidad_minima' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            // 1. Crear el Producto
            $producto = Producto::create([
                'categoria_id' => $request->categoria_id,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio' => $request->precio,
            ]);

            // 2. Crear el registro de Inventario inicial (vinculado al producto_id)
            Inventario::create([
                'producto_id' => $producto->id,
                'stock' => $request->stock_inicial,
                'cantidad_minima' => $request->cantidad_minima,
                // Puedes dejar cantidad_maxima como stock_inicial por defecto o usar null
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
        $categorias = Categoria::all();
        // Cargar el inventario relacionado
        $producto->load('inventario'); 
        return view('productos.edit', compact('producto', 'categorias'));
    }

    /**
     * Actualiza el producto y su inventario.
     */
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => ['required', 'string', 'max:255', Rule::unique('productos')->ignore($producto->id)],
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0.01',
            'cantidad_minima' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0', // El stock también puede ser editado
        ]);

        DB::beginTransaction();

        try {
            // 1. Actualizar Producto
            $producto->update([
                'categoria_id' => $request->categoria_id,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio' => $request->precio,
            ]);

            // 2. Actualizar Inventario (Usando la relación hasOne)
            $producto->inventario->update([
                'stock' => $request->stock,
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
        DB::beginTransaction();
        try {
            // El inventario se eliminará automáticamente si la FK tiene ON DELETE CASCADE, 
            // pero es más seguro eliminarlo explícitamente primero.
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