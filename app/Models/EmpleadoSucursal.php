<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpleadoSucursal extends Model
{
    use HasFactory;

    protected $table = 'empleado_sucursal';

    protected $fillable = [
        'empleado_id',
        'sucursal_id',
        'vigente_desde',
        'vigente_hasta',
        'modalidad',
        'validar_gps',
        'activo',
    ];

    protected $casts = [
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
        'validar_gps'   => 'boolean',
        'activo'        => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
