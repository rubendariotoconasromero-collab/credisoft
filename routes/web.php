<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CodeudorController;
use App\Http\Controllers\HistorialPagosController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\CajaMovimientosController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', 'App\Http\Controllers\LoginController@irLogin')->name('ir_login');
Route::post('/login_process', 'App\Http\Controllers\LoginController@loginProcess');
Route::get('/administracion', 'App\Http\Controllers\LoginController@administracion')->name('administracion')->middleware('auth');
Route::post('/administracion', 'App\Http\Controllers\LoginController@logout')->name('logout');

// permisos
Route::get('/get_permisos', 'App\Http\Controllers\PermisoController@getPermisos');
Route::get('/get_permisos_rol', 'App\Http\Controllers\PermisoController@getPermisoRol');

// roles
Route::get('/roles', 'App\Http\Controllers\RolController@index')->middleware('auth');
Route::get('/get_roles', 'App\Http\Controllers\RolController@getRoles');
Route::get('/get_roles_usuarios', 'App\Http\Controllers\RolController@getRolesUsuarios');
Route::get('/activar_rol', 'App\Http\Controllers\RolController@activar');
Route::get('/desactivar_rol', 'App\Http\Controllers\RolController@desactivar');
Route::post('/save_rol', 'App\Http\Controllers\RolController@save');
Route::post('/modify_rol', 'App\Http\Controllers\RolController@modify');

// usuarios
Route::get('/usuarios', 'App\Http\Controllers\UsuarioController@index')->middleware('auth');
Route::get('/get_usuarios', 'App\Http\Controllers\UsuarioController@getUsuarios');
Route::get('/get_usuarios_sin', 'App\Http\Controllers\UsuarioController@getUsuariosSin');

Route::get('/activar_usuario', 'App\Http\Controllers\UsuarioController@activar');
Route::get('/desactivar_usuario', 'App\Http\Controllers\UsuarioController@desactivar');
Route::post('/save_usuario', 'App\Http\Controllers\UsuarioController@save');
Route::post('/modify_usuario', 'App\Http\Controllers\UsuarioController@modify');

// mi empresa
Route::get('/informacion', 'App\Http\Controllers\MiEmpresaController@index')->middleware('auth');
Route::post('/modify_miempresa', 'App\Http\Controllers\MiEmpresaController@modify');
Route::get('/get_mi_empresa', 'App\Http\Controllers\MiEmpresaController@getMiEmpresa');

// cliente
Route::get('/clientes', 'App\Http\Controllers\ClienteController@index')->middleware('auth');
Route::get('/get_clientes', 'App\Http\Controllers\ClienteController@getClientes');
Route::get('/get_clientes_paginate', 'App\Http\Controllers\ClienteController@getClientesPaginate');
Route::get('/get_clientes_sin', 'App\Http\Controllers\ClienteController@getClientesSin');
Route::get('/get_direcciones_telefono', 'App\Http\Controllers\ClienteController@getDireccionesTelefonos');
Route::get('/activar_cliente', 'App\Http\Controllers\ClienteController@activar');
Route::get('/desactivar_cliente', 'App\Http\Controllers\ClienteController@desactivar');
Route::post('/save_cliente', 'App\Http\Controllers\ClienteController@save');
Route::post('/modify_cliente', 'App\Http\Controllers\ClienteController@modify');
Route::get('/clientes_pdf', 'App\Http\Controllers\ClienteController@clientesPdf2');
Route::post('/fotoCliente', 'App\Http\Controllers\ClienteController@fotoCliente');
Route::get('/get_creditos_cliente', 'App\Http\Controllers\ClienteController@getCreditosCliente');
Route::get('/get_cuotas_planes', 'App\Http\Controllers\ClienteController@getCuotasPlanes');
Route::get('/get_asesores', 'App\Http\Controllers\ClienteController@getAsesores');
Route::get('/get_cantidad_clientes', 'App\Http\Controllers\ClienteController@getCantidadClientes');
Route::get('/cantidades_clientes', 'App\Http\Controllers\ClienteController@cantidadesClientes');
Route::get('/cliente/ficha-completa/{id}', [ClienteController::class, 'getFichaCompleta']);
Route::get('/cliente/reporte', [ClienteController::class, 'imprimirReporteClientes']);
Route::get('/get_cliente_info', [ClienteController::class, 'getClienteInfo']);


