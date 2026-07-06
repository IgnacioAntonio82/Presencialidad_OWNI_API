<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Convenios extends Model
{
    use SoftDeletes;

    protected $table = 'convenios';

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Relaciones
        |--------------------------------------------------------------------------
        */

        'empresa_id',

        /*
        |--------------------------------------------------------------------------
        | Datos
        |--------------------------------------------------------------------------
        */

        'codigo',

        'nombre',

        /*
        |--------------------------------------------------------------------------
        | Jornada
        |--------------------------------------------------------------------------
        */

        'jornada_diaria',

        'jornada_semanal',

        /*
        |--------------------------------------------------------------------------
        | Horas extras
        |--------------------------------------------------------------------------
        */

        'permite_horas_extras',

        'permite_banco_horas',

        /*
        |--------------------------------------------------------------------------
        | Compensatorios
        |--------------------------------------------------------------------------
        */

        'permite_compensatorio',

        'compensatorio_reemplaza_pago',

        'genera_compensatorio_domingo',

        'genera_compensatorio_feriado',

        'genera_compensatorio_sabado_100',

        /*
        |--------------------------------------------------------------------------
        | Horas al 100 %
        |--------------------------------------------------------------------------
        */

        'sabado_desde_100',

        'domingo_100',

        'feriado_100',

        /*
        |--------------------------------------------------------------------------
        | Nocturnidad
        |--------------------------------------------------------------------------
        */

        'considera_nocturnidad',

        'inicio_nocturnidad',

        'fin_nocturnidad',

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

        'jornada_diaria' => 'decimal:2',

        'jornada_semanal' => 'decimal:2',

        'permite_horas_extras' => 'boolean',

        'permite_banco_horas' => 'boolean',

        'permite_compensatorio' => 'boolean',

        'compensatorio_reemplaza_pago' => 'boolean',

        'genera_compensatorio_domingo' => 'boolean',

        'genera_compensatorio_feriado' => 'boolean',

        'genera_compensatorio_sabado_100' => 'boolean',

        'domingo_100' => 'boolean',

        'feriado_100' => 'boolean',

        'considera_nocturnidad' => 'boolean',

        'vigente_desde' => 'date',

        'vigente_hasta' => 'date',

        'activo' => 'boolean',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    /**
     * Empresa propietaria del convenio.
     * Null = convenio general del sistema.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Empleados que poseen este convenio.
     */
    public function empleados()
    {
        return $this->belongsToMany(
            Empleado::class,
            'empleado_convenios'
        )
        ->withPivot([
            'empresa_id',
            'vigente_desde',
            'vigente_hasta',
            'activo'
        ])
        ->withTimestamps();
    }

    /**
     * Asignaciones del convenio a empleados.
     */
    public function empleadoConvenios()
    {
        return $this->hasMany(
            EmpleadoConvenio::class
        );
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

    public function scopeVigentes($query)
    {
        return $query
            ->where('vigente_desde', '<=', now())
            ->where(function ($q) {

                $q->whereNull('vigente_hasta')
                  ->orWhere(
                      'vigente_hasta',
                      '>=',
                      now()
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
        $fecha = $fecha
            ? \Carbon\Carbon::parse($fecha)
            : now();

        return
            $this->vigente_desde <= $fecha &&
            (
                is_null($this->vigente_hasta) ||
                $this->vigente_hasta >= $fecha
            );
    }
}
