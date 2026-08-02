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
use App\Http\Controllers\ConsultaFinancieraController;
use App\Http\Controllers\SocioController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\PlanPagoController;
use App\Http\Controllers\SesionController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| CONTROL DE ACCESO
| - Rutas públicas: solo login.
| - Todo lo demás requiere sesión (grupo 'auth').
| - Fase C: la app es una SPA. Cualquier ruta GET que no matchee algo
|   definido arriba cae en la ruta catch-all al final de este archivo, que
|   sirve el shell (resources/views/app.blade.php). Vue Router decide qué
|   módulo mostrar según la URL, y su guard de navegación (routes.js) exige
|   el permiso correspondiente — mismo nombre de permiso que se usaba antes
|   en el middleware 'permiso:<nombre>' de cada página.
| - Las ACCIONES DE ESCRITURA (crear/editar/anular/activar/desactivar) siguen
|   protegidas server-side con 'permiso:<nombre>' — esa es la seguridad real,
|   independiente de lo que decida mostrar el router en el cliente.
| - Los endpoints de LECTURA (get_x, listados, reportes) quedan bajo 'auth'
|   (nadie sin sesión los consume). Muchos son compartidos entre módulos, por
|   eso no se restringen individualmente por permiso.
| - Las acciones que cambian estado usan POST (no GET).
|
*/

// ==========================================================================
//  RUTAS PÚBLICAS (sin sesión)
// ==========================================================================
Route::get('/', 'App\Http\Controllers\LoginController@irLogin')->name('ir_login');
Route::post('/login_process', 'App\Http\Controllers\LoginController@loginProcess');


