# Entorno aislado y procedimiento de reproducción

## Ficha técnica observada

- Windows / Laragon; PHP CLI 8.2.29, Node 22.19.0, npm 10.9.3, Composer 2.8.11.
- Laravel instalado v10.22.0, PHPUnit 10.3.3, DomPDF v2.0.3, mPDF v8.2.5.
- Frontend: Vue 3, Vue Router 4, Vite 4; el detalle declarado está en package.json y las resoluciones en package-lock.json.
- Inicio frontend: resources/js/app.js; módulos en resources/js/router/routes.js; shell resources/views/app.blade.php.
- El `.env` habitual indica local, debug activado, MySQL en loopback:3306, caché/sesión en archivos, cola síncrona y datos demo activados. Son observaciones de desarrollo, no una evaluación de producción. No se copiaron credenciales ni se conectó el diagnóstico a esa base.
- El README original era el de Laravel. AGENTS.md mencionaba Vue 2; se actualizó la referencia técnica conservando las reglas financieras.
- Los 46 archivos de migración generan 40 tablas incluyendo `migrations`. No se contrastó este esquema con la base habitual.

## Ejecutar desde la raíz del repositorio

```powershell
php tools/diagnostics/inventory.php
php tools/diagnostics/frontend-inventory.php
php tools/diagnostics/baseline.php
php artisan test
npm.cmd run build
powershell -NoProfile -ExecutionPolicy Bypass -File tools/diagnostics/run-mysql.ps1
```

Requiere dependencias instaladas en vendor y node_modules. El último comando requiere mysqld.exe, mysqldump.exe y mysqladmin.exe; puede recibir `-MySqlBin 'ruta/al/bin'`. No utiliza el servicio MySQL habitual.

Los comandos de inventario y baseline sobrescriben solo sus informes de diagnóstico. `baseline.php` devuelve éxito de ejecución aunque reproduzca defectos: leer `scenarios.*.status`. `execution_error` indica un fallo del escenario, no una reproducción válida. Estas herramientas no sustituyen una suite de regresión de comportamiento correcto.

## Aislamiento

`tests/Support/IsolatedApplication.php` fuerza el entorno testing, clave ficticia, configuración no cacheada, sesión/caché en memoria y correo array. Lee solo tests/Support/isolated.env. Antes de iniciar proveedores limita las conexiones a SQLite `:memory:`; una conexión explícita mysql deja de estar disponible. La prueba DatabaseIsolationTest comprueba el motor y el archivo vacío de SQLite, además de una escritura temporal.

Los controladores se ejercitan con los datos de DiagnosticFixture. Cada escenario se revierte con una transacción externa; los métodos que abren transacciones anidadas no alcanzan la base habitual. Este envoltorio **no prueba atomicidad propia del controlador ni bloqueos concurrentes**.

Para probar fotos, `public_path` se redirige a storage/app/diagnostics/uploads-*. Solo se envía texto inofensivo `.txt`; no se sube ni ejecuta código y no se altera public/img.

## MySQL temporal y respaldo

1. run-mysql.ps1 crea un directorio único dentro de storage/app/diagnostics y elige un puerto libre.
2. Inicializa un servidor MySQL sin configuración heredada, enlazado únicamente a 127.0.0.1, con MySQL X desactivado. La cuenta root sin contraseña se usa solo durante esta ejecución local efímera con datos ficticios; no reutilizar esta configuración para un entorno permanente.
3. Antes de cualquier escritura, baseline.php compara `@@datadir` con el directorio recién creado. Si no coincide, aborta. Nunca reutiliza una base preexistente: CREATE DATABASE debe crear credisoft_diagnostic.
4. Ejecuta migraciones, datos ficticios, escenarios y benchmarks. No invoca DatabaseSeeder ni importa scripts SQL del repositorio.
5. mysqldump genera synthetic-backup.sql con estructura/datos/triggers/rutinas/eventos.
6. restore-check.php verifica otra vez el directorio y crea credisoft_diagnostic_restore. Compara cantidad de filas, SHA-256 del contenido ordenado y SHOW CREATE TABLE por cada tabla. Para la estructura normaliza únicamente el juego de caracteres de columna cuando es idéntico al predeterminado de la tabla: mysqldump lo escribe explícitamente al restaurar. Las intercalaciones y demás definiciones se conservan en la comparación.
7. Detiene únicamente el proceso temporal; elimina sus archivos de datos tras verificar la ruta. Conserva el dump y los registros dentro de la carpeta ignorada por Git.

Los informes públicos no incluyen contraseñas ni registros personales. Los dumps contienen solo identidades ficticias y hashes de la contraseña de prueba. No deben publicarse como respaldos reales ni instalarse en producción.

## Recuperación y límites

Reejecutar el comando crea una instancia nueva, sin borrar una base anterior o la habitual. Si falla el arranque, revisar initialize.log/server.log dentro del directorio de esa ejecución. No usar migrate:fresh, script_reset.sql ni scripts de población contra la base habitual como parte de este diagnóstico.

El ensayo demuestra restauración del esquema/datos ficticios. Quedan fuera: respaldo real, retención, cifrado de respaldos, archivos subidos, configuración del servidor productivo, recuperación ante corte eléctrico y pruebas de concurrencia. Antes de reparar históricos se necesita un respaldo completo verificado de esos elementos, con un procedimiento separado.
