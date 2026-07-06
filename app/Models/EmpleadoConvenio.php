<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpleadoConvenio extends Model
{
    use SoftDeletes;

    protected $table = 'empleado_convenios';

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Relaciones
        |--------------------------------------------------------------------------
        */

        'empresa_id',

        'empleado_id',

        'convenio_id',

        /*
        |--------------------------------------------------------------------------
        | Vigencia
        |--------------------------------------------------------------------------
        */

        'vigente_desde',

        'vigente_hasta',

        /*
        |--------------------------------------------------------------------------
        | Estado
        |--------------------------------------------------------------------------
        */

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

    public function convenio()
    {
        return $this->belongsTo(Convenio::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActivos($query)
    {
        return $query->where(
            'activo',
            true
        );
    }

    public function scopeVigentes($query, $fecha = null)
    {
        $fecha = $fecha ?? now()->toDateString();

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

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function estaVigente($fecha = null): bool
    {
        $fecha = $fecha ?? now()->toDateString();

        return
            $this->vigente_desde <= $fecha &&
            (
                is_null($this->vigente_hasta) ||
                $this->vigente_hasta >= $fecha
            );
    }
}