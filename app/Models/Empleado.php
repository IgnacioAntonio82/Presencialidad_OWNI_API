<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';

    protected $fillable = [
        'empresa_id',
        'legajo',
        'nombre',
        'apellido',
        'cuil',
        'telefono',
        'email',
        'fecha_nacimiento',
        'fecha_ingreso',
        'fecha_egreso',
        'usuario',
        'password',
        'rol',
        'activo',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso'    => 'date',
        'fecha_egreso'     => 'date',
        'activo'           => 'boolean',
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

    public function sucursales()
    {
        return $this->belongsToMany(
            Sucursal::class,
            'empleado_sucursal'
        )
        ->withPivot([
            'vigente_desde',
            'vigente_hasta',
            'modalidad',
            'validar_gps',
            'activo'
        ])
        ->withTimestamps();
    }

    public function dispositivos()
    {
        return $this->hasMany(EmpleadoDispositivo::class);
    }


}
