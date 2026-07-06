<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feriado extends Model
{
    use SoftDeletes;

    protected $table = 'feriados';

    protected $fillable = [

        'empresa_id',

        'sucursal_id',

        'nombre',

        'fecha',

        'ambito',

        'tipo',

        'recurrente',

        'es_oficial',

        'activo',

    ];

    protected $casts = [

        'fecha' => 'date',

        'recurrente' => 'boolean',

        'es_oficial' => 'boolean',

        'activo' => 'boolean',

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
     * Sucursal.
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Solo feriados activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Filtrar por ámbito.
     */
    public function scopeAmbito($query, string $ambito)
    {
        return $query->where('ambito', $ambito);
    }

    /**
     * Filtrar por fecha.
     */
    public function scopeFecha($query, string $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Indica si el feriado pertenece a una empresa.
     */
    public function esEmpresa(): bool
    {
        return $this->ambito === 'empresa';
    }

    /**
     * Indica si el feriado pertenece a una sucursal.
     */
    public function esSucursal(): bool
    {
        return $this->ambito === 'sucursal';
    }

    /**
     * Indica si es un feriado nacional.
     */
    public function esNacional(): bool
    {
        return $this->ambito === 'nacional';
    }

    /**
     * Indica si es un feriado trasladable.
     */
    public function esTrasladable(): bool
    {
        return $this->tipo === 'trasladable';
    }

    /**
     * Indica si es un feriado inamovible.
     */
    public function esInamovible(): bool
    {
        return $this->tipo === 'inamovible';
    }

    /**
     * Indica si es un feriado puente.
     */
    public function esPuente(): bool
    {
        return $this->tipo === 'puente';
    }

    /**
     * Obtiene el feriado de una fecha determinada.
     */
    public static function obtenerPorFecha(string $fecha)
    {
        return static::whereDate('fecha', $fecha)
            ->where('activo', true)
            ->first();
    }
}