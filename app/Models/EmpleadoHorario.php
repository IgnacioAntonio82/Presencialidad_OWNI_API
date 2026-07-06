<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpleadoHorario extends Model
{
    use SoftDeletes;

    protected $table = 'empleado_horarios';

    protected $fillable = [

        'empresa_id',

        'empleado_id',

        'horario_id',

        'vigente_desde',

        'vigente_hasta',

        'activo',

    ];

    protected $casts = [

        'vigente_desde' => 'date',

        'vigente_hasta' => 'date',

        'activo' => 'boolean',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }


    public function scopeVigentes(
        $query,
        $fecha
    )
    {
        return $query

            ->where(
                'vigente_desde',
                '<=',
                $fecha
            )

            ->where(function ($q) use ($fecha) {

                $q->whereNull(
                    'vigente_hasta'
                )

                ->orWhere(
                    'vigente_hasta',
                    '>=',
                    $fecha
                );

            });

    }

    public function scopeActivos($query)
    {
        return $query->where(
            'activo',
            true
        );
    }

    public function scopeEmpleado(
        $query,
        int $empleadoId
    )
    {
        return $query->where(
            'empleado_id',
            $empleadoId
        );
    }

   
}
