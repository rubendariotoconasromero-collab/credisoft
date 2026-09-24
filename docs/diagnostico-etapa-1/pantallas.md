# Inventario de pantallas

Generado desde resources/js/router/routes.js. Estado: existencia y carga declarada verificadas; aceptación visual pendiente.

| Ruta | Componente | Permiso de navegación |
|---|---|---|
| `/administracion` | `../components/PanelPrincipal.vue` | null |
| `/caja` | `../components/frmCaja.vue` | controlcaja |
| `/historial_desembolsos_pagos` | `../components/frmHistorialDesPagos.vue` | controlcaja |
| `/historial_gastos` | `../components/frmHistorialGastos.vue` | controlcaja |
| `/historial_pagos` | `../components/frmHistorialPagos.vue` | controlcaja |
| `/historial_ingresos` | `../components/frmHistorialIngresos.vue` | controlcaja |
| `/clientes` | `../components/frmCliente.vue` | cliente |
| `/codeudores` | `../components/frmCodeudores.vue` | codeudores |
| `/consultas_financieras` | `../components/frmConsultaFinanciera.vue` | consultasfinancieras |
| `/estado_resultados` | `../components/frmEstadoResultados.vue` | consultasfinancieras |
| `/solicitud` | `../components/frmSolicitud.vue` | solicitudprestamos |
| `/plan_pago` | `../components/frmPlanPago.vue` | planpagos |
| `/informacion` | `../components/frmInformacion.vue` | informacion |
| `/usuarios` | `../components/frmUsuarios.vue` | usuarios |
| `/socio` | `../components/frmSocio.vue` | socios |
| `/roles` | `../components/frmRoles.vue` | roles |
| `/configuracion` | `../components/frmConfiguracion.vue` | configuracion |
| `/boveda` | `../components/frmBoveda.vue` | boveda |
| `/reportes` | `../components/frmReporte.vue` | reportes |
| `/index_rep_extracto` | `../components/repExtracto.vue` | reportes |
| `/hist_credito_mora` | `../components/repHistoricoCreditoMora.vue` | reportes |
| `/cliente_mora` | `../components/repClientesMora.vue` | reportes |
| `/index_rep_pagos_realizados` | `../components/repPagosRealizados.vue` | reportes |
| `/index_pagos_programados` | `../components/repPagosProgramados.vue` | reportes |
| `/index_movimientos_credito` | `../components/repMovimientosCredito.vue` | reportes |
| `/index_porcentajes_pagos` | `../components/repAvanceCreditos.vue` | reportes |
| `/index_desembolsos` | `../components/repDesembolsos.vue` | reportes |
| `/index_desembolsos_oficial` | `../components/repDesembolsosOficial.vue` | reportes |
| `/index_desembolsos_pendientes` | `../components/repDesembolsosPendientes.vue` | reportes |
| `/perfil` | `../components/PerfilUsuarioComponent.vue` | null |
| `/prohibido` | `../components/Router/Prohibido.vue` | null |
| `/:pathMatch(.*)*` | `../components/Router/NoEncontrado.vue` | null |
