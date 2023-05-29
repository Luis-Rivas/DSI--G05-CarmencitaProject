<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return response()->json([
            'respuesta' => true,
            'mensaje' => 'Lista de clientes',
            'datos' => Cliente::all(),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'nombre_cliente' => 'required|string|max:50',
            'apellido_cliente' => 'required|string|max:50',
            'departamento_cliente' => 'required|string|max:50',
            'direccion_cliente' => 'required|string|max:50',
            'dui_cliente' => 'string|max:10',
            'nit_cliente' => 'string|max:20',
            'nrc_cliente' => 'required|string|max:20',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'respuesta' => false,
                'mensaje' => $validator->errors()->all(),
            ], 400);
        }

        if ($request->validate($rules)) {
            $cliente = Cliente::create($request->all());
            if (isset($cliente)) {
                return response()->json([
                    'respuesta' => true,
                    'mensaje' => 'Cliente creado correctamente',
                ], 201);
            } else {
                return response()->json([
                    'respuesta' => false,
                    'mensaje' => 'Error al crear el cliente',
                ], 400);
            }
        }

        
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        //Validar si existe el cliente
        if (isset($cliente)) {
            return response()->json([
                'respuesta' => true,
                'mensaje' => 'Cliente encontrado',
                'datos' => $cliente,
            ], 200);
        } else {
            return response()->json([
                'respuesta' => false,
                'mensaje' => 'Cliente no encontrado',
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        //
        $rules = [
            'nombre_cliente' => 'string|max:50',
            'apellido_cliente' => 'string|max:50',
            'departamento_cliente' => 'string|max:50',
            'direccion_cliente' => 'string|max:50',
            'dui_cliente' => 'max:10',
            'nit_cliente' => 'max:20',
            'nrc_cliente' => 'string|max:20',
        ];
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'respuesta' => false,
                'mensaje' => $validator->errors()->all(),
            ], 400);
        }

        if ($request->validate($rules)) {
            $cliente->update($request->all());
            if (isset($cliente)) {
                return response()->json([
                    'respuesta' => true,
                    'mensaje' => 'Cliente actualizado correctamente',
                ], 200);
            } else {
                return response()->json([
                    'respuesta' => false,
                    'mensaje' => 'Error al actualizar el cliente',
                ], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        //
        $cliente->delete();
        if (isset($cliente)) {
            return response()->json([
                'respuesta' => true,
                'mensaje' => 'Cliente eliminado correctamente',
            ]);
        } else {
            return response()->json([
                'respuesta' => false,
                'mensaje' => 'Error al eliminar el cliente',
            ]);
        }
    }
}