// solicitud
Route::get('/solicitud', 'App\Http\Controllers\SolicitudController@index')->middleware('auth');
Route::get('/get_solicitudes', 'App\Http\Controllers\SolicitudController@getSolicitudes');
Route::get('/get_garantias', 'App\Http\Controllers\SolicitudController@getGarantias');
Route::get('/activar_solicitud', 'App\Http\Controllers\SolicitudController@activarSolicitud');
Route::get('/desactivar_solicitud', 'App\Http\Controllers\SolicitudController@desactivarSolicitud');
Route::post('/guardar_observacion', 'App\Http\Controllers\SolicitudController@guardarObservacion');
Route::post('/save_solicitud', 'App\Http\Controllers\SolicitudController@save');
Route::get('/get_solicitud_respaldo', 'App\Http\Controllers\SolicitudController@getSolicitudRespaldo');
Route::post('/modify_solicitud', 'App\Http\Controllers\SolicitudController@modify');
Route::post('/solicitud_garantia_imagenes', 'App\Http\Controllers\SolicitudController@guardarImagenes');
Route::post('/guardar_imagen', 'App\Http\Controllers\SolicitudController@guardarImagenIndividual');
Route::get('/get_imagenes_garantia', 'App\Http\Controllers\SolicitudController@getImagenesGarantia');
Route::get('/eliminar_imagen', 'App\Http\Controllers\SolicitudController@eliminarImagen');
Route::get('/lista_cuotas_pdf', 'App\Http\Controllers\SolicitudController@ListaCuotasPdf');
Route::post('/save_planpagos_cuotas', 'App\Http\Controllers\SolicitudController@savePlanPagosCuotas');
Route::get('/imprimir_cuotas_simulacion', 'App\Http\Controllers\SolicitudController@imprimirCuotasSimulacion');
Route::get('/reporte_hoja_aprobacion', 'App\Http\Controllers\SolicitudController@reporteHojaAprobacion');
Route::get('/reporte_hoja_solicitud', 'App\Http\Controllers\SolicitudController@reporteHojaSolicitud');

Route::post('/guardar_garantias_imagenes', 'App\Http\Controllers\SolicitudController@guardarGarantiasImagenes');

Route::get('/plan_pago', 'App\Http\Controllers\PlanPagoController@index')->middleware('auth');
Route::get('/get_planespago', 'App\Http\Controllers\PlanPagoController@getPlanesPago');
Route::get('/get_planespago_caja', 'App\Http\Controllers\PlanPagoController@getPlanesPagoCaja');


Route::get('/get_cuotas_plan', 'App\Http\Controllers\PlanPagoController@getCuotasPlan');

Route::post('/anular_planpago', 'App\Http\Controllers\PlanPagoController@anularPlanPago');
Route::post('/activar_planpago', 'App\Http\Controllers\PlanPagoController@activarPlanPago');

Route::get('/consulta_plan_pago_vigente', 'App\Http\Controllers\PlanPagoController@consultarPlanPagoCuotasCanceladas');
Route::get('/lista_cuotas_planpago_pdf', 'App\Http\Controllers\PlanPagoController@ListaCuotasPlanPagoPdf');
Route::post('/save_amortizacion', 'App\Http\Controllers\PlanPagoController@saveAmortizacion');


Route::post('/save_amortizacion_nuevo', 'App\Http\Controllers\PlanPagoController@saveAmortizacionNuevo');

Route::post('/liquidar_deuda', 'App\Http\Controllers\PlanPagoController@liquidarDeuda');
Route::get('/generar_contrato', 'App\Http\Controllers\PlanPagoController@generarContrato');
Route::get('/get_cantidad_creditos', 'App\Http\Controllers\PlanPagoController@getCantidadCreditos');
Route::get('/get_planes_pago_ligados', 'App\Http\Controllers\PlanPagoController@getPlanesPagoLigados');


