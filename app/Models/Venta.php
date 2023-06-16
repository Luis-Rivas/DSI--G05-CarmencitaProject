<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;


    protected $table = 'Venta';


    protected $primaryKey = 'id_venta';


    public $timestamps = false;


    protected $fillable = [
        'fecha_venta',
        'total_venta',
        'total_iva',
        'nombre_cliente_venta',
        'is_credito',
        'is_active'
    ];

    public function detalleVenta()
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta', 'id_venta');
    }

    // Relación uno a uno con CreditoFiscal
    public function creditoFiscal()
    {
        return $this->hasOne(CreditoFiscal::class, 'id_venta', 'id_venta');
    }

    


}
