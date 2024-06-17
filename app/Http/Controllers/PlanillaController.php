<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Nette\Utils\DateTime;
use App\Models\Planilla;
use App\Models\Empleado;
use App\Models\DetallePlanilla;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PlanillaController extends Controller
{

    public function index()
    {
        /*Poner fecha inicio*/
        return Planilla::paginate(5);
    }

    public function show(Request $request, int $id_planilla)
    {

    }

    public function obtenerPlanillasOrdenadasPorFecha(Request $request)
    {
        $fechaFiltro = $request->input("fechaFiltro", " ");
        if ($fechaFiltro == " ") {
            $fechaFiltro = date("Y");
        }
        $resultados = Planilla::whereYear("fecha_fin", "=", $fechaFiltro)
            ->orderByDesc("fecha_fin");
        return $resultados->paginate(5);
    }

    public function obtenerListaFechasPlanillas(Request $request)
    {
        /*agregar el flujo de error*/
        $resultado = Planilla::select(
            DB::raw("DISTINCT(YEAR(fecha_fin)) as anio")
        );
        return response()->json(
            [
                "resultado" => $resultado->get(),
            ]
        );
    }

    public function store(Request $request)
    {
        $fechaActual = new DateTime();
        //Comprobar si hay empleados activos
        $empleados = Empleado::where('esta_activo', '1')->get();
        if (!isset($empleados)) {
            return response()->json([
                'status' => true,
                'mensaje' => 'No se encontraron empleados activos.'
            ], 200);
        }

        $total_seguro = 0;
        $total_afp = 0;
        $total = 0; // total de planilla

        $fecha = $request->fecha;//fecha para la cual estamos generando la planilla
        $fecha = new DateTime($fecha);
        $diasMes = cal_days_in_month(CAL_GREGORIAN, $fecha->format('m'), $fecha->format('Y'));

        if ($fecha->format('d') <= 15) {
            $fechaInicio = date($fecha->format('Y') . '-' . $fecha->format('m') . '-1');
            $fechaFin = date($fecha->format('Y') . '-' . $fecha->format('m') . '-15');
        } else {
            $fechaInicio = date($fecha->format('Y') . '-' . $fecha->format('m') . '-16');
            $fechaFin = date($fecha->format('Y') . '-' . $fecha->format('m') . '-' . $diasMes);
        }

        if ($fechaActual <= $fechaFin) {
            return response()->json([
                'status' => false,
                'mensaje' => 'No se ha generado la planilla para el periodo especificado,  el periodo aun no finalizao o no ha iniciado'
            ]);
        }
        try {


            if (Planilla::where('fecha_inicio', $fechaInicio)->exists()) {
                return response()->json([
                    'status' => false,
                    'mensaje' => 'La planilla del ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin)) . ' Ya existe.'
                ], 200);
            } else {
                DB::beginTransaction();
                $planilla = Planilla::create([
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin
                ]);
            }

            if (!isset($planilla)) {
                return response()->json([
                    'status' => false,
                    'mensaje' => 'Ocurrio un error al crear la planilla'
                ], 200);
            }

            foreach ($empleados as $empleado) {
                //Creamos el detallePlanilla para cada empleado
                $diasLaborados = $empleado->asistencia()->where('fecha', '>=', $fechaInicio)->where('fecha', '<=', $fechaFin)->count();
                $salarioMensual = $empleado->cargo->salario_cargo;
                $sueldoDiario = $salarioMensual / 30.00;
                $sueldoBase = $sueldoDiario * $diasLaborados; //quincenal

                //Inicio de calculos de planillas

                //Calculo de vacaciones
                //$salarioQuincenal = $salarioMensual / 2;
                //$monto_vacaciones = $salarioQuincenal * 0.30;
                $monto_vacaciones = 0;
                //bonos
                $bono = 0; //Revisar despues como hacer esto

                //calculo de aguinaldo
                $monto_aguinaldo = 0;//$this->calcularAguinaldo($empleado);

                //1.Calcular monto gravable
                $monto_gravable_cotizable = $sueldoBase + $monto_vacaciones + $monto_aguinaldo + $bono;

                //2.Calcuar isss y afp (laboral y patronal)
                //laboral
                //$afp = $sueldoBase * 0.0725;
                //$isss = $sueldoBase * 0.03;
                $afp = $monto_gravable_cotizable * 0.0725;
                $isss = $monto_gravable_cotizable * 0.03;

                //patronal
                $afp_patronal = $monto_gravable_cotizable * 0.0875;
                $isss_patronal = $monto_gravable_cotizable * 0.075;

                //3.sumatorias
                $planilla_unica = $afp + $isss + $afp_patronal + $isss_patronal;

                $monto_pago_empleado = $monto_gravable_cotizable - $afp - $isss;

                //$totalAPagar = $sueldoBase - $afp - $isss;

                $detallePlanilla = DetallePlanilla::create([
                    'id_empleado' => $empleado->id_empleado,
                    'id_planilla' => $planilla->id,
                    'base' => round($sueldoBase, 4),
                    'monto_isss' => $isss,
                    'monto_afp' => $afp,
                    //'monto_pago' => $totalAPagar,
                    'monto_pago' => $monto_pago_empleado,
                    'dias_laborados' => $diasLaborados,
                    'monto_vacaciones' => $monto_vacaciones,
                    'monto_aguinaldo' => $monto_aguinaldo,
                    'monto_bonos' => $bono,
                    'monto_isss_patronal' => $isss_patronal,
                    'monto_afp_patronal' => $afp_patronal,
                    'monto_gravable_cotizable' => $monto_gravable_cotizable,
                    'monto_pago_empleado' => $monto_pago_empleado,
                    'monto_planilla_unica' => $planilla_unica,
                ]);

                $total_seguro += $isss;
                $total_afp += $afp;
                //$total += $totalAPagar;
                $total += $monto_pago_empleado;
            }

            $planilla->total_seguro = $total_seguro;
            $planilla->total_afp = $total_afp;
            /*$planilla->monto_vacaciones = ; Despues valorar si almacenar estos datos
            $planilla->monto_aguinaldo
            $planilla->monto_bonos
            $planilla->monto_isss_patronal
            $planilla->monto_afp_patronal
            $planilla->monto_gravable_cotizable
            $planilla->monto_pago_empleado
            $planilla->monto_planilla_unica
            $planilla->date_emision_boleta*/
            $planilla->total = $total;

            $planilla->update();
            $detalles = $planilla->detallesPlanilla()->get();

            DB::commit();
            return response()->json([
                'status' => true,
                'mensaje' => 'La planilla del ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin)) . ' se ha guardado correctamente',
                'planilla' => $planilla,
                'detalles' => $detalles,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error: ' . $e->getMessage() . '- Line: ' . $e->getLine());
            return response()->json([
                'status' => true,
                'mensaje' => 'Error inesperado',
                'planilla' => $planilla,
                'detalles' => $detalles,
            ], 400);
        }

    }

    public function calcularAguinaldo(Empleado $empleado)
    {
        $aniosLaborados = $empleado->aniosLaborados();
        $salarioDiario = $empleado->cargo->salario_cargo / 30;
        $aguinaldo = $this->get_aguinaldo_days($aniosLaborados) * $salarioDiario;
        return $aguinaldo;
    }

    public function get_aguinaldo_days($tiempo_laborando)
    {
        if (!is_numeric($tiempo_laborando) || $tiempo_laborando < 0) {
            throw new InvalidArgumentException('The number of years worked must be a non-negative number.');
        }

        return match (true) {
            $tiempo_laborando < 1 => 15 * $tiempo_laborando,
            $tiempo_laborando < 3 => 15,
            $tiempo_laborando < 10 => 19,
            $tiempo_laborando >= 10 => 21,
            default => 0,
        };
    }

    public function obetenerAniosDisponibles()
    {

    }

    public function obtenerDetallesPlanilla(Request $request, int $id)
    {
        $planilla = Planilla::find($id);
        $planilla->detallesPlanilla;
        foreach ($planilla->detallesPlanilla as $detallePlanilla) {
            $detallePlanilla->empleado;
            \Log::info($detallePlanilla->empleado);
        }
        


        return $planilla;
    }


    public function update(Request $request, Planilla $planilla)
    {
        $detalle_planillas = $request->detalle_planillas;

        \Log::info($detalle_planillas);
        try {


            DB::beginTransaction();
            foreach ($detalle_planillas as $detalle_planilla) {
                $detalle_planilla_bd = DetallePlanilla::find($detalle_planilla['detalle_planilla']);

                if (isset($detalle_planilla_bd) and $detalle_planilla_bd->dias_laborados > 0) {

                    $detalle_planilla_bd->monto_bonos = $detalle_planilla['monto_bonos'];

                    //update monto_gravado
                    if ($detalle_planilla['monto_vacaciones'] > 0) {
                        $detalle_planilla_bd->monto_vacaciones = (($detalle_planilla_bd->base / $detalle_planilla_bd->dias_laborados) * 15) * 0.3;
                        \Log::info($detalle_planilla_bd->monto_vacaciones);
                        $detalle_planilla_bd->monto_gravable_cotizable = $detalle_planilla_bd->base + $detalle_planilla_bd->monto_vacaiones;
                    }
                    \Log::info($detalle_planilla_bd->monto_vacaciones);
                    \Log::info($detalle_planilla_bd->monto_bonos);
                    \Log::info($detalle_planilla_bd->base);
                    $detalle_planilla_bd->monto_gravable_cotizable = $detalle_planilla_bd->base + $detalle_planilla_bd->monto_vacaciones +$detalle_planilla_bd->monto_bonos;

                    //update seguro y afp
                    $detalle_planilla_bd->monto_afp = $detalle_planilla_bd->monto_gravable_cotizable * 0.0725;
                    $detalle_planilla_bd->monto_isss = $detalle_planilla_bd->monto_gravable_cotizable * 0.03;

                    //patronal
                    $detalle_planilla_bd->monto_afp_patronal = $detalle_planilla_bd->monto_gravable_cotizable * 0.0875;
                    $detalle_planilla_bd->monto_isss_patronal = $detalle_planilla_bd->monto_gravable_cotizable * 0.075;

                    //3.actualizar sumatorias
                    $detalle_planilla_bd->monto_planilla_unica =
                        $detalle_planilla_bd->monto_afp +
                        $detalle_planilla_bd->monto_isss +
                        $detalle_planilla_bd->monto_afp_patronal +
                        $detalle_planilla_bd->monto_isss_patronal;

                    $detalle_planilla_bd->monto_pago_empleado = $detalle_planilla_bd->monto_gravable_cotizable - $detalle_planilla_bd->monto_afp - $detalle_planilla_bd->monto_isss;

                    $detalle_planilla_bd->save();
                }

            }

            DB::commit();

            return response()->json([
                'status' => 'ok',
                'message' => 'Actualizado correctamente'
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();
            \Log::info('Error: '.$e->getMessage().' - Line: '.$e->getLine());

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrio un error inesperado'
            ], 400);

        }
    }

}