Route::get('/get_amortizaciones_plan', 'App\Http\Controllers\PlanPagoController@getAmortizacionesPlan');
Route::get('/get_amortizaciones', 'App\Http\Controllers\PlanPagoController@getAmortizaciones');

// obteniendo amortizaciones de cuotas
Route::get('/listar_amortizaciones_cuotas', 'App\Http\Controllers\PlanPagoController@listarAmortizaciones');
// para solicitud
Route::get('/listar_cuotas_plan_reprogramacion', 'App\Http\Controllers\SolicitudController@listarCuotasPlanReprogramacion');
Route::get('/listar_amortizaciones_cuotas_planpago', 'App\Http\Controllers\PlanPagoController@listarAmortizacionesPlanPago');


// pago
Route::post('/save_pago', 'App\Http\Controllers\PagoController@save');
Route::post('/pagar_cuotas', 'App\Http\Controllers\PagoController@pagarCuotas');
Route::get('/get_pago', 'App\Http\Controllers\PagoController@getPago');
Route::post('/anular_pago', 'App\Http\Controllers\PagoController@anularPago');

Route::get('/get_pagos', 'App\Http\Controllers\PagoController@getPagos');
Route::get('/get_pagos_lista_cuotas', 'App\Http\Controllers\PagoController@getPagosListaCuotas');
Route::get('/get_pagos_lista_anulados', 'App\Http\Controllers\PagoController@getPagosListaAnulados');
Route::get('/get_pagos_lista_cuotas_total', 'App\Http\Controllers\PagoController@getPagosListaCuotasTotal');
Route::post('/fotoRespaldoPago', 'App\Http\Controllers\PagoController@imagenRespaldo');

// busqueda por fechas
Route::get('/get_pagos_fecha', 'App\Http\Controllers\PagoController@getPagosFecha');
Route::get('/get_pagos_lista_cuotas_fecha', 'App\Http\Controllers\PagoController@getPagosListaCuotasFecha');
Route::get('/get_pagos_lista_anulados_fecha', 'App\Http\Controllers\PagoController@getPagosListaAnuladosFecha');
Route::get('/get_pagos_lista_cuotas_total_fecha', 'App\Http\Controllers\PagoController@getPagosListaCuotasTotalFecha');
Route::get('/imprimir/recibo/{codigo_transaccion}', 'App\Http\Controllers\PagoController@generarTicketPago');




// administracion
Route::get('/cantidad_clientes', 'App\Http\Controllers\AdministracionController@cantidadClientes');
Route::get('/cantidad_solicitudes', 'App\Http\Controllers\AdministracionController@cantidadSolicitudes');
Route::get('/cantidad_planes', 'App\Http\Controllers\AdministracionController@cantidadPlanes');
Route::get('/datos_pagos_grafico', 'App\Http\Controllers\AdministracionController@datosGraficoPagos');
Route::get('/cantidad_creditos_x_usuario', 'App\Http\Controllers\AdministracionController@cantidadPlanesPagoUsuarios');

Route::get('/dashboard', [AdministracionController::class, 'index'])->name('dashboard');
Route::get('/cantidad_clientes', [AdministracionController::class, 'getCantidadClientes']);
Route::get('/cantidad_solicitudes', [AdministracionController::class, 'getCantidadSolicitudes']);
Route::get('/cantidad_planes', [AdministracionController::class, 'getCantidadPlanes']);
Route::get('/cantidad_creditos_x_usuario', [AdministracionController::class, 'getCreditosPorUsuario']);
Route::get('/datos_pagos_grafico', [AdministracionController::class, 'getDatosPagos']);
Route::get('/estadisticas_prestamos', [AdministracionController::class, 'getEstadisticasPrestamos']);
Route::get('/top_clientes', [AdministracionController::class, 'getTopClientes']);




//Respaldos
Route::post('/imagenRespaldo', 'App\Http\Controllers\RespaldoController@guardarRespaldo');
Route::post('/guardar_respaldos_imagenes', 'App\Http\Controllers\RespaldoController@guardarRespaldosImagenes');

