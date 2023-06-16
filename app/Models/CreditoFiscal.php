<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditoFiscal extends Model
{
    use HasFactory;


    protected $table = 'CreditoFiscal';


    protected $primaryKey = 'id_creditofiscal';


    public $timestamps = false;


    protected $fillable = [
        'id_cliente',
        'id_venta',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }
    
    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }
}
