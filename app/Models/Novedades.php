<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Novedades extends Model
{
   use SoftDeletes;

    protected $table = 'novedades';

    protected $fillable = [

        'empresa_id',

        'empleado_id',

        'sucursal_id',

        'empleado_autorizador_id',

        'tipo',

        'fecha_desde',

        'fecha_hasta',

        'hora_desde',

        'hora_hasta',

        'motivo',

        'observaciones',

        'observacion_autorizador',

        'archivo',

        'estado',

        'fecha_autorizacion',

        'activo',

        'afecta_asistencia',

        'prioridad',

    ];

    protected $casts = [

        'fecha_desde' => 'date',

        'fecha_hasta' => 'date',

        'fecha_autorizacion' => 'datetime',

        'activo' => 'boolean',

        'afecta_asistencia' => 'boolean',

        'prioridad' => 'integer',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

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
     * Sucursal.
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Empleado que autorizó la novedad.
     */
    public function autorizador()
    {
        return $this->belongsTo(
            Empleado::class,
            'empleado_autorizador_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Solo novedades activas.
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Solo novedades aprobadas.
     */
    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobada');
    }

    /**
     * Filtrar por empleado.
     */
    public function scopeEmpleado($query, int $empleadoId)
    {
        return $query->where(
            'empleado_id',
            $empleadoId
        );
    }

    /**
     * Filtrar por tipo.
     */
    public function scopeTipo($query, string $tipo)
    {
        return $query->where(
            'tipo',
            $tipo
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Indica si la novedad está aprobada.
     */
    public function estaAprobada(): bool
    {
        return $this->estado === 'aprobada';
    }

    /**
     * Indica si la novedad está pendiente.
     */
    public function estaPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    /**
     * Indica si la novedad está rechazada.
     */
    public function estaRechazada(): bool
    {
        return $this->estado === 'rechazada';
    }

    /**
     * Indica si aplica a toda la jornada.
     */
    public function jornadaCompleta(): bool
    {
        return is_null($this->hora_desde)
            && is_null($this->hora_hasta);
    }

    /**
     * Indica si afecta el cálculo de asistencia.
     */
    public function afectaAsistencia(): bool
    {
        return $this->afecta_asistencia;
    }

    /**
     * Indica si la novedad está vigente
     * para una fecha determinada.
     */
    public function aplicaEnFecha(string $fecha): bool
    {
        return $fecha >= $this->fecha_desde->toDateString()
            && $fecha <= $this->fecha_hasta->toDateString();
    }

    /**
     * Indica si la novedad corresponde a una ausencia.
     */
    public function esAusencia(): bool
    {
        return in_array($this->tipo, [

            'vacaciones',

            'enfermedad',

            'accidente',

            'maternidad',

            'paternidad',

            'suspension',

            'ausencia_justificada',

            'ausencia_injustificada'

        ]);
    }
}
