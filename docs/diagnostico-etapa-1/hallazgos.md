# Hallazgos priorizados

P0: corregir antes de confiar en controles de acceso o integridad financiera. P1: funcionalidad importante o exposición de datos. P2: mantenimiento/rendimiento. Prioridad expresa impacto, no una afirmación de que haya ocurrido un incidente real.

Los casos siguientes se ejecutan con `php tools/diagnostics/baseline.php`; REP-01 requiere `run-mysql.ps1`. Datos semilla: bóveda 900, caja A 100, crédito de capital 1.000 con interés 100. Cada caso parte del mismo estado gracias al rollback externo. Ver baseline-mysql.json para los valores observados. No se cambió código de negocio para hacer pasar los casos.

## Incidencias reproducidas

| ID / prioridad | Reproducción y resultado | Esperado / propuesta | Evidencia de código |
|---|---|---|---|
| FIN-01 / P0 | Cerrar dos veces la caja A: bóveda 900 → 1.000 → 1.100 | Retorno único; validar propietario/estado, transacción y bloqueo | CajaController::closeCaja |
| FIN-02 / P0 | Enviar capital adeudado=1, interés/mora=0 para cuota de capital 1.000; pagar 1: cuota y plan quedan pagados | Recalcular deuda en servidor, validar modalidad y bloquear cuotas | PagoController::pagarCuotas |
| FIN-03 / P0 | Sin caja abierta, retirar 50 con descripción Transferencia a Caja: HTTP 200, bóveda 850, sin ingreso contraparte | Rechazar y no cambiar saldos si falta destino válido | BovedaController::retirarBoveda |
| FIN-04 / P0 | Transferir 50 a caja y anular el ingreso: ingreso inactivo, bóveda sigue en 850 | Reversión vinculada de ambos lados; bóveda vuelve a 900 | IngresoController::anularIngreso |
| FIN-05 / P0 | Transferir 50 a bóveda y anular egreso dos veces: bóveda 900 tras primera anulación, 850 tras segunda | Anulación única con saldo y estado bloqueados | GastoController::anularGasto |
| FIN-06 / P0 | Cobrar y anular pago por HistorialPagosController: pago inactivo, capital_pagado y estado 2 permanecen | Revertir componentes y recalcular cuota/plan; definir D-02 | HistorialPagosController::anular |
| FIN-07 / P0 | Crear caja B y cobrar como B: pago usa caja A | Seleccionar y autorizar caja del usuario, conforme R-01 | PagoController::pagarCuotas; revisar también ingresos/gastos |
| FIN-08 / P0 | Desembolsar dos veces 10 sobre el mismo plan: dos registros | Desembolso único y monto validado contra plan aprobado | CajaController::actualizarDesembolso |
| FIN-09 / P0 | Cobro por Deposito de 20: saldo físico calculado de caja pasa de 100 a 120 | Separar cuenta bancaria de efectivo conforme R-02 | CalculaSaldoCaja + PagoController::pagarCuotas |
| SEC-01 / P0 | Login con contraseña correcta del usuario inhabilitado: redirección 302, Auth::check sigue true | No crear sesión activa; revocar/rechazar inhabilitados en solicitudes posteriores | LoginController::loginProcess; Authenticate |
| SEC-02 / P0 | Subir texto inofensivo .txt como foto de cliente, con public_path aislado: HTTP 200 y nombre .txt guardado | Validar contenido/tipo/tamaño antes de reemplazar imagen; impedir ejecución de subidas | ClienteController::fotoCliente; patrón similar en CodeudorController::fotoCodeudor |
| SEC-03 / P1 | Usuario con rol sin permisos solicita GET /get_clientes_paginate por kernel HTTP: 200 | Definir permisos de lectura compartidos y aplicarlos en servidor | routes/web.php |
| FUN-01 / P1 | getClientesPaginate(opcion_asesor=1) falla con columna users.id inexistente | Corregir relación/filtro o retirar parámetro si no aplica | ClienteController::getClientesPaginate |
| REP-01 / P0 | Libro general marca capital 1.000 antes de cerrar y 1.100 después de un solo cierre | Excluir retorno de ingresos reales; transferencias neto cero | ConsultaFinancieraController::obtenerConsultaSQLBase |

