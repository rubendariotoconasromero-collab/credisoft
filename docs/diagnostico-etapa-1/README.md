# Credisoft — diagnóstico de etapa 1

Fecha: 2026-09-24. Alcance: repositorio local, esquema reconstruido desde migraciones y datos exclusivamente ficticios. No se ejecutaron operaciones financieras sobre la base habitual.

## Resultado

El entorno aislado funciona en SQLite en memoria y MySQL 8.4.3 temporal. Las 46 migraciones se ejecutaron desde cero. Se reprodujeron 14 incidencias en MySQL; 13 de ellas también en SQLite. El diagnóstico reproduce defectos actuales: **no significa que hayan sido corregidos**.

Se inventariaron 252 rutas registradas y 32 entradas de Vue Router (30 pantallas de negocio/perfil y dos pantallas de infraestructura). El análisis de llamadas encontró ocho referencias a siete endpoints distintos sin contrato de API válido en componentes alcanzables desde la aplicación. Existe además una ruta cuyo método de controlador no existe.

El respaldo y restauración del conjunto ficticio compara filas y definición de las 40 tablas. El servidor temporal se detiene al terminar; el servicio habitual de Laragon no se modifica.

## Documentos y evidencias

- [Entorno y reproducción](entorno.md): aislamiento, requisitos, ejecución y recuperación.
- [Matriz funcional](matriz-funcional.md): pantallas, permisos, responsabilidades y cobertura.
- [Reglas y aceptación](reglas.md): requisitos confirmados y decisiones pendientes.
- [Hallazgos](hallazgos.md): reproducción, impacto y corrección propuesta.
- [Backlog](backlog.md): orden, dependencias y pruebas de aceptación.
- [Inventario backend](inventario.json): rutas, middleware, métodos, tablas detectadas y versiones.
- [Inventario frontend](frontend.json): llamadas con archivo y línea; alcance y límites del análisis estático.
- [Resultados MySQL](baseline-mysql.json) y [SQLite](baseline-sqlite.json): escenarios y mediciones.
- [Restauración](restauracion.json): huellas de datos y esquema por tabla.
- [Verificación técnica](verificacion.json): compilación, pruebas y sintaxis.

## Cambios realizados en esta etapa

1. Aislamiento de la suite de pruebas: no carga el `.env` habitual, elimina conexiones alternativas y usa SQLite en memoria antes de iniciar proveedores.
2. La prueba de inicio ahora ejecuta migraciones aisladas: antes dependía de que existiera `mi_empresa` en la conexión heredada.
3. Herramientas de inventario, reproducción, medición y restauración de datos sintéticos.
4. Documentación de Vue 3 y de las reglas confirmadas: una caja por usuario y separación de pagos bancarios.

No se cambiaron controladores, rutas de negocio, cálculos, migraciones ni datos históricos.

## Estado de salida

| Trabajo | Estado | Evidencia / límite |
|---|---|---|
| Estado técnico y configuración local | Realizado | Laravel instalado 10.22.0; PHP 8.2.29; configuración local sin credenciales en el informe |
| Entorno aislado y datos ficticios | Verificado | SQLite y MySQL separados; cuatro usuarios, dos roles, cliente/codeudor/crédito/caja/bóveda |
| Respaldo y restauración | Verificado con datos sintéticos | 40 tablas; no certifica respaldos del sistema en producción ni archivos adjuntos |
| Inventario funcional | Realizado a nivel código/rutas | Existencia de una pantalla no equivale a validación funcional completa |
| Reglas operativas | Parcialmente confirmadas | Dos decisiones respondidas por el usuario; otras enumeradas en reglas.md |
| Reproducción prioritaria | Realizada | 14 escenarios; la mayoría invoca controladores y uno recorre HTTP con middleware |
| Línea base | Realizada en entorno local | Cinco recorridos medidos; sin carga concurrente ni red/navegador |
| Backlog | Preparado | Correcciones ordenadas con aceptación y dependencias |

**El diagnóstico técnico está entregado. La etapa no constituye aceptación operativa integral:** quedan confirmación de reglas pendientes, recorridos visuales con usuarios, concurrencia y comparación del esquema/historial real con el reconstruido. No se han probado todas las modalidades, exportaciones ni reprogramaciones de extremo a extremo.

## Línea base MySQL

Mediana de cinco repeticiones tras calentamiento. Tiempo de ejecución de controlador, sin red, navegador o autenticación salvo cuando se indica. Las cifras exactas y muestras están en baseline-mysql.json.

| Recorrido | Volumen | Mediana aproximada | Consultas | Respuesta |
|---|---|---:|---:|---:|
| Clientes paginados | 1.001 clientes | 1,55 ms | 2 | 6.608 bytes |
| Ficha de cliente | 1 cliente, sin direcciones/teléfonos | 0,97 ms | 3 | 331 bytes |
| Amortización | 1 cuota | 0,49 ms | 1 | 785 bytes |
| Libro financiero | 2 movimientos | 1,24 ms | 1 | 725 bytes |
| Libro financiero | 10.002 movimientos | 67,75 ms | 1 | 1.632.696 bytes |

La última respuesta muestra 15 filas paginadas, pero incluye 10.001 filas de ingresos sin paginar. Es evidencia de crecimiento de la respuesta con el historial; no demuestra por sí sola una latencia inaceptable en producción.
