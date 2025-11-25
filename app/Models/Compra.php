<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'proveedor_id',
        'user_id',       
        'created_at', 
        'descripcion',
        'concepto',
        'metodo_pago',
        'total',
        'responsable_nombre'
    ];

    // Relación con Proveedor
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    // Relación con Usuario (El Responsable)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}