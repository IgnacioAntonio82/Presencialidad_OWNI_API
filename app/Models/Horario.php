<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Horario extends Model
{
    use SoftDeletes;

    protected $table = 'horarios';

    protected $fillable = [

        'empresa_id',
        'sucursal_id',

        'codigo',
        'nombre',

        'hora_ingreso',
        'hora_salida',

        'lunes',
        'martes',
        'miercoles',
        'jueves',
        'viernes',
        'sabado',
        'domingo',

        'tolerancia_ingreso',
        'tolerancia_salida',

        'activo',

    ];

    protected $casts = [

     

        'lunes' => 'boolean',
        'martes' => 'boolean',
        'miercoles' => 'boolean',
        'jueves' => 'boolean',
        'viernes' => 'boolean',
        'sabado' => 'boolean',
        'domingo' => 'boolean',

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

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function empleados()
    {
        return $this->belongsToMany(
            Empleado::class,
            'empleado_horarios'
        )
        ->withPivot([
            'empresa_id',
            'vigente_desde',
            'vigente_hasta',
            'activo'
        ])
        ->withTimestamps();
    }

    public function empleadoHorarios()
    {
        return $this->hasMany(EmpleadoHorario::class);
    }

    public function aplicaDia(string $dia): bool
    {
        return (bool) $this->{$dia};
    }

    public function contieneHora(string $hora): bool
    {
        return $hora >= $this->hora_ingreso
            && $hora <= $this->hora_salida;
    }

    public function getDiasAttribute(): array
    {
        return collect([
            'lunes',
            'martes',
            'miercoles',
            'jueves',
            'viernes',
            'sabado',
            'domingo',
        ])
        ->filter(fn ($dia) => $this->$dia)
        ->values()
        ->toArray();
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}