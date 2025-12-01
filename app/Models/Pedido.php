<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'cliente_id',
        'nombre_cliente',
        'telefono_cliente',
        'fecha_entrega',
        'total',
        'anticipo',
        'estatus',
        'notas_especiales',
        'user_id'
    ];

    protected $casts = [
        'fecha_entrega' => 'datetime',
    ];

    // Relación: Un pedido pertenece a un Cliente
    public function cliente()
    {
        // 'cliente_id' es la columna en pedidos
        // 'idCli' es la columna en clientes
        return $this->belongsTo(Cliente::class, 'cliente_id', 'idCli');
    }

    // Relación: Un pedido tiene muchos detalles (productos)
    public function detalles()
    {
        return $this->hasMany(DetallePedido::class);
    }

    // Relación: Quién registró el pedido
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    // Helper para saber saldo pendiente
    public function getSaldoPendienteAttribute()
    {
        return $this->total - $this->anticipo;
    }
}