Route::get('/get_respaldos', 'App\Http\Controllers\RespaldoController@getRespaldos');
Route::get('/delete_respaldo', 'App\Http\Controllers\RespaldoController@deleteRespaldo');

// Reportes
Route::get('/reportes', 'App\Http\Controllers\ReporteController@index')->middleware('auth');
Route::get('/reporte_listado_clientes', 'App\Http\Controllers\ReporteController@listadoClientes');

Route::get('/get_planes_pago_cliente', 'App\Http\Controllers\ReporteController@getPlanesPagoCliente');
Route::get('/reporte_planes_pago_cuotas_cliente', 'App\Http\Controllers\ReporteController@reportePlanesCuotasCliente');
Route::get('/reporte_general_solicitudes', 'App\Http\Controllers\ReporteController@reporteGeneralSolicitudes');
Route::get('/reporte_solicitudes_aprobadas', 'App\Http\Controllers\ReporteController@reporteSolicitudesAprobadas');
Route::get('/reporte_solicitudes_por_aprobar', 'App\Http\Controllers\ReporteController@reporteSolicitudesPorAprobar');
Route::get('/reporte_solicitudes_anuladas', 'App\Http\Controllers\ReporteController@reporteSolicitudesAnuladas');
Route::get('/reporte_solicitudes_por_usuario', 'App\Http\Controllers\ReporteController@reporteSolicitudesPorUsuario');
Route::get('/reporte_solicitudes_por_rango', 'App\Http\Controllers\ReporteController@reporteSolicitudesPorRango');
Route::get('/reporte_general_planes_pago', 'App\Http\Controllers\ReporteController@reporteGeneralPlanesPago');
Route::get('/reporte_planes_pago_en_proceso', 'App\Http\Controllers\ReporteController@reportePlanesPagoEnProceso');
Route::get('/reporte_planes_pago_cancelados', 'App\Http\Controllers\ReporteController@reportePlanesPagoCancelados');




//caja
Route::get('/caja', 'App\Http\Controllers\CajaController@index')->middleware('auth');
Route::get('/get_caja', 'App\Http\Controllers\CajaController@getCaja');
Route::get('/get_caja_fecha', 'App\Http\Controllers\CajaController@getCajaFecha');
Route::post('/caja/aperturar', 'App\Http\Controllers\CajaController@save');
Route::post('/close_caja', 'App\Http\Controllers\CajaController@closeCaja');
Route::get('/caja_abierta', 'App\Http\Controllers\CajaController@cajaAbierta');
Route::get('/get_pagos_caja', 'App\Http\Controllers\CajaController@getPagos');

Route::get('/get_pagos_caja_amortizaciones', 'App\Http\Controllers\CajaController@getPagosAmortizaciones');

Route::get('/exportar_pagos_pdf', 'App\Http\Controllers\CajaController@exportarPagosCaja');
Route::get('/exportar_pagos_amortizacion_pdf', 'App\Http\Controllers\CajaController@exportarPagosCajaAmortizacion');

Route::get('/exportar_ingresos_corrientes_pdf', 'App\Http\Controllers\CajaController@exportarIngresosCorrientesCaja');
Route::get('/exportar_gastos_corrientes_pdf', 'App\Http\Controllers\CajaController@exportarGastosCorrientesCaja');
Route::get('/get_planes_pago_sin_desembolso', 'App\Http\Controllers\CajaController@getPlanesPagoSinDesembolsar');
Route::get('/get_planes_pago_sin_pago_adm', 'App\Http\Controllers\CajaController@getPlanesPagoSinPagoAdm');
Route::post('/actualizar_desembolso', 'App\Http\Controllers\CajaController@actualizarDesembolso');
Route::post('/guardar_pago_adm', 'App\Http\Controllers\CajaController@guardarPagoAdministrativo');
Route::get('/reporte_cajas_fecha', 'App\Http\Controllers\CajaController@reporteCajasFecha');


Route::get('/get_desembolsos', 'App\Http\Controllers\CajaController@getDesembolsos');

Route::get('/exportar_desembolsos_caja_pdf', 'App\Http\Controllers\CajaController@exportarDesembolsosCaja');

