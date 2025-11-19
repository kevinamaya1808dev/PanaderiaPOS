<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    /**
     * Muestra la lista de productos.
     */
    public function index()
    {
        $productos = Producto::with(['categoria', 'inventario'])->get();
        $categorias = Categoria::all(); 
        return view('productos.index', compact('productos', 'categorias'));
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
     * Almacena un nuevo producto.
     */
    public function store(Request $request)
    {
        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => [
                'required', 'string', 'max:255',
                // Validación Única: Ignora los registros que han sido borrados (soft deleted)
                Rule::unique('productos')->whereNull('deleted_at'),
            ],
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0.01',
            'costo' => 'required|numeric|min:0', // Validamos Costo
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'stock_inicial' => 'required|integer|min:0',
            'cantidad_minima' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $datosProducto = $request->only(['categoria_id', 'nombre', 'descripcion', 'precio', 'costo']);
            
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
            return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al crear: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para editar.
     */
    public function edit(Producto $producto)
    {
        $categorias = Categoria::all();
        $producto->load('inventario'); 
        return view('productos.edit', compact('producto', 'categorias'));
    }

    /**
     * Actualiza el producto.
     */
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            
            // --- CORRECCIÓN DEFINITIVA ---
            'nombre' => [
                'required', 'string', 'max:255',
                // 1. Ignora el ID de ESTE producto (para que no choque consigo mismo)
                // 2. Solo revisa productos que NO estén borrados (whereNull deleted_at)
                Rule::unique('productos')
                    ->ignore($producto->id)
                    ->whereNull('deleted_at'),
            ],
            // -----------------------------

            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0.01',
            'costo' => 'required|numeric|min:0', // Validamos Costo

            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cantidad_minima' => 'required|integer|min:0', 
        ]);

        DB::beginTransaction();
        try {
            $datosProducto = $request->only(['categoria_id', 'nombre', 'descripcion', 'precio', 'costo']);
            
            if ($request->hasFile('imagen')) {
                if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                    Storage::disk('public')->delete($producto->imagen);
                }
                $path = $request->file('imagen')->store('productos', 'public');
                $datosProducto['imagen'] = $path;
            }
            
            $producto->update($datosProducto);
            
            // Actualizar inventario
            $producto->inventario->update([
                'cantidad_minima' => $request->cantidad_minima,
            ]);
            
            DB::commit();
            return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Elimina el producto (Soft Delete).
     */
    public function destroy(Producto $producto)
    {
        DB::beginTransaction();
        try {
            // Nota: Con SoftDeletes, normalmente NO borramos la imagen del disco
            // ni borramos el inventario físico, solo marcamos el producto como borrado.
            // Si quieres borrado físico, usa forceDelete().
            
            $producto->delete(); // Esto pone la fecha en deleted_at

            DB::commit();
            return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('productos.index')->with('error', 'Error al eliminar.');
        }
    }
}