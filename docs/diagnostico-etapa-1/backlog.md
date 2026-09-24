# Backlog de implementación posterior

Estado inicial de todos los paquetes: pendiente. Tamaño orientativo S/M/L expresa complejidad relativa, no una promesa de plazo. Primero convertir el escenario diagnóstico correspondiente en prueba de regresión del comportamiento correcto.

| Orden / paquete | Alcance y hallazgos | Depende de | Tamaño | Criterio de aceptación |
|---|---|---|---|---|
| B-01 Seguridad inmediata | Usuarios inhabilitados y fotos: SEC-01/02 | Entorno aislado listo | M | HTTP rechaza usuario inactivo; archivo no imagen rechazado; foto previa se conserva si falla reemplazo |
| B-02 Autorización | Lecturas, propietario de caja, estados de roles/permisos: SEC-03, RISK-04 | R-01; D-01/D-06 para excepciones | M | Matriz de roles probada por HTTP; cajero B no opera caja A; catálogos compartidos autorizados explícitamente |
| B-03 Modelo de dinero | Separar bancos/efectivo y fijar precisión: FIN-09, RISK-02 | D-05 | L | Depósito/transferencia reduce deuda, afecta banco y no cambia efectivo; migración incremental con clasificación histórica revisada |
| B-04 Caja y transferencias | Apertura/cierre idempotentes, contrapartidas y anulaciones: FIN-01/03/04/05/07, RISK-01/03 | B-02; compatible con B-03 | L | Conservación con dos usuarios, reintentos y fallos intermedios; una caja abierta por usuario; auditoría sin borrados |
| B-05 Cobros/desembolsos | Deuda en servidor, modalidades, exceso y desembolso único: FIN-02/08 | B-02/03/04; D-03/D-04 | L | Cinco modalidades correctas; cuota no se liquida pagando 1 de 1.000; desembolso no se duplica bajo concurrencia |
| B-06 Anulaciones | Pago/transacción, cuota/plan, bancos/caja, condonaciones: FIN-06 | B-05; D-02 | L | Reversión exacta de componentes y estados, una sola vez, con motivo/autor y prueba de caja cerrada |
| B-07 Reportes y conciliación | Retornos y saldos, bancos/efectivo: REP-01 | B-03/04/05/06 | M | Transferencia/cierre no infla capital/ingresos; misma cifra en pantalla/PDF/Excel; diferencias históricas identificadas |
| B-08 Contratos faltantes | FUN-02b/c/d/e/f/g, FUN-03 y filtro FUN-01 | B-04/06 para habilitar anulaciones | M | Cada acción activa tiene endpoint, permiso, formato y prueba HTTP; no se recibe HTML donde se espera JSON |
| B-09 Reprogramación | FUN-02a y ciclo de solicitud/orden/crédito original | B-05/06; D-07 | L | Flujo completo aprobado con estados y trazabilidad del crédito anterior |
| B-10 Rendimiento/mantenimiento | PERF-01, RISK-05 | Resultados financieros estabilizados | M/L | Reducir volumen de respuesta sin perder saldos/totales; comparar mismas muestras; extraer servicios sin duplicar fórmulas |
| B-11 Operación y entrega | Navegador, reportes visuales, respaldo real, despliegue y monitoreo | Paquetes críticos terminados | L | Aceptación del usuario, cero P0 abiertos, restauración real ensayada y reversión de despliegue documentada |

## Aclaración posterior de alcance para fase 3

La propuesta original de B-03 sobre cuentas bancarias queda sustituida por el [plan actualizado de fase 3](../fase-3-plan-desarrollo.md): efectivo y no efectivo permanecen en el circuito de caja/bóveda, con ingresos por forma de pago y total en el arqueo. No se incluye módulo bancario ni conciliación independiente; se preserva la distinción del efectivo físicamente disponible. La tabla anterior conserva el diagnóstico original y no prevalece sobre esta aclaración del usuario.

## Primer bloque recomendado

B-01 y definición de B-02. En B-01 no es necesario resolver las políticas financieras pendientes para impedir sesiones de usuarios inactivos o archivos inválidos. Continuar después con B-04, diseñándolo para la separación bancaria de B-03.

No exponer botones de anulación mediante simples alias de rutas mientras la reversión de dinero siga incompleta.

## Validación y reversión

- Cambios de código: commit/PR acotado por paquete y suite pertinente; despliegue y reversión a una versión identificada.
- Migraciones: agregar migraciones nuevas, ensayo desde copia anonimizada, comprobar índices/constraints y plan de rollback antes de tocar históricos.
- Reparación de datos: diagnóstico de solo lectura, propuesta por registro con valores anteriores/nuevos, respaldo completo y autorización concreta antes de ejecución.
- Repetir benchmarks solo cuando cambie el flujo medido o exista una hipótesis de rendimiento que verificar.

## Pendientes para cerrar aceptación de etapa 1

Confirmar D-01 a D-08 según su dependencia; verificar los recorridos visuales con datos ficticios y usuarios representativos; contrastar esquema/histórico real de forma controlada. El inventario y el entorno ya permiten comenzar correcciones técnicas independientes. La aceptación comercial/operativa no se deduce de compilar ni de reproducir defectos.