Route::get('/generar_comprobante_cliente', 'App\Http\Controllers\CajaController@generarComprobanteCliente');
Route::get('/get_comprobante_cliente_data', 'App\Http\Controllers\CajaController@getComprobanteClienteData');

// rutas submenus
Route::get('/historial_desembolsos_pagos', 'App\Http\Controllers\CajaController@historialDesembolsosPagos');
Route::get('/historial_desembolsos_pagos_listado', 'App\Http\Controllers\CajaController@historialDesembolsosPagosListado');
Route::post('/anular_desembolso', 'App\Http\Controllers\CajaController@anularDesembolso');


Route::get('/historial_ingresos', 'App\Http\Controllers\CajaController@historialIngresos');
Route::get('/historial_ingresos_listado', 'App\Http\Controllers\IngresoController@historialIngresosListado');
Route::get('/historial_ingresos_listado_caja', 'App\Http\Controllers\IngresoController@historialIngresosListadoCaja');
Route::post('/anular_ingreso', 'App\Http\Controllers\IngresoController@anularIngreso');

Route::get('/historial_gastos', 'App\Http\Controllers\CajaController@historialGastos');
Route::get('/historial_gastos_listado', 'App\Http\Controllers\GastoController@historialGastosListado');
Route::get('/historial_egresos_listado_caja', 'App\Http\Controllers\GastoController@historialEgresosListadoCaja');
Route::post('/anular_gasto', 'App\Http\Controllers\GastoController@anularGasto');





Route::get('/historial_pagos', 'App\Http\Controllers\CajaController@historialPagos');
Route::get('/movimientos_caja', 'App\Http\Controllers\CajaController@getMovimientosCaja');


// gasto
Route::post('/save_gasto', 'App\Http\Controllers\GastoController@save');
Route::get('/get_gastos_corrientes', 'App\Http\Controllers\GastoController@getGastosCorrientes');

// ingreso
Route::post('/save_ingreso', 'App\Http\Controllers\IngresoController@save');
Route::get('/get_ingresos_corrientes', 'App\Http\Controllers\IngresoController@getIngresosCorrientes');


// Estado resultados
Route::get('/estado_resultados', 'App\Http\Controllers\EstadoResultadosController@index');
Route::get('/get_pagos_administrativos', 'App\Http\Controllers\EstadoResultadosController@getPagosAdministrativos');
Route::get('/get_monto_intereses', 'App\Http\Controllers\EstadoResultadosController@getMontoIntereses');
Route::get('/get_monto_multas', 'App\Http\Controllers\EstadoResultadosController@getMontoMultas');
Route::get('/get_monto_otros_ingresos', 'App\Http\Controllers\EstadoResultadosController@getMontoOtrosIngresos');
Route::get('/get_monto_total_egresos', 'App\Http\Controllers\EstadoResultadosController@getMontoTotalEgresos');


// Codeudores
Route::get('/codeudores', 'App\Http\Controllers\CodeudorController@index');
Route::post('/save_codeudor', 'App\Http\Controllers\CodeudorController@save');
Route::get('/get_codeudores', 'App\Http\Controllers\CodeudorController@getCodeudores');
Route::get('/get_direcciones_telefono_codeudor', 'App\Http\Controllers\CodeudorController@getDireccionesTelefonos');
Route::post('/modify_codeudor', 'App\Http\Controllers\CodeudorController@modify');
Route::get('/activar_codeudor', 'App\Http\Controllers\CodeudorController@activar');
Route::get('/desactivar_codeudor', 'App\Http\Controllers\CodeudorController@desactivar');
Route::get('/codeudores_pdf', 'App\Http\Controllers\CodeudorController@codeudoresPdf2');
Route::post('/fotoCodeudor', 'App\Http\Controllers\CodeudorController@fotoCodeudor');
Route::get('/get_codeudores_sin', 'App\Http\Controllers\CodeudorController@getCodeudoresSin');
Route::get('/get_codeudores_solicitud', 'App\Http\Controllers\CodeudorController@getCodeudorSolicitud');
Route::get('/get_codeudores_solicitudes', 'App\Http\Controllers\CodeudorController@getCodeudoresSolicitudes');
Route::get('/get_actividades', 'App\Http\Controllers\CodeudorController@getActividades');
Route::get('/codeudor/reporte', [CodeudorController::class, 'imprimirReporteCodeudores']);

