<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoDispositivo extends Model
{
    protected $table = 'empleado_dispositivos';

    protected $fillable = [
        'empresa_id',
        'empleado_id',
        'tipo',
        'identificador',
        'usuario',
        'celular',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Empresa a la que pertenece.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Empleado propietario del dispositivo.
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}