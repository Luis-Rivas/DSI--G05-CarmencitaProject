<?php

namespace App\Http\Controllers;

use App\Models\Incapacidad;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class IncapacidadesController extends Controller
{
    public function index(Request $request) //Empleados
    {
        try {
            // Obtener los parámetros id_empleado y fechaAusencia del request
            $idEmpleado = $request->id_empleado;
            $fechaAusencia = $request->fechaAusencia;

            // Filtrar las incapacidades por id_empleado y fecha_solicitud si se proporcionaron
            $query = Incapacidad::with(['empleado', 'estado']);
            if ($idEmpleado) {
                $query->where('id_empleado', $idEmpleado);
            }
            if ($fechaAusencia) {
                $query->whereDate('fecha_solicitud', $fechaAusencia);
            }

            $incapacidades = $query->get();

            $listadoFiltrado = $incapacidades->map(function ($incapacidad) {
                return [
                    'id' => $incapacidad->id,
                    'fecha_inicio' => $incapacidad->fecha_inicio,
                    'fecha_fin' => $incapacidad->fecha_fin,
                    'fecha_solicitud' => $incapacidad->fecha_solicitud,
                    'comprobante' => $incapacidad->comprobante,
                    'detalle' => $incapacidad->detalle,
                    'created_at' => $incapacidad->created_at,
                    'updated_at' => $incapacidad->updated_at,
                    'empleado' => [
                        'id_empleado' => $incapacidad->empleado->id_empleado,
                        'nombre_completo' => $incapacidad->empleado->primer_nombre . ' ' . $incapacidad->empleado->primer_apellido,
                    ],
                    'estado' => [
                        'id' => $incapacidad->estado->id,
                        'nombre' => $incapacidad->estado->nombre,
                    ],
                ];
            })->toArray();

            // Paginar el array transformado
            $currentPage = $request->page ?: 1;
            $perPage = 8; // Mismo valor de perPage que en indexGerente
            $offset = ($currentPage - 1) * $perPage;
            $listadoPaginado = array_slice($listadoFiltrado, $offset, $perPage);

            return response()->json([
                'data' => $listadoPaginado,
                'totalPages' => ceil(count($listadoFiltrado) / $perPage),
                'errors' => []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function indexGerente(Request $request) //Gerente
    {
        try {
            // Obtener el parámetro fechaAusencia del request
            $fechaAusencia = $request->fechaAusencia;

            // Filtrar las incapacidades por fecha_solicitud si se proporcionó fechaAusencia
            $query = Incapacidad::with(['empleado', 'estado']);
            if ($fechaAusencia) {
                $query->whereDate('fecha_solicitud', $fechaAusencia);
            }
            $incapacidades = $query->get();

            $listadoFiltrado = $incapacidades->map(function ($incapacidad) {
                return [
                    'id' => $incapacidad->id,
                    'fecha_inicio' => $incapacidad->fecha_inicio,
                    'fecha_fin' => $incapacidad->fecha_fin,
                    'fecha_solicitud' => $incapacidad->fecha_solicitud,
                    'comprobante' => $incapacidad->comprobante,
                    'detalle' => $incapacidad->detalle,
                    'created_at' => $incapacidad->created_at,
                    'updated_at' => $incapacidad->updated_at,
                    'empleado' => [
                        'id_empleado' => $incapacidad->empleado->id_empleado,
                        'nombre_completo' => $incapacidad->empleado->primer_nombre . ' ' . $incapacidad->empleado->primer_apellido,
                    ],
                    'estado' => [
                        'id' => $incapacidad->estado->id,
                        'nombre' => $incapacidad->estado->nombre,
                    ],
                ];
            })->toArray();

            // Paginar el array transformado
            $currentPage = $request->page ?: 1;
            $perPage = 8;
            $offset = ($currentPage - 1) * $perPage;
            $listadoPaginado = array_slice($listadoFiltrado, $offset, $perPage);

            return response()->json([
                'data' => $listadoPaginado,
                'totalPages' => ceil(count($listadoFiltrado) / $perPage),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $incapacidad = Incapacidad::find($id);

        if ($incapacidad == null) {
            return response()->json(['No se encontro la incapadidad solicitada.'], 404);
        } else {
            return response()->json($incapacidad);
        }
    }

    public function store(Request $request)
    {

        $id_empleado = Auth::user()->id_empleado;
        error_log($request->hasFile('comprobante'));
        error_log($request->incapacida);
        $rules = [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'detalle' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'data' => [],
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            if (isset($id_empleado) && $id_empleado == null) {
                return response()->json([
                    'data' => [],
                    'errors' => 'No se encontró el empleado'
                ], 404);
            }
            if ($request->hasFile('comprobante')) {
                $rules = [
                    'comprobante' => 'file|mimes:pdf|max:10048'
                ];
                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    return response()->json([
                        'data' => [],
                        'errors' => $validator->errors()
                    ], 400);
                }
            }
            $date = Carbon::now();
            $incapacidad = Incapacidad::create([
                'id_empleado' => $id_empleado,
                'fecha_solicitud' => $date,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'id_estado' => 1,
                'comprobante' => $request->comprobante,
                'detalle' => $request->detalle,

            ]);
            if ($request->hasFile('comprobante')) {
                $incapacidad->update([
                    'comprobante' => $request->comprobante->store('incapacidades/comprobantes')
                ]);
                $incapacidad->save();
            }
            return response()->json([
                'data' => $incapacidad,
                'errors' => []
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $id_empleado = Auth::user()->id_empleado;
        $rules = [
            'detalle' => 'required|string'
        ];
        if ($request->hasFile('comprobante')) {
            $rules['comprobante'] = 'nullable|file|mimes:pdf|max:10048';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'data' => [],
                'errors' => $validator->errors()
            ], 400);
        }
        try {
            if (isset($id_empleado) && $id_empleado == null) {
                return response()->json([
                    'data' => [],
                    'errors' => 'No se encontró el empleado'
                ], 404);
            }
            $incapacidad = Incapacidad::find($id);
            $incapacidad->update([
                'detalle' => $request->detalle,
            ]);
            if ($request->hasFile('comprobante')) {
                // Eliminar el archivo anterior de Storage/app
                if ($incapacidad->comprobante !== null) {
                    Storage::delete($incapacidad->comprobante);
                }
                $incapacidad->update([
                    'comprobante' => $request->comprobante->store('incapacidades/comprobantes')
                ]);
            } else {
                if ($incapacidad->comprobante !== null) {
                    Storage::delete($incapacidad->comprobante);
                }
                $incapacidad->update([
                    'comprobante' => null
                ]);
            }

            return response()->json([
                'data' => $incapacidad,
                'errors' => []
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(String $id)
    {
        try {
            $incapacidad = Incapacidad::find($id);
            if ($incapacidad->comprobante !== null) {
                Storage::delete($incapacidad->comprobante);
            }
            $incapacidad->delete();
            return response()->json([
                'data' => $incapacidad,
                'errors' => []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function actualizarEstado(Request $request) //Gerente
    {
        $rules = [
            'id_estado' => 'required|exists:estados,id',
            'id' => 'required|exists:incapacidades,id'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'data' => [],
                'errors' => $validator->errors()
            ], 400);
        }
        try {
            $incapacidad = Incapacidad::find($request->id);
            //Validar
            if ($incapacidad->id_estado == 1) {

                $incapacidad->update([
                    'id_estado' => $request->id_estado
                ]);
                return response()->json([
                    'data' => $incapacidad,
                    'errors' => []
                ], 201);
            } else {
                return response()->json(['Error, no se puede cambiar el estado'], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function getArchivoComprobante(Request $request)
    {
        error_log($request->comprobante);
        return response()->download(storage_path('app/' . $request->comprobante));
    }
}