SEC-02 demuestra aceptación de archivo no imagen, no ejecución remota; ese impacto adicional depende de la configuración del servidor. FUN-01 afecta ese endpoint/parámetro; el listado principal actual usa /get_clientes y no se afirma que todos sus listados fallen.

## Contratos incompletos confirmados por rutas y fuente

Inventario de ocho llamadas en archivos alcanzables, revisadas para descartar que sean solo comentarios. Siete endpoints diferentes. Los GET siguientes caen en el shell SPA en lugar de un endpoint JSON; los POST resuelven 405 por la ruta catch-all GET.

| ID | Llamada | Ubicación | Acción pendiente |
|---|---|---|---|
| FUN-02a / P1 | POST /caja/cobrar-orden-reprogramacion | Caja/GestionCobros.vue:994 y frmCaja.vue:1076 | Completar cobro de orden con contrato y efecto financiero definidos |
| FUN-02b / P1 | GET /historial_ingresos_listado_general | frmHistorialGastos.vue:716 | Alinear endpoint y formato de ingresos |
| FUN-02c / P1 | GET /historial_gastos_listado_general | frmHistorialGastos.vue:739 | Alinear endpoint y formato de egresos |
| FUN-02d / P1 | POST /anular_ingreso_caja | frmHistorialGastos.vue:772 | Enlazar solo tras corregir reversión de transferencias |
| FUN-02e / P1 | POST /anular_egreso_caja | frmHistorialGastos.vue:808 | Enlazar solo tras corregir anulación repetida |
| FUN-02f / P1 | GET /historial-pagos | frmHistorialPagos.vue:405 | Exponer listado agrupado con permiso y respuesta acordada |
| FUN-02g / P1 | POST /pagos/anular-transaccion | frmHistorialPagos.vue:446 | Implementar reversión por código, no solo cambiar estado |
| FUN-03 / P1 | GET /get_solicitud_respaldo | routes/web.php → SolicitudController@getSolicitudRespaldo | Método ausente; localizar consumo antes de restaurar o retirar ruta |

No basta agregar alias: validar forma de respuesta, autorización y efecto contable en cada caso.

## Riesgos detectados por inspección; no reproducidos con concurrencia

- RISK-01 / P0: saldos consultados fuera de transacción en apertura/desembolso y sin bloqueo uniforme en retiros. Ensayar dos conexiones simultáneas después de definir protocolo de bloqueo.
- RISK-02 / P1: mezcla de double/float/decimal en columnas monetarias; tasa entera en plan_pago. Revisar con valores decimales y migraciones incrementales, sin cambiar importes históricos a ciegas.
- RISK-03 / P1: retornos tempranos dentro de transacciones en varias acciones; revisar que toda salida cierre la transacción. El rollback externo del diagnóstico evita contaminar casos, pero puede ocultar atomicidad deficiente del método.
- RISK-04 / P1: campos/estados de permisos se interpretan de forma distinta en User::tienePermiso y SesionController. Revisar rol/permiso inhabilitado además de usuario.
- RISK-05 / P2: lógica y reportes concentrados en controladores grandes; duplicados/archivos antiguos requieren verificar consumidores antes de retirarlos.
- PERF-01 / P2: libro financiero materializa todo el historial y devuelve listas completas; medición 10.002 movimientos = 1.632.696 bytes, aunque tabla de 15 filas. Optimizar paginación/agregados conservando saldo anterior y totales.

## Límites del diagnóstico

No se certificaron permisos exhaustivos, todos los formatos de archivo, CORS/CSRF en navegador, carga simultánea, datos históricos, exportaciones visuales, reglas de reprogramación ni auditoría de vulnerabilidades externas. La auditoría de dependencias queda para etapa 2. Ningún resultado valida una corrección que aún no se implementó.
