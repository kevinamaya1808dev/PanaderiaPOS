<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anticipo extends Model
{
    use HasFactory;

    // Indicamos explícitamente el nombre de la tabla
    protected $table = 'anticipos';

    protected $fillable = [
        'pedido_id',
        'caja_id',
        'monto',        
        'metodo_pago',
        'referencia_pago',
        'user_id',
        'created_at',
        'updated_at',
    ];

    // Opcional: Relación con Pedido (si tienes modelo Pedido)
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
    
    // Opcional: Relación con Usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}