// Consultas Financieras
Route::get('/consultas_financieras', 'App\Http\Controllers\ConsultaFinancieraController@index')->middleware('auth');


// exporte excels
Route::get('/export-planes_pago', 'App\Http\Controllers\ExportController@exportPlanPago');




// VISTAS REPORTES

Route::get('/index_rep_extracto', 'App\Http\Controllers\VistasReporteController@indexExtracto');
Route::get('/get_clientes_rep', 'App\Http\Controllers\VistasReporteController@getClientesRep');
Route::get('/get_creditos_rep', 'App\Http\Controllers\VistasReporteController@getCreditosRep');
Route::get('/rep_extracto_credito', 'App\Http\Controllers\VistasReporteController@generarReporteExtracto');

Route::get('/hist_credito_mora', 'App\Http\Controllers\VistasReporteController@indexHistCreditoMora');

Route::get('/rep_creditos_mora', 'App\Http\Controllers\VistasReporteController@generarReporteCreditosMora');

Route::get('/cliente_mora', 'App\Http\Controllers\VistasReporteController@indexClientesMora');

Route::get('/rep_clientes_mora', 'App\Http\Controllers\VistasReporteController@generarReporteClientesMora');


Route::get('/index_rep_pagos_realizados', 'App\Http\Controllers\VistasReporteController@indexPagosRealizados');

Route::get('/generar_reporte_pagos_realizados', 'App\Http\Controllers\VistasReporteController@generarReportePagosRealizados');

Route::get('/index_pagos_programados', 'App\Http\Controllers\VistasReporteController@indexPagosProgramados');

Route::get('/generar_reporte_pagos_programados', 'App\Http\Controllers\VistasReporteController@generarReportesPagosProgramados');

Route::get('/index_movimientos_credito', 'App\Http\Controllers\VistasReporteController@indexMovimientosCredito');

Route::get('/reporte_extracto_movimientos', 'App\Http\Controllers\VistasReporteController@reporteExtractoMovimientos');

Route::get('/index_porcentajes_pagos', 'App\Http\Controllers\VistasReporteController@indexPorcentajesPagos');
Route::get('/rep_porcentajes_creditos', 'App\Http\Controllers\VistasReporteController@reportePorcentajesCreditos');

Route::get('/index_desembolsos', 'App\Http\Controllers\VistasReporteController@indexDesembolsos');
Route::get('/reporte_desembolsos', 'App\Http\Controllers\VistasReporteController@reporteDesembolsos');

Route::get('/index_desembolsos_oficial', 'App\Http\Controllers\VistasReporteController@indexDesembolsosOficial');
Route::get('/reporte_desembolsos_oficial', 'App\Http\Controllers\VistasReporteController@reporteDesembolsosOficial');


Route::get('/index_desembolsos_pendientes', 'App\Http\Controllers\VistasReporteController@indexDesembolsosPendientes');
Route::get('/reporte_desembolsos_pendientes', 'App\Http\Controllers\VistasReporteController@reporteDesembolsosPendientes');


Route::get('/configuracion', 'App\Http\Controllers\ConfiguracionController@indexConfiguracion');

// MOTIVOS INGRESOS
Route::get('/get_motivos_ingresos', 'App\Http\Controllers\ConfiguracionController@getMotivosIngresos');
Route::post('/guardar_motivo_ingreso', 'App\Http\Controllers\ConfiguracionController@guardarMotivoIngreso');
Route::post('/modificar_motivo_ingreso', 'App\Http\Controllers\ConfiguracionController@modificarMotivoIngreso');
Route::post('/desactivar_motivo_ingreso', 'App\Http\Controllers\ConfiguracionController@desactivarMotivoIngreso');
Route::post('/activar_motivo_ingreso', 'App\Http\Controllers\ConfiguracionController@activarMotivoIngreso');


