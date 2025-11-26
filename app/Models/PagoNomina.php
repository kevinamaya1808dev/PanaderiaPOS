<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoNomina extends Model
{
    use HasFactory;

    // Aseguramos que apunte a la tabla correcta
    protected $table = 'pago_nominas';

    // ESTO ES LO QUE TE FALTA: La lista blanca de campos permitidos
    protected $fillable = [
        'empleado_id',
        'fecha',
        'monto',
        'concepto',
        'descuento',
        'liquidado'
    ];

    // Relación inversa (opcional pero útil)
    public function empleado()
    {
        // Recuerda que tu llave primaria en empleados es 'idEmp'
        return $this->belongsTo(Empleado::class, 'empleado_id', 'idEmp');
    }
}