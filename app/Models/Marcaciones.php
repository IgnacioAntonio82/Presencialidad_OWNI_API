<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Marcaciones extends Model
{
    use SoftDeletes;
    protected $table = 'marcaciones';
    

    protected $fillable = [
        'empresa_id',
        'empleado_id',
        'empleado_dispositivo_id',
        'empleado_autorizador_id',
        'sucursal_id',
        'tipo',
        'fecha',
        'fecha_hora',
        'latitud',
        'longitud',
        'origen',
        'es_manual',
        'estado',
        'id_dispositivo',
        'motivo',
        'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_hora' => 'datetime',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'es_manual' => 'boolean',
    ];

    /**
     * Empresa.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Empleado.
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    /**
     * Dispositivo utilizado.
     */
    public function dispositivo()
    {
        return $this->belongsTo(
            EmpleadoDispositivo::class,
            'empleado_dispositivo_id'
        );
    }

    /**
     * Empleado que autorizó/modificó.
     */
    public function autorizador()
    {
        return $this->belongsTo(
            Empleado::class,
            'empleado_autorizador_id'
        );
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}