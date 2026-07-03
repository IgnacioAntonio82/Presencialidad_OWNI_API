<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'tipo_ubicacion',
        'direccion',
        'localidad',
        'provincia',
        'codigo_postal',
        'telefono',
        'latitud',
        'longitud',
        'radio_permitido',
        'codigo_sucursal',
        'telegram_link',
        'activo',
    ];

    protected $casts = [
        'latitud'         => 'decimal:7',
        'longitud'        => 'decimal:7',
        'radio_permitido' => 'integer',
        'activo'          => 'boolean',
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

    public function empleados()
    {
        return $this->belongsToMany(
            Empleado::class,
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
}