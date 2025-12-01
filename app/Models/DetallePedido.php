<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedidos';
    public $timestamps = false; // Esta tabla no suele necesitar created_at/updated_at

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'especificaciones'
    ];

    public function producto()
    {
        // Si tus productos usan 'idProd', cambia 'id' por 'idProd' aquí también
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}