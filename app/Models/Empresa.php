<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'razon_social',
        'nombre_fantasia',
        'cuit',
        'condicion_iva',
        'ingresos_brutos',
        'direccion',
        'localidad',
        'provincia',
        'codigo_postal',
        'fecha_inicio',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'activo' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class);
    }

    public function empleados()
    {
        return $this->hasMany(Empleado::class);
    }

    public function dispositivos()
    {
        return $this->hasMany(EmpleadoDispositivo::class);
    }
}
