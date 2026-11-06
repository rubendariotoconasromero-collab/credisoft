# Fase 2: acceso, sesiones, archivos y propiedad de operaciones

Implementación del 24 de septiembre de 2026. Se mantienen los datos de la BD habitual; las migraciones y semillas de verificación se ejecutaron exclusivamente en entornos aislados.

## Reglas aplicadas

| Perfil / permiso | Consultas | Operaciones |
|---|---|---|
| Administrador | Módulos habilitados; puede supervisar todas las cajas | Solo opera su propia caja abierta |
| Cajero / `controlcaja` | Cobros, caja y catálogos necesarios | Apertura, pagos, ingresos, egresos y cierres en su caja |
| Oficial de Credito | Solicitudes, planes, cuotas y documentos de su cartera asignada | Solo registros asignados; las operaciones monetarias requieren además `controlcaja` y caja propia |
| Encargado / `consultasfinancieras` | Caja en modo consulta y módulos autorizados | Sin operación de cajas ajenas |
| Usuario, rol o permiso desactivado | Se rechaza el acceso correspondiente | Se rechaza el acceso correspondiente |

La asignación corresponde a `solicitud.id_usuario`. La restricción identifica al rol operativo `Oficial de Credito`; un rol personalizado requiere definir expresamente su alcance antes de usarlo como oficial. El catálogo general de personas no se convierte en cartera: los filtros de propiedad se aplican a solicitudes, créditos, cuotas y sus adjuntos.

Los permisos alternativos de catálogos compartidos están declarados en `routes/web.php`. La sesión, el perfil propio y los datos de empresa usados para presentar el sistema conservan acceso para usuarios activos autenticados. Las imágenes tienen autorización específica por tipo y registro.

## Cambios

- Login: validación de entradas, cuentas y roles activos, regeneración de sesión y límite de 5 intentos por usuario/IP por minuto, además de 30 por IP.
- Un usuario o rol inhabilitado pierde acceso en la siguiente petición. `auth.session` invalida sesiones cuyo hash de contraseña ya cambió. `/me` y el servidor consultan la misma fuente de permisos activos.
- Consultas, PDF y Excel exigen permisos; no basta con ocultar botones. Los catálogos de usuarios ya no entregan hashes ni tokens de sesión.
- Las operaciones de caja comprueban propietario y estado. La comprobación se repite con bloqueo dentro de una transacción; errores y transacciones incompletas revierten la operación. Los flujos que elegían la primera caja abierta ahora seleccionan la propia.
- Cobros verifican que las cuotas pertenezcan al plan indicado. Las anulaciones de pago obtienen cuota y plan desde el registro persistido. La anulación de un plan desembolsado comprueba la caja del desembolso.
- La transferencia bóveda→caja también respeta la caja propia abierta. Para transferir hacia otra caja mediante una autorización especial haría falta una regla nueva; no se añadió esa excepción.
- El supervisor puede abrir `/caja` para consultar. La interfaz oculta acciones operativas sin permiso y el cierre de cajas ajenas. El desembolso usa el identificador de la caja propia, independientemente de la primera fila del listado.
- Las consultas SQL y los modelos de solicitudes, planes y cuotas filtran la cartera de los oficiales. También se verifican identificadores directos, anidados, de garantías y respaldos antes de modificar registros.
- Imágenes: JPEG, PNG y GIF; máximo 2 MB y dimensiones de hasta 6000×6000. Se valida el contenido antes de ejecutar el controlador, se generan nombres UUID y la extensión se determina en el servidor.
- Clientes, codeudores y respaldos de pago conservan su foto anterior si falla la BD. Los lotes validan la relación solicitud→adjunto→imagen y eliminan archivos nuevos conocidos cuando revierte el guardado. Logo y respaldo individual ya no borran el anterior antes de confirmar el cambio.
- Se retiró la ruta antigua `/solicitud_garantia_imagenes` con respuesta 410; solo la utilizaban componentes antiguos no montados. El formulario actual utiliza `/guardar_garantias_imagenes`.
- Fuera de `local` y `testing`, crear el administrador inicial exige `BOOTSTRAP_ADMIN_PASSWORD` de al menos 12 caracteres. Las contraseñas de cuentas existentes no se cambian. La clave DEMO sigue disponible en desarrollo.
- Cookies `HttpOnly`, `SameSite=Lax` y seguras por defecto en producción; respuestas JSON de errores internos se simplifican con `APP_DEBUG=false`.

## Imágenes y servidor web

