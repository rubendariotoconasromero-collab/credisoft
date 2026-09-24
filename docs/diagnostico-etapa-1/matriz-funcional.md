# Matriz funcional

Fuentes: routes/web.php, resources/js/router/routes.js, controladores y llamadas axios detectadas. [Pantallas completas](pantallas.md) enumera las 32 entradas. [inventario.json](inventario.json) contiene todas las rutas, middleware y tablas por controlador; [frontend.json](frontend.json) enlaza las llamadas con sus archivos/líneas.

**Estados:** inventariado = código/rutas presentes; reproducido = escenario ejecutado con datos ficticios; incompleto = falta contrato o implementación comprobada. Ningún estado inventariado certifica un recorrido completo en navegador.

| Área | Pantallas principales | Controladores / tablas principales | Acciones y estado | Validación siguiente |
|---|---|---|---|---|
| Sesión y perfil | /perfil; login | Login, Sesion, Perfil / users, rol, permiso, permiso_rol | Login y perfil inventariados; SEC-01 reproducido | Login/logout, contraseña, expiración e inhabilitación vía HTTP/navegador |
| Panel | /administracion | Administracion, Panel / solicitud, plan_pago, cuota, pago, caja | Paneles por rol inventariados | Visibilidad y cifras según rol, usuario y fecha |
| Roles y usuarios | /roles, /usuarios | Rol, Usuario, Permiso / rol, users, permiso_rol | Alta/modificación/activación inventariadas | Matriz de permisos; rol/permiso inactivo |
| Empresa | /informacion | MiEmpresa / mi_empresa | Edición y lectura inventariadas | Información inicial, logo válido, dependencia del shell |
| Clientes | /clientes | Cliente / cliente, direccion, telefono, solicitud | CRUD/ficha/foto/reportes; SEC-02/03 y FUN-01 reproducidos | Validaciones, homónimos, direcciones/mapa y PDF |
| Codeudores | /codeudores | Codeudor / codeudor, direccion, telefono, solicitud_codeudor | CRUD/foto/reportes inventariados; validación de foto deficiente por inspección | Reproducir fotos y verificar vínculos con solicitudes |
| Solicitudes y garantías | /solicitud | Solicitud, Respaldo / solicitud, garantia, imagen, respaldo, solicitud_codeudor | Alta/aprobación/garantías inventariadas; método getSolicitudRespaldo ausente | Ciclo solicitud→aprobación→plan; archivos y restricciones |
| Planes y cartera | /plan_pago | PlanPago / plan_pago, cuota, pago_amortizacion | Amortización medida en MySQL; reprogramación/refinanciamiento inventariados | Modalidades, fechas, liquidación y estados de crédito original |
| Desembolsos | /caja, /historial_desembolsos_pagos | Caja / desembolso, plan_pago, solicitud, pago_administrativo | FIN-08 reproducido; administrativo inventariado | Una entrega por plan; saldo/propiedad; administrativo y anulaciones |
| Cobros e historial | /caja, /historial_pagos | Pago, HistorialPagos / pago, cuota, plan_pago, movimientos_caja | FIN-02/06/07/09 reproducidos; listado/anulación UI incompletos FUN-02 | Deuda en servidor; modalidades; banco/efectivo; recibo y reversión |
| Caja | /caja | Caja / caja, movimientos_caja | Apertura inventariada; FIN-01 reproducido | R-01 dos cajeros; cierre único; fallos y simultaneidad |
| Ingresos/egresos | /historial_gastos, /historial_ingresos | Ingreso, Gasto / ingreso, egreso, movimientos_caja | FIN-04/05 reproducidos; contratos incompletos FUN-02 | Contrapartidas, motivos, estado, propietario y banco/efectivo |
| Bóveda y socios | /boveda, /socio | Boveda, Socio / boveda, movimientos_boveda, socios | FIN-03 reproducido; socios inventariado | Fondos, retiros, socio, destino y conciliación |
| Configuración | /configuracion | Configuracion / motivo_ingreso, motivo_gasto | CRUD/activación/tipos inventariados | Motivos según operación; clasificación por tipo explícito |
| Finanzas | /consultas_financieras, /estado_resultados | ConsultaFinanciera, EstadoResultados / pago, ingreso, egreso, desembolso, movimientos_boveda | REP-01 reproducido; libro medido con 10.002 movimientos | Saldos/totales, anulaciones y bancos; PDF/Excel |
| Reportes | /reportes y rutas específicas en pantallas.md | Reporte, VistasReporte, Export / crédito, cliente y movimientos | Rutas y exportaciones inventariadas | Comparar resultado con origen; permisos y revisión visual |
| Infraestructura SPA | /prohibido, ruta no encontrada | Vue Router, shell Blade | Importaciones y permisos inventariados | Distinguir API inexistente de página; sesión expirada |

## Dependencias y duplicación

- La fuente de permisos del menú se obtiene por /me; cada endpoint necesita protección independiente en backend.
- Direcciones/teléfonos compartidos entre clientes y codeudores: verificar propietario y nulabilidad antes de modificar el esquema.
- CalculaSaldoCaja centraliza saldo pero suma formas bancarias al efectivo. CalculaAmortizaciones contiene SQL específico de MySQL; SQLite no lo valida.
- Componentes antiguos fuera del grafo de importación activo se registran, pero no se eliminan ni se consideran automáticamente errores visibles.
- transferencia_caja_boveda existe en migraciones: revisar consumidores antes de decidir cómo relacionar contrapartidas.
- El rastreo usa expresiones regulares: URLs mediante variables/concatenación necesitan revisión adicional. No equivale a probar todos los contratos.

## Cobertura faltante explícita

Recorridos visuales, PDFs/XLS, todos los roles, dispositivos móviles, garantías/imágenes de respaldo, carga concurrente, reprogramación completa, históricos y comparación con el esquema real. El inventario y los escenarios permiten abordar esa cobertura incrementalmente.
