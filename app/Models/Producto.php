<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// ***** CAMBIO 1: Importar SoftDeletes *****
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory;

    // ***** CAMBIO 2: Usar SoftDeletes *****
    use SoftDeletes;

    protected $table = 'productos'; 
    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'precio',
        'imagen'
    ];

    // Un producto pertenece a una categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    // Un producto tiene un registro de inventario (Relación 1:1)
    public function inventario()
    {
        return $this->hasOne(Inventario::class, 'producto_id');
    }
}