`PrivateImageController` controla las URLs existentes `/img/cliente/…`, `/img/codeudor/…`, `/img/pago/…`, `/img/garantia/…`, `/img/garantias/…` y `/img/respaldos/…`. Se comprueba permiso, referencia en la BD y cartera cuando corresponde. Las respuestas usan `nosniff` y `private, no-store`.

Los adjuntos se guardan ahora en `storage/app/private/expedientes/{categoria}` (disco `expedientes`). `PrivateImages` resuelve rutas validadas y rechaza recorridos fuera de carpeta y enlaces simbólicos. Los PDF de clientes/codeudores leen las fotos desde esta ubicación. Se admite la lectura de WebP históricos, conservando la política de nuevas subidas JPEG/PNG/GIF.

Se trasladaron **61 archivos históricos** de esta instalación después de comprobar SHA-256; no se modificaron sus referencias en la BD. El manifiesto privado está en `storage/app/private/expedientes/migration.jsonl`. Solo quedaron los seis `default.png` genéricos en las carpetas públicas. Las imágenes privadas se excluyen de Git y deben incluirse en las copias de seguridad junto con la BD.

Para otras instalaciones:

```powershell
php artisan expedientes:privatizar
php artisan expedientes:privatizar --apply
php artisan seguridad:verificar
```

El primer comando solo revisa. `--apply` no sobrescribe destinos diferentes: verifica todo el inventario, copia y comprueba hashes antes de retirar cada original. Puede repetirse; una colisión detiene el proceso y conserva los archivos. Ejecutarlo durante mantenimiento. Antes de actualizar otro checkout, respaldar sus imágenes públicas: algunas estaban versionadas y la actualización de Git puede retirarlas. Conservar ese respaldo hasta comprobar su traslado y acceso privado. No enlazar `storage/app/private` dentro de `public`.

Apache mantiene la regla defensiva de `.htaccess`. Los expedientes ya no dependen de ella para evitar descargas estáticas: tampoco existen en `public` cuando se usa el servidor de Artisan. El directorio público del servidor debe seguir siendo exclusivamente `public/`.

## Verificación realizada

- `php artisan test`: 32 pruebas, 675 aserciones (PHPUnit 11).
- `npm run build`: compilación correcta con Vite 8.3.1.
- MySQL 8.4.3 desechable: dos ejecuciones de `migrate:fresh --seed`; población reproducible, 42 cuotas comprobadas, total físico y contable de 41.817 Bs. También se verificaron la cartera asignada, el filtro de asesor y la selección de caja propia sobre MySQL real.
- HTTP: sesión inhabilitada, rol y permiso desactivados, límite de login, logout, lecturas y exportaciones sin permiso, caja ajena incluso como administrador, caja cerrada, ingreso en la caja correcta, cartera ajena, uploads inválidos, foto autorizada y conservación ante fallo real de actualización SQL.
- Una prueba inspecciona todas las rutas de controladores y exige permisos explícitos, salvo las excepciones de sesión/perfil/empresa y el controlador de imágenes con autorización propia.

La verificación financiera reproducible está en `poblacion-desarrollo-verificacion.json`. Para repetirla: `powershell -NoProfile -ExecutionPolicy Bypass -File tools/diagnostics/run-mysql.ps1 -SeedDevelopment`. Este comando crea y destruye su propio servidor temporal; no usa el MySQL habitual.

## Actualización y cierre de implementación

Se actualizaron Laravel a **12.69.2**, Sanctum a **4.3.3**, Carbon a **3.14.0**, Dompdf a **3.1.6**, su wrapper a **3.1.2**, mPDF a **8.3.1** y Vite a **8.3.1** con plugins compatibles. Requisitos: PHP 8.2+; Node 20.19+ o 22.12+. Se comprobaron los requisitos Composer con PHP 8.2.29 y Node 22.19.0.