// ==========================================================================
//  RUTAS AUTENTICADAS (requieren sesión)
// ==========================================================================
Route::middleware('auth')->group(function () {

    // /administracion es el destino post-login (redirect()->intended) y
    // sirve el shell SPA. Accesible a cualquier usuario autenticado (el
    // menú decide qué módulos mostrar según sus permisos). Por eso NO
    // lleva 'permiso'.
    Route::get('/administracion', 'App\Http\Controllers\LoginController@administracion')->name('administracion');
    Route::post('/administracion', 'App\Http\Controllers\LoginController@logout')->name('logout');

    // Bootstrap de sesión para el cliente SPA (Vue Router): datos del
    // usuario + permisos de su rol, para armar el menú y los guards.
    Route::get('/me', [SesionController::class, 'me']);

    // permisos (lectura, usados por el módulo de roles)
    Route::get('/get_permisos', 'App\Http\Controllers\PermisoController@getPermisos');
    Route::get('/get_permisos_rol', 'App\Http\Controllers\PermisoController@getPermisoRol');

    // ==================== ROLES  [permiso: roles] ====================
    Route::get('/get_roles', 'App\Http\Controllers\RolController@getRoles');
    Route::get('/get_roles_usuarios', 'App\Http\Controllers\RolController@getRolesUsuarios');
    Route::post('/activar_rol', 'App\Http\Controllers\RolController@activar')->middleware('permiso:roles');
    Route::post('/desactivar_rol', 'App\Http\Controllers\RolController@desactivar')->middleware('permiso:roles');
    Route::post('/save_rol', 'App\Http\Controllers\RolController@save')->middleware('permiso:roles');
    Route::post('/modify_rol', 'App\Http\Controllers\RolController@modify')->middleware('permiso:roles');

    // ==================== USUARIOS  [permiso: usuarios] ====================
    Route::get('/get_usuarios', 'App\Http\Controllers\UsuarioController@getUsuarios');
    Route::get('/get_usuarios_sin', 'App\Http\Controllers\UsuarioController@getUsuariosSin');
    Route::post('/activar_usuario', 'App\Http\Controllers\UsuarioController@activar')->middleware('permiso:usuarios');
    Route::post('/desactivar_usuario', 'App\Http\Controllers\UsuarioController@desactivar')->middleware('permiso:usuarios');
    Route::post('/save_usuario', 'App\Http\Controllers\UsuarioController@save')->middleware('permiso:usuarios');
    Route::post('/modify_usuario', 'App\Http\Controllers\UsuarioController@modify')->middleware('permiso:usuarios');

    // ==================== MI EMPRESA  [permiso: informacion] ====================
    Route::post('/modify_miempresa', 'App\Http\Controllers\MiEmpresaController@modify')->middleware('permiso:informacion');
    Route::get('/get_mi_empresa', 'App\Http\Controllers\MiEmpresaController@getMiEmpresa');

    // ==================== CLIENTE  [permiso: cliente] ====================
    Route::get('/get_clientes', 'App\Http\Controllers\ClienteController@getClientes');
    Route::get('/get_clientes_paginate', 'App\Http\Controllers\ClienteController@getClientesPaginate');
    Route::get('/get_clientes_sin', 'App\Http\Controllers\ClienteController@getClientesSin');
    Route::get('/get_direcciones_telefono', 'App\Http\Controllers\ClienteController@getDireccionesTelefonos');
    Route::post('/activar_cliente', 'App\Http\Controllers\ClienteController@activar')->middleware('permiso:cliente');
    Route::post('/desactivar_cliente', 'App\Http\Controllers\ClienteController@desactivar')->middleware('permiso:cliente');
    Route::post('/save_cliente', 'App\Http\Controllers\ClienteController@save')->middleware('permiso:cliente');
    Route::post('/modify_cliente', 'App\Http\Controllers\ClienteController@modify')->middleware('permiso:cliente');
    Route::get('/clientes_pdf', 'App\Http\Controllers\ClienteController@clientesPdf2');
    Route::post('/fotoCliente', 'App\Http\Controllers\ClienteController@fotoCliente')->middleware('permiso:cliente');
    Route::get('/get_creditos_cliente', 'App\Http\Controllers\ClienteController@getCreditosCliente');
    Route::get('/get_cuotas_planes', 'App\Http\Controllers\ClienteController@getCuotasPlanes');
    Route::get('/get_asesores', 'App\Http\Controllers\ClienteController@getAsesores');
    Route::get('/get_cantidad_clientes', 'App\Http\Controllers\ClienteController@getCantidadClientes');
    Route::get('/cantidades_clientes', 'App\Http\Controllers\ClienteController@cantidadesClientes');
    Route::get('/cliente/ficha-completa/{id}', [ClienteController::class, 'getFichaCompleta']);
    Route::get('/cliente/reporte', [ClienteController::class, 'imprimirReporteClientes']);
    Route::get('/get_cliente_info', [ClienteController::class, 'getClienteInfo']);

    // ==================== SOLICITUD  [permiso: solicitudprestamos] ====================
    Route::get('/get_solicitudes', 'App\Http\Controllers\SolicitudController@getSolicitudes');
    Route::get('/get_garantias', 'App\Http\Controllers\SolicitudController@getGarantias');
    Route::post('/activar_solicitud', 'App\Http\Controllers\SolicitudController@activarSolicitud')->middleware('permiso:solicitudprestamos');
    Route::post('/desactivar_solicitud', 'App\Http\Controllers\SolicitudController@desactivarSolicitud')->middleware('permiso:solicitudprestamos');
    Route::post('/guardar_observacion', 'App\Http\Controllers\SolicitudController@guardarObservacion')->middleware('permiso:solicitudprestamos');
    Route::post('/save_solicitud', 'App\Http\Controllers\SolicitudController@save')->middleware('permiso:solicitudprestamos');
    Route::get('/get_solicitud_respaldo', 'App\Http\Controllers\SolicitudController@getSolicitudRespaldo');
    Route::post('/modify_solicitud', 'App\Http\Controllers\SolicitudController@modify')->middleware('permiso:solicitudprestamos');
    Route::post('/solicitud_garantia_imagenes', 'App\Http\Controllers\SolicitudController@guardarImagenes')->middleware('permiso:solicitudprestamos');
    Route::post('/guardar_imagen', 'App\Http\Controllers\SolicitudController@guardarImagenIndividual')->middleware('permiso:solicitudprestamos');
    Route::get('/get_imagenes_garantia', 'App\Http\Controllers\SolicitudController@getImagenesGarantia');
    Route::post('/eliminar_imagen', 'App\Http\Controllers\SolicitudController@eliminarImagen')->middleware('permiso:solicitudprestamos');
    Route::get('/lista_cuotas_pdf', 'App\Http\Controllers\SolicitudController@ListaCuotasPdf');
    Route::post('/save_planpagos_cuotas', 'App\Http\Controllers\SolicitudController@savePlanPagosCuotas')->middleware('permiso:solicitudprestamos');
    Route::get('/imprimir_cuotas_simulacion', 'App\Http\Controllers\SolicitudController@imprimirCuotasSimulacion');
    Route::get('/reporte_hoja_aprobacion', 'App\Http\Controllers\SolicitudController@reporteHojaAprobacion');
    Route::get('/reporte_hoja_solicitud', 'App\Http\Controllers\SolicitudController@reporteHojaSolicitud');
    Route::post('/guardar_garantias_imagenes', 'App\Http\Controllers\SolicitudController@guardarGarantiasImagenes')->middleware('permiso:solicitudprestamos');
    Route::post('/solicitud/registrar-especial', [SolicitudController::class, 'registrarSolicitudEspecial'])->middleware('permiso:solicitudprestamos');
    Route::post('/solicitud/aprobar-reprogramacion-final', [SolicitudController::class, 'aprobarReprogramacionFinal'])->middleware('permiso:solicitudprestamos');
    Route::get('/reprogramacion/calcular/{id_plan_pago}', [SolicitudController::class, 'calcularMonto']);
    Route::get('/solicitud/datos-originales/{id_reprogramacion}', [SolicitudController::class, 'obtenerDatosOriginales']);
    Route::get('/solicitud/detalle-completo/{id_solicitud}', [SolicitudController::class, 'getDetalleCompletoSolicitud']);
    Route::get('/get_orden_pago_reprogramacion', [SolicitudController::class, 'getOrdenPagoReprogramacion']);
    Route::get('/listar_cuotas_plan_reprogramacion', 'App\Http\Controllers\SolicitudController@listarCuotasPlanReprogramacion');

    // ==================== PLAN DE PAGOS  [permiso: planpagos] ====================
    Route::get('/get_planespago', 'App\Http\Controllers\PlanPagoController@getPlanesPago');
    Route::get('/get_planespago_caja', 'App\Http\Controllers\PlanPagoController@getPlanesPagoCaja');
    Route::get('/get_cuotas_plan', 'App\Http\Controllers\PlanPagoController@getCuotasPlan');
    Route::post('/anular_planpago', 'App\Http\Controllers\PlanPagoController@anularPlanPago')->middleware('permiso:planpagos');
    Route::post('/activar_planpago', 'App\Http\Controllers\PlanPagoController@activarPlanPago')->middleware('permiso:planpagos');
    Route::get('/consulta_plan_pago_vigente', 'App\Http\Controllers\PlanPagoController@consultarPlanPagoCuotasCanceladas');
    Route::get('/lista_cuotas_planpago_pdf', 'App\Http\Controllers\PlanPagoController@ListaCuotasPlanPagoPdf');
    Route::post('/save_amortizacion', 'App\Http\Controllers\PlanPagoController@saveAmortizacion')->middleware('permiso:planpagos');
    Route::post('/save_amortizacion_nuevo', 'App\Http\Controllers\PlanPagoController@saveAmortizacionNuevo')->middleware('permiso:planpagos');
    Route::post('/liquidar_deuda', 'App\Http\Controllers\PlanPagoController@liquidarDeuda')->middleware('permiso:planpagos');
    Route::get('/generar_contrato', 'App\Http\Controllers\PlanPagoController@generarContrato');
    Route::get('/get_cantidad_creditos', 'App\Http\Controllers\PlanPagoController@getCantidadCreditos');
    Route::get('/get_planes_pago_ligados', 'App\Http\Controllers\PlanPagoController@getPlanesPagoLigados');
    Route::get('/get_amortizaciones_plan', 'App\Http\Controllers\PlanPagoController@getAmortizacionesPlan');
    Route::get('/get_amortizaciones', 'App\Http\Controllers\PlanPagoController@getAmortizaciones');
    Route::get('/listar_amortizaciones_cuotas', 'App\Http\Controllers\PlanPagoController@listarAmortizaciones');
    Route::get('/listar_amortizaciones_cuotas_planpago', 'App\Http\Controllers\PlanPagoController@listarAmortizacionesPlanPago');
    Route::get('/get_pagos_cuota', [PlanPagoController::class, 'getPagosPorCuota']);

    // ==================== PAGOS (cobros en caja)  [permiso: controlcaja] ====================
    Route::post('/save_pago', 'App\Http\Controllers\PagoController@save')->middleware('permiso:controlcaja');
    Route::post('/pagar_cuotas', 'App\Http\Controllers\PagoController@pagarCuotas')->middleware('permiso:controlcaja');
    Route::get('/get_pago', 'App\Http\Controllers\PagoController@getPago');
    Route::post('/anular_pago', 'App\Http\Controllers\PagoController@anularPago')->middleware('permiso:controlcaja');
    Route::get('/get_pagos', 'App\Http\Controllers\PagoController@getPagos');
    Route::get('/get_pagos_lista_cuotas', 'App\Http\Controllers\PagoController@getPagosListaCuotas');
    Route::get('/get_pagos_lista_anulados', 'App\Http\Controllers\PagoController@getPagosListaAnulados');
    Route::get('/get_pagos_lista_cuotas_total', 'App\Http\Controllers\PagoController@getPagosListaCuotasTotal');
    Route::post('/fotoRespaldoPago', 'App\Http\Controllers\PagoController@imagenRespaldo')->middleware('permiso:controlcaja');
    Route::get('/get_pagos_fecha', 'App\Http\Controllers\PagoController@getPagosFecha');
    Route::get('/get_pagos_lista_cuotas_fecha', 'App\Http\Controllers\PagoController@getPagosListaCuotasFecha');
    Route::get('/get_pagos_lista_anulados_fecha', 'App\Http\Controllers\PagoController@getPagosListaAnuladosFecha');
    Route::get('/get_pagos_lista_cuotas_total_fecha', 'App\Http\Controllers\PagoController@getPagosListaCuotasTotalFecha');
    Route::get('/imprimir/recibo/{codigo_transaccion}', 'App\Http\Controllers\PagoController@generarTicketPago');

    // ==================== DASHBOARD  [permiso: paneladministracion] ====================
    // Nota: se retiró '/dashboard' (AdministracionController@index) — el método
    // 'index' no existe en ese controlador; era una ruta rota y sin uso.
    Route::get('/cantidad_clientes', [AdministracionController::class, 'getCantidadClientes']);
    Route::get('/cantidad_solicitudes', [AdministracionController::class, 'getCantidadSolicitudes']);
    Route::get('/cantidad_planes', [AdministracionController::class, 'getCantidadPlanes']);
    Route::get('/cantidad_creditos_x_usuario', [AdministracionController::class, 'getCreditosPorUsuario']);
    Route::get('/datos_pagos_grafico', [AdministracionController::class, 'getDatosPagos']);
    Route::get('/estadisticas_prestamos', [AdministracionController::class, 'getEstadisticasPrestamos']);
    Route::get('/top_clientes', [AdministracionController::class, 'getTopClientes']);

    // ==================== RESPALDOS (solicitud)  [permiso: solicitudprestamos] ====================
    Route::post('/imagenRespaldo', 'App\Http\Controllers\RespaldoController@guardarRespaldo')->middleware('permiso:solicitudprestamos');
    Route::post('/guardar_respaldos_imagenes', 'App\Http\Controllers\RespaldoController@guardarRespaldosImagenes')->middleware('permiso:solicitudprestamos');
    Route::get('/get_respaldos', 'App\Http\Controllers\RespaldoController@getRespaldos');
    Route::post('/delete_respaldo', 'App\Http\Controllers\RespaldoController@deleteRespaldo')->middleware('permiso:solicitudprestamos');

    // ==================== REPORTES  [permiso: reportes] ====================
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
    Route::get('/get_planes_pago_general', 'App\Http\Controllers\ReporteController@getPlanesPagoGeneral');
    Route::get('/get_planes_pago_general_pdf', 'App\Http\Controllers\ReporteController@getPlanesPagoGeneralPdf');
    Route::get('/get_solicitudes_general', 'App\Http\Controllers\ReporteController@getSolicitudesGeneral');
    Route::get('/get_solicitudes_general_pdf', 'App\Http\Controllers\ReporteController@getSolicitudesGeneralPdf');
    Route::post('/reportes/plan-pago-reprogramado-pdf', [ReporteController::class, 'generarPlanReprogramadoPDF']);

    // ==================== CAJA  [permiso: controlcaja] ====================
    Route::get('/get_caja', 'App\Http\Controllers\CajaController@getCaja');
    Route::get('/get_caja_fecha', 'App\Http\Controllers\CajaController@getCajaFecha');
    Route::post('/caja/aperturar', 'App\Http\Controllers\CajaController@save')->middleware('permiso:controlcaja');
    Route::post('/close_caja', 'App\Http\Controllers\CajaController@closeCaja')->middleware('permiso:controlcaja');
    Route::get('/caja_abierta', 'App\Http\Controllers\CajaController@cajaAbierta');
    Route::get('/get_pagos_caja', 'App\Http\Controllers\CajaController@getPagos');
    Route::get('/get_pagos_caja_amortizaciones', 'App\Http\Controllers\CajaController@getPagosAmortizaciones');
    Route::get('/exportar_pagos_pdf', 'App\Http\Controllers\CajaController@exportarPagosCaja');
    Route::get('/exportar_pagos_amortizacion_pdf', 'App\Http\Controllers\CajaController@exportarPagosCajaAmortizacion');
    Route::get('/exportar_ingresos_corrientes_pdf', 'App\Http\Controllers\CajaController@exportarIngresosCorrientesCaja');
    Route::get('/exportar_gastos_corrientes_pdf', 'App\Http\Controllers\CajaController@exportarGastosCorrientesCaja');
    Route::get('/get_planes_pago_sin_desembolso', 'App\Http\Controllers\CajaController@getPlanesPagoSinDesembolsar');
    Route::get('/get_planes_pago_sin_pago_adm', 'App\Http\Controllers\CajaController@getPlanesPagoSinPagoAdm');
    Route::post('/actualizar_desembolso', 'App\Http\Controllers\CajaController@actualizarDesembolso')->middleware('permiso:controlcaja');
    Route::post('/guardar_pago_adm', 'App\Http\Controllers\CajaController@guardarPagoAdministrativo')->middleware('permiso:controlcaja');
    Route::get('/reporte_cajas_fecha', 'App\Http\Controllers\CajaController@reporteCajasFecha');
    Route::get('/get_desembolsos', 'App\Http\Controllers\CajaController@getDesembolsos');
    Route::get('/exportar_desembolsos_caja_pdf', 'App\Http\Controllers\CajaController@exportarDesembolsosCaja');
    Route::get('/generar_comprobante_cliente', 'App\Http\Controllers\CajaController@generarComprobanteCliente');
    Route::get('/get_comprobante_cliente_data', 'App\Http\Controllers\CajaController@getComprobanteClienteData');
    Route::get('/lista_cuotas_pdf_caja', 'App\Http\Controllers\CajaController@ListaCuotasPdfCaja');
    Route::get('/caja/get-ordenes-reprogramacion', [CajaController::class, 'getOrdenesReprogramacion']);
    Route::get('/verificar-boveda', [CajaController::class, 'verificarBoveda']);
    Route::get('/caja/saldo-actual', [CajaController::class, 'getSaldoCajaActual']);
    Route::get('/caja/movimientos/listado', [CajaMovimientosController::class, 'getListado']);
    Route::get('/caja/movimientos/reporte-pdf', [CajaMovimientosController::class, 'generarReporteLista']);

    // caja - submenús de historial  [permiso: controlcaja]
    Route::get('/historial_desembolsos_pagos_listado', 'App\Http\Controllers\CajaController@historialDesembolsosPagosListado');
    Route::post('/anular_desembolso', 'App\Http\Controllers\CajaController@anularDesembolso')->middleware('permiso:controlcaja');
    Route::get('/historial_ingresos_listado', 'App\Http\Controllers\IngresoController@historialIngresosListado');
    Route::get('/historial_ingresos_listado_caja', 'App\Http\Controllers\IngresoController@historialIngresosListadoCaja');
    Route::post('/anular_ingreso', 'App\Http\Controllers\IngresoController@anularIngreso')->middleware('permiso:controlcaja');
    Route::get('/historial_gastos_listado', 'App\Http\Controllers\GastoController@historialGastosListado');
    Route::get('/historial_egresos_listado_caja', 'App\Http\Controllers\GastoController@historialEgresosListadoCaja');
    Route::post('/anular_gasto', 'App\Http\Controllers\GastoController@anularGasto')->middleware('permiso:controlcaja');
    Route::get('/movimientos_caja', 'App\Http\Controllers\CajaController@getMovimientosCaja');

    // gasto / ingreso corrientes  [permiso: controlcaja]
    Route::post('/save_gasto', 'App\Http\Controllers\GastoController@save')->middleware('permiso:controlcaja');
    Route::get('/get_gastos_corrientes', 'App\Http\Controllers\GastoController@getGastosCorrientes');
    Route::post('/save_ingreso', 'App\Http\Controllers\IngresoController@save')->middleware('permiso:controlcaja');
    Route::get('/get_ingresos_corrientes', 'App\Http\Controllers\IngresoController@getIngresosCorrientes');

    // Historial de pagos (anulación)  [permiso: controlcaja]
    Route::post('/pagos/anular/{id}', [HistorialPagosController::class, 'anular'])->middleware('permiso:controlcaja');
    Route::get('/historial-pagos/detalles/{codigo}', [HistorialPagosController::class, 'show']);

    // ==================== ESTADO RESULTADOS  [permiso: consultasfinancieras] ====================
    Route::get('/get_pagos_administrativos', 'App\Http\Controllers\EstadoResultadosController@getPagosAdministrativos');
    Route::get('/get_monto_intereses', 'App\Http\Controllers\EstadoResultadosController@getMontoIntereses');
    Route::get('/get_monto_multas', 'App\Http\Controllers\EstadoResultadosController@getMontoMultas');
    Route::get('/get_monto_otros_ingresos', 'App\Http\Controllers\EstadoResultadosController@getMontoOtrosIngresos');
    Route::get('/get_monto_total_egresos', 'App\Http\Controllers\EstadoResultadosController@getMontoTotalEgresos');

    // ==================== CODEUDORES  [permiso: codeudores] ====================
    Route::post('/save_codeudor', 'App\Http\Controllers\CodeudorController@save')->middleware('permiso:codeudores');
    Route::get('/get_codeudores', 'App\Http\Controllers\CodeudorController@getCodeudores');
    Route::get('/get_direcciones_telefono_codeudor', 'App\Http\Controllers\CodeudorController@getDireccionesTelefonos');
    Route::post('/modify_codeudor', 'App\Http\Controllers\CodeudorController@modify')->middleware('permiso:codeudores');
    Route::post('/activar_codeudor', 'App\Http\Controllers\CodeudorController@activar')->middleware('permiso:codeudores');
    Route::post('/desactivar_codeudor', 'App\Http\Controllers\CodeudorController@desactivar')->middleware('permiso:codeudores');
    Route::get('/codeudores_pdf', 'App\Http\Controllers\CodeudorController@codeudoresPdf2');
    Route::post('/fotoCodeudor', 'App\Http\Controllers\CodeudorController@fotoCodeudor')->middleware('permiso:codeudores');
    Route::get('/get_codeudores_sin', 'App\Http\Controllers\CodeudorController@getCodeudoresSin');
    Route::get('/get_codeudores_solicitud', 'App\Http\Controllers\CodeudorController@getCodeudorSolicitud');
    Route::get('/get_codeudores_solicitudes', 'App\Http\Controllers\CodeudorController@getCodeudoresSolicitudes');
    Route::get('/get_actividades', 'App\Http\Controllers\CodeudorController@getActividades');
    Route::get('/codeudor/reporte', [CodeudorController::class, 'imprimirReporteCodeudores']);

    // ==================== CONSULTAS FINANCIERAS  [permiso: consultasfinancieras] ====================
    Route::get('/libro-mayor', [ConsultaFinancieraController::class, 'getLibroMayor']);
    Route::get('/consulta-financiera/info-credito', [ConsultaFinancieraController::class, 'getInfoCreditoAsociado']);
    Route::get('/reportes/libro-mayor', [ConsultaFinancieraController::class, 'imprimirLibroMayor']);
    Route::get('/reportes/ingresos', [ConsultaFinancieraController::class, 'imprimirReporteIngresos']);
    Route::get('/reportes/egresos', [ConsultaFinancieraController::class, 'imprimirReporteEgresos']);
    Route::get('/reportes/libro-mayor/excel', [ConsultaFinancieraController::class, 'exportarExcelLibroMayor']);
    Route::get('/reportes/ingresos/excel', [ConsultaFinancieraController::class, 'exportarExcelIngresos']);
    Route::get('/reportes/egresos/excel', [ConsultaFinancieraController::class, 'exportarExcelEgresos']);

    // exporte excels
    Route::get('/export-planes_pago', 'App\Http\Controllers\ExportController@exportPlanPago');

    // ==================== VISTAS REPORTES  [permiso: reportes] ====================
    Route::get('/get_clientes_rep', 'App\Http\Controllers\VistasReporteController@getClientesRep');
    Route::get('/get_creditos_rep', 'App\Http\Controllers\VistasReporteController@getCreditosRep');
    Route::get('/get_creditos_mora_rep', 'App\Http\Controllers\VistasReporteController@getCreditosMoraRep');
    Route::get('/rep_extracto_credito', 'App\Http\Controllers\VistasReporteController@generarReporteExtracto');
    Route::get('/get_detalle_credito_extracto', 'App\Http\Controllers\VistasReporteController@getDetalleCreditoExtracto');

    Route::get('/exportar_creditos_mora_pdf', 'App\Http\Controllers\VistasReporteController@exportarCreditosMoraPdf');
    Route::get('/exportar_creditos_mora_excel', 'App\Http\Controllers\VistasReporteController@exportarCreditosMoraExcel');
    Route::get('/rep_creditos_mora', 'App\Http\Controllers\VistasReporteController@generarReporteCreditosMora');

    Route::get('/rep_clientes_mora', 'App\Http\Controllers\VistasReporteController@generarReporteClientesMora');

    Route::get('/generar_reporte_pagos_realizados', 'App\Http\Controllers\VistasReporteController@generarReportePagosRealizados');

    Route::get('/generar_reporte_pagos_programados', 'App\Http\Controllers\VistasReporteController@generarReportesPagosProgramados');
    Route::get('/get_pagos_programados_rep', 'App\Http\Controllers\VistasReporteController@getPagosProgramadosRep');
    Route::get('/exportar_pagos_programados_pdf', 'App\Http\Controllers\VistasReporteController@exportarPagosProgramadosPdf');
    Route::get('/exportar_pagos_programados_excel', 'App\Http\Controllers\VistasReporteController@exportarPagosProgramadosExcel');

    Route::get('/reporte_extracto_movimientos', 'App\Http\Controllers\VistasReporteController@reporteExtractoMovimientos');

    Route::get('/rep_porcentajes_creditos', 'App\Http\Controllers\VistasReporteController@reportePorcentajesCreditos');
    Route::get('/get_avance_creditos_rep', 'App\Http\Controllers\VistasReporteController@getPorcentajesCreditosRep');
    Route::get('/exportar_avance_creditos_pdf', 'App\Http\Controllers\VistasReporteController@exportarAvanceCreditosPdf');
    Route::get('/exportar_avance_creditos_excel', 'App\Http\Controllers\VistasReporteController@exportarAvanceCreditosExcel');

    Route::get('/reporte_desembolsos', 'App\Http\Controllers\VistasReporteController@reporteDesembolsos');
    Route::get('/get_desembolsos_rep', 'App\Http\Controllers\VistasReporteController@getDesembolsosRep');
    Route::get('/exportar_desembolsos_pdf', 'App\Http\Controllers\VistasReporteController@exportarDesembolsosPdf');
    Route::get('/exportar_desembolsos_excel', 'App\Http\Controllers\VistasReporteController@exportarDesembolsosExcel');

    Route::get('/reporte_desembolsos_oficial', 'App\Http\Controllers\VistasReporteController@reporteDesembolsosOficial');

    Route::get('/reporte_desembolsos_pendientes', 'App\Http\Controllers\VistasReporteController@reporteDesembolsosPendientes');

    // ==================== CONFIGURACIÓN (Motivos)  [permiso: informacion] ====================
    Route::get('/get_motivos_ingresos', 'App\Http\Controllers\ConfiguracionController@getMotivosIngresos');
    Route::post('/guardar_motivo_ingreso', 'App\Http\Controllers\ConfiguracionController@guardarMotivoIngreso')->middleware('permiso:informacion');
    Route::post('/modificar_motivo_ingreso', 'App\Http\Controllers\ConfiguracionController@modificarMotivoIngreso')->middleware('permiso:informacion');
    Route::post('/desactivar_motivo_ingreso', 'App\Http\Controllers\ConfiguracionController@desactivarMotivoIngreso')->middleware('permiso:informacion');
    Route::post('/activar_motivo_ingreso', 'App\Http\Controllers\ConfiguracionController@activarMotivoIngreso')->middleware('permiso:informacion');
    Route::get('/get_motivos_gastos', 'App\Http\Controllers\ConfiguracionController@getMotivosGastos');
    Route::post('/guardar_motivo_gasto', 'App\Http\Controllers\ConfiguracionController@guardarMotivoGasto')->middleware('permiso:informacion');
    Route::post('/modificar_motivo_gasto', 'App\Http\Controllers\ConfiguracionController@modificarMotivoGasto')->middleware('permiso:informacion');
    Route::post('/desactivar_motivo_gasto', 'App\Http\Controllers\ConfiguracionController@desactivarMotivoGasto')->middleware('permiso:informacion');
    Route::post('/activar_motivo_gasto', 'App\Http\Controllers\ConfiguracionController@activarMotivoGasto')->middleware('permiso:informacion');
    Route::get('/get_motivos_ingresos_activos', [ConfiguracionController::class, 'getMotivosIngresoPorTipo']);
    Route::get('/get_motivos_gastos_activos', [ConfiguracionController::class, 'getMotivosGastoPorTipo']);

    // ==================== BÓVEDA  [permiso: informacion] ====================
    Route::get('/get_movimientos_boveda', 'App\Http\Controllers\BovedaController@getMovimientosBoveda');
    Route::get('/get_boveda', 'App\Http\Controllers\BovedaController@getBoveda');
    Route::post('/ingresar_boveda', 'App\Http\Controllers\BovedaController@ingresarBoveda')->middleware('permiso:informacion');
    Route::post('/retirar_boveda', 'App\Http\Controllers\BovedaController@retirarBoveda')->middleware('permiso:informacion');
    Route::post('/aperturar_boveda', 'App\Http\Controllers\BovedaController@aperturarBoveda')->middleware('permiso:informacion');

    // ==================== PERFIL (cualquier usuario autenticado) ====================
    Route::get('/perfil/get_datos', [PerfilController::class, 'getPerfil']);
    Route::post('/perfil/update_info', [PerfilController::class, 'updateInformacion']);
    Route::post('/perfil/update_password', [PerfilController::class, 'updatePassword']);

    // ==================== SOCIOS  [permiso: socios] ====================
    // Las escrituras llevan permiso; las lecturas quedan disponibles porque
    // también las usa la vista de Bóveda.
    Route::prefix('socio')->group(function () {
        Route::get('/activos', [SocioController::class, 'getSociosActivos']);
        Route::get('/get_socios', [SocioController::class, 'index']);
        Route::post('/registrar', [SocioController::class, 'store'])->middleware('permiso:socios');
        Route::put('/actualizar', [SocioController::class, 'update'])->middleware('permiso:socios');
        Route::put('/desactivar', [SocioController::class, 'desactivar'])->middleware('permiso:socios');
        Route::put('/activar', [SocioController::class, 'activar'])->middleware('permiso:socios');
        Route::get('/selectSocio', [SocioController::class, 'selectSocio']); // Útil para la vista de Bóveda
    });

    // ==========================================================================
    //  SHELL SPA — CATCH-ALL (Fase C)
    // ==========================================================================
    // Cualquier GET autenticado que no matcheó ninguna ruta de arriba (es
    // decir, cualquier "página" de módulo: /caja, /roles, /clientes, etc.,
    // más rutas ya retiradas como /roles, /usuarios, /solicitud...) sirve el
    // mismo shell. Vue Router (resources/js/router) decide del lado del
    // cliente qué componente mostrar y valida el permiso correspondiente.
    // DEBE quedar como la ÚLTIMA ruta del grupo para no tapar nada de arriba.
    Route::get('/{any}', function () {
        return view('app');
    })->where('any', '.*')->name('spa');
});
