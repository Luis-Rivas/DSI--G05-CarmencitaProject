<?php

use App\Http\Controllers\CargoController;
use App\Http\Controllers\JornadaLaboralDiariaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UnidadDeMedidaController;
use App\Http\Controllers\PrecioUnidadDeMedidaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DetalleVentaController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CreditoFiscalController;
use App\Http\Controllers\DetalleCreditoController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\DepartamentoController;
use App\Models\CreditoFiscal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Rutas para cargos
Route::resource('cargos',CargoController::class);

//Rutas para productos
Route::resource('productos',ProductoController::class);

//Rutas para unidades de medida
Route::resource('unidades_de_medida',UnidadDeMedidaController::class);

//Rutas para precios de unidades de medida
Route::resource('precios_unidades_de_medida',PrecioUnidadDeMedidaController::class);

//Rutas para jornadas laborales diarias
Route::resource('jornadas_laborales_diarias',JornadaLaboralDiariaController::class);

//Rutas para cargos
Route::resource('cargos',CargoController::class);


// ------------------------ RUTAS DAVID ------------------------
//Rutas para Cliente
Route::resource('clientes',ClienteController::class);

//Rutas para DetalleVenta
Route::resource('detalle_ventas',DetalleVentaController::class);

//Rutas para Venta
Route::resource('ventas',VentaController::class);

//Rutas para CreditoFiscal
Route::resource('credito_fiscals',CreditoFiscalController::class);

//Rutas para DetalleCreditoFiscal
Route::resource('detalle_creditos',DetalleCreditoController::class);

//Ruta para buscar Producto por Nombre
Route::get('productos/buscar/{nombre_producto}',[ProductoController::class,'getProductoPorNombre']);

//Ruta para obtener todos los nombres de los productos
Route::get('productos/nombres/lista',[ProductoController::class,'getNombresProductos']);

//Ruta para obtener un producto con sus precio de unidad de medida
Route::get('productos/precios/{nombre_producto}',[ProductoController::class,'getProductoConUnidadMedida']);

//Ruta para obtener todos los identificadores de los clientes
Route::get('clientes/identificador/lista',[ClienteController::class,'getListaClientesIdentificadores']);

//Rutas para Municipio
Route::resource('municipios',MunicipioController::class);

//Rutas para Departamento
Route::resource('departamentos',DepartamentoController::class);

//Ruta para obtener el departamento segun el nombre
Route::get('departamentos/buscar/{nombre_departamento}',[DepartamentoController::class,'getDepartamentoPorNombre']);

//Ruta para registrar una Venta con DetalleVenta junto
Route::post('ventas/registrar',[VentaController::class,'register_venta_detalle']);

//Ruta para registrar un Credito con DetalleCredito junto
Route::post('creditos/registrar',[CreditoFiscalController::class,'register_credito_detalle']);