// MOTIVOS GASTOS
Route::get('/get_motivos_gastos', 'App\Http\Controllers\ConfiguracionController@getMotivosGastos');
Route::post('/guardar_motivo_gasto', 'App\Http\Controllers\ConfiguracionController@guardarMotivoGasto');
Route::post('/modificar_motivo_gasto', 'App\Http\Controllers\ConfiguracionController@modificarMotivoGasto');
Route::post('/desactivar_motivo_gasto', 'App\Http\Controllers\ConfiguracionController@desactivarMotivoGasto');
Route::post('/activar_motivo_gasto', 'App\Http\Controllers\ConfiguracionController@activarMotivoGasto');


// Boveda

Route::get('/boveda', 'App\Http\Controllers\BovedaController@indexBoveda');
Route::get('/get_movimientos_boveda', 'App\Http\Controllers\BovedaController@getMovimientosBoveda');
Route::get('/get_boveda', 'App\Http\Controllers\BovedaController@getBoveda');
Route::post('/ingresar_boveda', 'App\Http\Controllers\BovedaController@ingresarBoveda');
Route::post('/retirar_boveda', 'App\Http\Controllers\BovedaController@retirarBoveda');
Route::post('/aperturar_boveda', 'App\Http\Controllers\BovedaController@aperturarBoveda');

// caja
Route::get('/lista_cuotas_pdf_caja', 'App\Http\Controllers\CajaController@ListaCuotasPdfCaja');


Route::get('/get_planes_pago_general', 'App\Http\Controllers\ReporteController@getPlanesPagoGeneral');
Route::get('/get_planes_pago_general_pdf', 'App\Http\Controllers\ReporteController@getPlanesPagoGeneralPdf');
Route::get('/get_solicitudes_general', 'App\Http\Controllers\ReporteController@getSolicitudesGeneral');
Route::get('/get_solicitudes_general_pdf', 'App\Http\Controllers\ReporteController@getSolicitudesGeneralPdf');

Route::post('/reportes/plan-pago-reprogramado-pdf', [ReporteController::class, 'generarPlanReprogramadoPDF']);

// Route::post('/solicitud/registrar-reprogramacion', [SolicitudController::class, 'registrarReprogramacion']);
Route::post('/solicitud/registrar-especial', [SolicitudController::class, 'registrarSolicitudEspecial']);

Route::get('/reprogramacion/calcular/{id_plan_pago}', [SolicitudController::class, 'calcularMonto']);
Route::get('/solicitud/datos-originales/{id_reprogramacion}', [SolicitudController::class, 'obtenerDatosOriginales']);
Route::post('/solicitud/aprobar-reprogramacion-final', [SolicitudController::class, 'aprobarReprogramacionFinal']);
Route::get('/solicitud/detalle-completo/{id_solicitud}', [SolicitudController::class, 'getDetalleCompletoSolicitud']);
Route::get('/get_orden_pago_reprogramacion', [SolicitudController::class, 'getOrdenPagoReprogramacion']);


Route::get('/historial-pagos', [HistorialPagosController::class, 'index']);
Route::post('/pagos/anular/{id}', [HistorialPagosController::class, 'anular']);
Route::get('/historial-pagos/detalles/{codigo}', [HistorialPagosController::class, 'show']);


Route::get('/caja/get-ordenes-reprogramacion', [CajaController::class, 'getOrdenesReprogramacion']);
Route::get('/verificar-boveda', [CajaController::class, 'verificarBoveda']);


Route::middleware(['auth'])->group(function () {
    Route::get('/perfil', [PerfilController::class, 'index']);
    Route::get('/perfil/get_datos', [PerfilController::class, 'getPerfil']);
    Route::post('/perfil/update_info', [PerfilController::class, 'updateInformacion']);
    Route::post('/perfil/update_password', [PerfilController::class, 'updatePassword']);

    // caja nuevo
    Route::get('/caja/movimientos/listado', [CajaMovimientosController::class, 'getListado']);
    Route::get('/caja/movimientos/reporte-pdf', [CajaMovimientosController::class, 'generarReporteLista']);
});

























