- Las diferencias de días conservan el truncamiento a enteros y el signo anterior con Carbon 3; la renovación de reglas financieras queda en fase 3.
- Los PDF mPDF devuelven su contenido dentro de la respuesta Laravel, sin imprimirlo antes de los middleware.
- Se corrigió el alias de Dompdf y el conflicto de autodetección con Snappy, que conserva su registro explícito. Dompdf no ejecuta PHP ni carga recursos remotos, admite imágenes `data:` y limita el acceso local a carpetas de recursos.
- Se retiró `rmunate/spell-number`, abandonado y sin uso en el código.
- **Composer: 0 avisos de vulnerabilidad; npm: 0 vulnerabilidades** en la auditoría de cierre. Detalle reproducible en `fase-2-auditoria-dependencias.json`.
- Las pruebas generan realmente tickets Dompdf, fichas mPDF (incluida una foto privada), PDF por el alias antiguo y Excel legible con los créditos DEMO. También verifican migración repetible, colisiones sin pérdida, rutas inválidas y autorización de fotos por cartera.
- En navegador se verificaron login, panel, clientes, codeudores y caja con la cuenta DEMO, sin errores ni advertencias de consola y sin registrar operaciones monetarias. Esta comprobación acotada no sustituye la aceptación manual de todos los perfiles.
- Vite de desarrollo sirvió `@vite/client` y la entrada de la SPA con HTTP 200, CORS correcto y sin `__WS_TOKEN__` pendiente. Se retiró el archivo `public/hot` obsoleto de un servidor detenido; esta instalación usa los assets compilados. `npm run dev` permite volver al modo de desarrollo.

La implementación pendiente de esta fase queda completada en el entorno de desarrollo. La aceptación manual del usuario sigue siendo necesaria; no se declara que todas las funcionalidades del sistema estén completas.

Referencias de compatibilidad: [Laravel 11](https://laravel.com/docs/11.x/upgrade), [Laravel 12](https://laravel.com/docs/12.x/upgrade), [Vite](https://vite.dev/guide/migration), [Dompdf para Laravel](https://github.com/barryvdh/laravel-dompdf).

## Comprobación previa a producción

`php artisan seguridad:verificar` pasa en esta instalación. `php artisan seguridad:verificar --production` exige además `APP_ENV=production`, `APP_DEBUG=false`, URL HTTPS, cookies Secure, DEMO deshabilitado, ausencia de `public/hot` y assets compilados. En Laragon los ajustes de desarrollo se conservan deliberadamente; no se cambió `.env` a producción.

En el servidor real: respaldar BD y expedientes; desplegar con PHP compatible; configurar HTTPS y raíz `public/`; instalar desde los lockfiles; compilar assets; migrar los adjuntos históricos; regenerar cachés de configuración/vistas; ejecutar el verificador y probar login, permisos y exportaciones. El verificador comprueba configuración local, no certifica TLS, proxies, permisos del hosting ni restauración de respaldos de un servidor todavía no proporcionado.

Las correcciones financieras de fase 3 siguen pendientes: cálculo autoritativo de la deuda, separación efectivo/bancos, anulación contable consistente, conciliación de retornos, idempotencia de desembolsos y pruebas de concurrencia completas. La nueva protección de acceso no corrige por sí sola esas reglas.

## Prueba manual con la población de escenarios

Usar la URL de Apache/Laragon y dos navegadores o perfiles separados. Las cuentas y escenarios están en `poblacion-desarrollo.md`.

1. Entrar como `lucia.rivera` y `andres.flores`. Confirmar que cada uno ve su propio saldo operativo (2.160 y 1.500 Bs inicialmente) y registra sus ingresos en su caja.
2. Abrir el detalle de la caja ajena: el botón de cierre no debe estar disponible. Un POST directo a `/close_caja` con la caja ajena debe devolver 403 sin cambiar saldos.
3. Entrar como `patricia.suarez`: abrir Caja para consultar; no deben aparecer los botones de apertura, ingresos, egresos o cobros. El administrador tampoco puede cerrar una caja ajena.
4. Entrar como cada oficial. Comparar solicitudes y créditos; modificar la URL para abrir un ID del otro oficial debe fallar. Revisar también cuotas, garantías y respaldos.
5. Desactivar un usuario desde otra sesión. Su siguiente petición debe perder acceso. Repetir con el rol y con un permiso individual.
6. Subir una foto PNG/JPEG; intentar luego TXT, PHP o un archivo de más de 2 MB. La foto anterior debe conservarse. Copiar la URL y abrirla sin sesión: debe pedir login.
7. Revisar PDF/Excel permitidos y confirmar que otro perfil sin permiso no puede descargarlos directamente.
8. Registrar cualquier bloqueo inesperado indicando cuenta, módulo, acción y mensaje. Los errores funcionales ya identificados en fase 1 siguen documentados en el backlog; no confundirlos con un permiso denegado.

No hace falta ejecutar `migrate:fresh --seed` para aplicar el código de esta fase. Solo usar ese comando cuando se quiera reiniciar deliberadamente la población de desarrollo, porque elimina los datos existentes.
