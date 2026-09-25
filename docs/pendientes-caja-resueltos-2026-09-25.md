# Resolución de pendientes de caja — 25/09/2026

## Cambios

- Cobro retroactivo: fecha, deuda y revisión permanecen sincronizadas al cancelar y reabrir. Durante el recálculo, o si falla, se impide continuar; se descartan respuestas atrasadas.
- Búsqueda de créditos para cobro: se eliminó el filtro inicial de tres meses y se permiten límites de fecha independientes. Los planes anulados o reservados para reprogramación no se ofrecen para cobro ordinario.
- Reprogramación y refinanciamiento: simulación, capital, deuda de corte, cuotas y órdenes se calculan en el servidor. Los importes enviados por el navegador no determinan las obligaciones. Se corrigió el uso de separadores de miles en cálculos del formulario.
- Registro, aprobación, cobro y cancelación usan transacciones, bloqueo financiero y claves de reintento. La nueva tabla `reprogramaciones_financieras` guarda la deuda de corte y los vínculos necesarios para auditar y revertir.
- La aprobación exige rol Administrador también en el servidor. La edición genérica no puede sobrescribir una solicitud especial; sus términos se ajustan en la aprobación.
- Las órdenes se habilitan al aprobar; admiten pagos mixtos, cambio únicamente en efectivo, condonación autorizada y recibo PDF. Cada ingreso se suma una sola vez a caja.
- El nuevo crédito no permite cobros ordinarios, gasto administrativo ni desembolso mientras su orden esté pendiente. En refinanciamiento se desembolsa únicamente el importe adicional.
- La cancelación restablece el crédito original cuando es reversible. Un cobro de orden debe anularse primero en la caja propia abierta; la actividad posterior del nuevo crédito impide revertir su origen.
- La documentación de cierre indica retorno del saldo operativo total a bóveda, conservando por separado el arqueo físico.

## Reglas del recorrido

La deuda se fija al registrar la solicitud especial y el crédito original queda reservado. Los intereses y multas seleccionados se separan en una orden; el resto se incorpora al nuevo capital junto con el adicional, cuando corresponda. Las condonaciones se descuentan de la orden, nunca del capital. Si se cancela, el crédito original vuelve a su estado anterior y su deuda vuelve a calcularse según las fechas originales.

La aprobación genera el cronograma desde el capital verificado. Se conserva el historial original; las cuotas sustituidas quedan anuladas lógicamente. La solicitud no puede reactivarse por rutas genéricas para eludir este circuito.

## Validación

- `php artisan test`: 88 pruebas y 1.285 comprobaciones aprobadas. `node --test tests/frontend/cobros.test.mjs`: 3 pruebas aprobadas.
- Casos HTTP: importes manipulados, simulación consistente, aprobación, cobro mixto, reintento, recibo, condonación total sin ingreso, permisos de condonación, anulación y restitución, bloqueo por actividad posterior y desembolso exclusivo del adicional.
- Cronogramas: conservación exacta del capital y saldo final cero para las tres frecuencias, dos tipos de tasa y tasa cero/positiva.
- MySQL 8.4.3 temporal: dos procesos cobrando la misma orden y clave reciben la misma operación; se registra un solo ingreso de Bs 76,67. Evidencia en `fase-3-concurrencia.json`. También pasan las carreras de apertura, retiro, cobro, desembolso y cierre/transferencia.
- Navegador con SQLite independiente y datos ficticios: filtros vacíos, detalle de deuda, cambio de fecha, bloqueo durante cálculo y conservación de fecha/importe al cancelar y reabrir.
- Recorrido visual completo de reprogramación: selección de intereses, simulación del servidor, registro, aprobación como administrador y cobro de orden con Bs 50 en efectivo y Bs 30 por transferencia. Resultado: una sola fila de pago de Bs 76,67, cambio de Bs 3,33, orden pagada y botón de recibo disponible. Verificado también en la base ficticia: efectivo aplicado Bs 46,67 y transferencia Bs 30.
- Compilación Vite aprobada.

## Aplicación y límites

La migración nueva se aplicó a la base local; únicamente creó la tabla de seguimiento. Las pruebas monetarias usan datos ficticios en bases aisladas. No se modificaron saldos ni pagos históricos de la base habitual.

Las solicitudes y órdenes históricas sin cálculo verificable quedan bloqueadas para este circuito y requieren revisión individual. No se reconstruyen ni se consideran correctas automáticamente. Las pruebas no certifican la integridad del historial real ni sustituyen la aceptación operativa de la institución.
