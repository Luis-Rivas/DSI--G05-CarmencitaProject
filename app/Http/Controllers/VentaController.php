<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DetalleVentaController;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return response()->json([
            'respuesta' => true,
            'mensaje' => 'Lista de ventas',
            'datos' => Venta::all(),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $rules = [
            // fecha_venta en formato dd-mm-aaaa
            'fecha_venta' => 'required|date',
            'total_venta' => 'required|decimal:0,2',
            'total_iva' => 'required|decimal:0,2',
            'nombre_cliente_venta' => 'nullable|string|max:30',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'respuesta' => false,
                'mensaje' => $validator->errors()->all()
            ], 400);
        }
        if ($request->validate($rules)) {
            $venta = Venta::create($request->all());
            if (isset($venta)) {
                return response()->json([
                    'respuesta' => true,
                    'mensaje' => 'Venta creada correctamente',
                    'datos' => $venta->id_venta,
                ], 201);
            } else {
                return response()->json([
                    'respuesta' => false,
                    'mensaje' => 'Error al crear la venta',
                ], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        //Validar si existe el registro
        if (isset($venta)) {
            return response()->json([
                'respuesta' => true,
                'mensaje' => 'Venta encontrada',
                'datos' => $venta,
            ], 200);
        } else {
            return response()->json([
                'respuesta' => false,
                'mensaje' => 'Venta no encontrada',
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venta $venta)
    {
        //
        $rules = [
            'fecha_venta' => 'required|date',
            'total_venta' => 'required|decimal:2,2',
            'total_iva' => 'required|decimal:2,2',
            'nombre_cliente_venta' => 'required|string|max:30',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'respuesta' => false,
                'mensaje' => $validator->errors()->all()
            ], 400);
        }

        if ($request->validate($rules)) {
            $venta->update($request->all());
            if (isset($venta)) {
                return response()->json([
                    'respuesta' => true,
                    'mensaje' => 'Venta actualizada correctamente',
                ], 201);
            } else {
                return response()->json([
                    'respuesta' => false,
                    'mensaje' => 'Error al actualizar la venta',
                ], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venta $venta)
    {
        //
        if (isset($venta)) {
            $venta->delete();
            return response()->json([
                'respuesta' => true,
                'mensaje' => 'Venta eliminada correctamente',
            ], 200);
        } else {
            return response()->json([
                'respuesta' => false,
                'mensaje' => 'Error al eliminar la venta',
            ], 400);
        }
    }


    public function register_venta_detalle(Request $request)
    {
        //
        $rules = [
            // fecha_venta en formato dd-mm-aaaa
            'fecha_venta' => 'required|date',
            'total_venta' => 'required|decimal:0,2',
            'total_iva' => 'required|decimal:0,2',
            'nombre_cliente_venta' => 'nullable|string|max:30',
        ];
        $validator = Validator::make($request->venta, $rules);
        if ($validator->fails()) {
            return response()->json([
                'respuesta' => false,
                'mensaje' => $validator->errors()->all()
            ], 400);
        }

        $venta = Venta::create($request->venta);
        if (isset($venta)) {
            $detalle_venta = new DetalleVentaController();
            return $detalle_venta->register_detalle_venta($request, $venta->id_venta);
        } else {
            return response()->json([
                'respuesta' => false,
                'mensaje' => 'Error al crear la venta',
            ], 400);
        }
    }

    public function getVentasDomicilio(Request $request){

        $today = now()->format('Y-m-d');
        $date = $request->fecha;
        //$ventas = Venta::where('fecha_venta',$date)->get();
        $ventas = DB::select("SELECT * FROM venta WHERE venta.id_venta NOT IN (SELECT id_venta FROM ventadomicilio) and venta.fecha_venta=:fecha_venta",['fecha_venta'=>$date]);
        if(isset($ventas)){
            return response()->json([
                'status' => true,
                'facturas'=> $ventas,
                'fecha'=>$date
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'message' => 'no se encontraron pedidos'
            ], 400);
        }
    }
}
