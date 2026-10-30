# Examen de seguridad — FacturaFácil

**Fecha:** 2026-10-28  
**Alcance:** revisión defensiva estática del código, configuración, rutas, dependencias PHP/Node y archivos versionados.  
**Método:** revisión manual + `composer audit` + `npm audit` (`print-server-node`) + `php artisan route:list` + inspección de `.env` (sin exponer secretos).  
**Nota:** no se explotaron vulnerabilidades ni se modificó código durante la auditoría.

---

## 1. Resumen ejecutivo

Se identificaron **5 hallazgos críticos, 8 altos, 6 medios y varios bajos/informativos**.

Los riesgos más urgentes son:

1. Dependencias PHP/Node con vulnerabilidades conocidas (incluida una **crítica** de Laravel).
2. Entorno actual en `local`/`debug`, con `APP_DEBUG=true` y `LOG_LEVEL=debug`.
3. Backup completo de la base de datos versionado en Git.
4. Cuentas demo/administrativas y contraseñas por defecto en seeders/README.
5. XSS almacenado desde el autopedido/notas hacia el KDS y la vista de restaurante.

También hay problemas altos de **IDOR/multiempresa**, endpoints públicos con datos personales, servidor de impresión sin autenticación y fotos de asistencia en almacenamiento público.

---

## 2. Hallazgos críticos

### C-01 — Dependencias vulnerables

**Evidencia:**

- `laravel/framework v13.12.0`.
- `guzzlehttp/guzzle 7.10.5`.
- `guzzlehttp/psr7 2.10.4`.
- `dompdf/dompdf v3.1.5`.
- `phpoffice/phpspreadsheet 5.7.0`.
- `league/commonmark 2.8.2`.
- `symfony/http-foundation v8.1.0`.
- `symfony/mime v8.1.0`.
- `composer audit`: **42 advisories en 9 paquetes**.
- `npm audit` en `print-server-node`: `proxy-addr` crítico, `body-parser` alto y `qs` moderado.

**Impacto:**

- Laravel 13.12.0 está afectado por **CVE-2026-114509** (crítico, bypass de TokenGuard). La aplicación expone `auth:sanctum` en `/api/user`, por lo que debe actualizarse.
- Guzzle, PSR-7, PhpSpreadsheet, CommonMark y Symfony tienen advisories altas/medias (SSRF, XXE, DoS, XSS, ReDoS, etc.).
- El print server usa paquetes con vulnerabilidades conocidas.

**Recomendación:**

- Actualizar a versiones parcheadas: Laravel ≥ 13.29, Guzzle ≥ 7.15.2, Dompdf ≥ 3.1.6, PhpSpreadsheet > 5.9.0, CommonMark > 2.10.1 y Symfony ≥ 8.1.10.
- Ejecutar `composer update` y `npm audit fix` en `print-server-node`.
- Probar SUNAT, PDFs, importación/exportación Excel y print server después de actualizar.

---

### C-02 — Entorno en desarrollo/debug

**Evidencia (`.env` revisado):**

- `APP_ENV=local`
- `APP_DEBUG=true`
- `LOG_LEVEL=debug`
- `APP_URL=http://facturafacil.test`
- `SESSION_SECURE_COOKIE` vacío
- `SESSION_SAME_SITE` vacío
- `SESSION_ENCRYPT` vacío

**Impacto:**

Si el servidor público usa este mismo `.env`, cualquier error puede mostrar stack traces, rutas, consultas SQL, variables de entorno y datos internos. La cookie de sesión puede viajar sin la marca `Secure`.

**Recomendación:**

En producción:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio
LOG_LEVEL=warning
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_ENCRYPT=true
```

Verificar el `.env` real del servidor, no solo la copia local.

---

### C-03 — Backup completo de la base de datos en Git

**Evidencia:**

```text
eliminar-ventas-productos/backup/pre-kitchen-batches-20261028-074721.sql
```

- Archivo versionado (`git ls-files`).
- Tamaño: **5.78 MB**.
- Contiene tablas como `users`, `customers`, `invoices`, `companies`, `personal`, `cashregisters`, `attendance_*`, entre otras.

**Impacto:**

Exposición de datos personales (clientes, personal), comprobantes, movimientos de caja, hashes de contraseñas y posiblemente certificados/secretos de empresa.

**Recomendación:**

1. Sacar el archivo del working tree y del historial (`git filter-repo` o BFG).
2. Agregar la carpeta de backups a `.gitignore`.
3. Rotar contraseñas y secretos que pudieran estar en la base.
4. Considerar el repositorio como comprometido y revisar accesos.

---

### C-04 — Cuentas y contraseñas por defecto

**Evidencia:**

- `README.md` documenta credenciales por defecto.
- `database/seeders/TestUsersSeeder.php` crea `demo@example.com`, `manager@example.com` y `mozo@gmail.com` con contraseñas conocidas.
- `database/seeders/SuperAdminSeeder.php` crea `Caja@gmail.com` con contraseña conocida.
- `database/seeders/AdminUserSeeder.php:62` contiene una contraseña de administrador hardcodeada.
- En la tabla `users` existen cuentas como `manager@example.com`, `Caja@gmail.com`, `mozo@gmail.com`, `demo@example.com` y `admin@example.com`.

**Impacto:**

Acceso administrativo/cajero/mozo con credenciales conocidas si no fueron cambiadas o deshabilitadas.

**Recomendación:**

- Deshabilitar cuentas demo y de prueba.
- Cambiar todas las contraseñas de `admin`, `cajero` y `mozo`.
- Eliminar contraseñas hardcodeadas de seeders.
- Aplicar contraseñas fuertes y, si es posible, 2FA.
- Revisar todos los usuarios con rol `admin`.

---

### C-05 — XSS almacenado desde autopedido/notas hacia el KDS

**Evidencia:**

- `resources/views/restaurant/kds.blade.php:803` (`order.notes`).
- `resources/views/restaurant/kds.blade.php:820-822` (`item.product_name`, `item.notes`, `auxiliary_names`).
- `resources/views/restaurant/index.blade.php:1232-1243` (nombre, notas y auxiliares del pedido).
- `resources/views/autopedido/index.blade.php:122,337` (nombre/notas insertados en `onclick`/`innerHTML`).

**Impacto:**

Un visitante **público** puede enviar una nota desde `/autopedido`. El KDS la renderiza con `innerHTML`, ejecutando JavaScript en la sesión del KDS (admin/cajero). También un mozo puede inyectar contenido desde las notas del pedido.

**Recomendación:**

- No usar `innerHTML` con datos de usuario.
- Usar `textContent` o una función `escapeHtml`.
- Usar `data-*` + `addEventListener` en lugar de `onclick` con cadenas.
- Agregar una buena **Content-Security-Policy**.
- Sanitizar las notas en servidor como defensa adicional.

---

## 3. Hallazgos altos

### H-01 — IDOR / falta de aislamiento por empresa

**Evidencia:**

- `app/Http/Controllers/CashRegisterController.php:81` usa `CashRegister::findOrFail($request->cashregister_id)` y las rutas `show/pdf/ticket` usan route model binding sin filtrar por empresa.
- `app/Http/Controllers/CustomerApiController.php:14` usa `company_id` desde el request.
- `app/Http/Controllers/DecolectaController.php:15` usa `company_id` desde el request.
- `app/Http/Controllers/Restaurant/RestaurantController.php:35,192,532` toman `company_id` del request o usan `RestaurantOrder::findOrFail($orderId)` sin scope.
- `app/Http/Controllers/CashMovementController.php:17` toma `company_id` del request.
- `InvoiceController`, `PosController` y otros flujos también reciben `company_id` desde el request.

**Impacto:**

Un usuario autenticado puede consultar o modificar información de otra empresa si conoce IDs (cajas, pedidos, facturas, clientes, movimientos).

**Recomendación:**

- Derivar la empresa de `auth()->user()->company_id`.
- Usar policies/global scopes.
- Reemplazar `findOrFail($id)` por consultas con `where('company_id', $companyId)`.
- Agregar pruebas de aislamiento multiempresa.

---

### H-02 — `/decolecta/search` público con datos personales

**Evidencia:**

- `routes/web.php` y `routes/api.php` exponen `/decolecta/search` sin autenticación.
- `DecolectaController::search()` recibe `company_id` y `documento` y devuelve nombre, dirección, email y teléfono de clientes.

**Impacto:**

Enumeración de clientes y fuga de PII de cualquier empresa.

**Recomendación:**

- Exigir autenticación o un token de kiosco.
- Limitar por empresa.
- Aplicar rate limiting.
- No devolver más datos de los necesarios.

---

### H-03 — `/marcar` público y fotos de asistencia en disco público

**Evidencia:**

- `routes/web.php`: `GET/POST /marcar` sin autenticación.
- `AttendanceController.php:114` guarda las fotos en `storage/app/public/marcaciones`.
- No hay rate limiting en el endpoint.
- Se aceptan DNI y descriptores faciales.

**Impacto:**

Fraude de marcación, fuerza bruta de DNI, exposición de fotos y datos biométricos mediante URLs públicas.

**Recomendación:**

- Guardar fotos en disco privado y servirlas con autorización.
- Rate limiting por IP/DNI.
- Token de kiosco para el marcador.
- Política de retención y privacidad para datos biométricos.

---

### H-04 — Registro público abierto

**Evidencia:**

- `routes/auth.php` expone `GET/POST /register` en el grupo `guest`.
- `RegisteredUserController::store()` crea usuarios sin rol ni empresa.

**Impacto:**

Cualquier persona puede crear una cuenta y acceder al sistema con el rol por defecto.

**Recomendación:**

- Deshabilitar el registro público o restringirlo a administradores.
- Si se mantiene, asignar rol mínimo y validar empresa.

---

### H-05 — Print Server sin autenticación

**Evidencia:**

- `print-server-node/server.js:35`: `HOST = '0.0.0.0'`.
- Endpoints `/print`, `/print-raw`, `/print-escpos-text` y `/open-drawer` sin token.
- CORS amplio y `express.json({ limit: '50mb' })`.

**Impacto:**

Cualquier equipo en la red puede imprimir o abrir el cajón. Una web maliciosa visitada en el POS podría intentar imprimir/abrir el cajón vía CORS.

**Recomendación:**

- Bind a `127.0.0.1` o a la interfaz necesaria.
- Token compartido entre Laravel y print server.
- CORS con allowlist.
- Firewall de Windows/Linux.
- Actualizar dependencias NPM.

---

### H-06 — `/autopedido/success/{orderId}` sin control de acceso

**Evidencia:**

- `AutoPedidoController::success()` hace `RestaurantOrder::findOrFail($orderId)` sin validar pertenencia.

**Impacto:**

Enumeración de pedidos kiosko, ítems y datos asociados.

**Recomendación:**

- URL firmada (`signed`) o token de pedido.
- Validar que el pedido pertenezca a la sesión/kiosko.

---

### H-07 — Fotos de asistencia y datos biométricos

**Evidencia:**

- Fotos en `storage/app/public/marcaciones`.
- Descriptor facial en la base de datos.
- Endpoint de marcación público.

**Impacto:**

Exposición de datos personales sensibles y posible incumplimiento de privacidad.

**Recomendación:**

- Almacenamiento privado, cifrado y acceso auditado.
- Retención definida.
- Consentimiento y política de privacidad.

---

### H-08 — Faltan controles de seguridad en cabeceras y HTTPS

**Evidencia:**

- `APP_URL` en HTTP.
- `SESSION_SECURE_COOKIE` vacío.
- No se observan CSP, HSTS, `X-Content-Type-Options` ni `Referrer-Policy`.

**Impacto:**

Mayor superficie para XSS, clickjacking y robo de sesión en redes inseguras.

**Recomendación:**

- Forzar HTTPS.
- `SESSION_SECURE_COOKIE=true`.
- CSP, HSTS, `X-Frame-Options`/`frame-ancestors`, `X-Content-Type-Options` y `Referrer-Policy`.

---

## 4. Hallazgos medios

### M-01 — Certificado demo con clave privada versionado

**Evidencia:**

- `app/CoreFacturalo/WS/Signed/Resources/certificate.pem`.
- Contiene `BEGIN PRIVATE KEY`.
- Es un certificado de **demostración**, vencido en 2019.

**Impacto:**

Aunque no parece una clave de producción, una clave privada no debe estar en el repositorio.

**Recomendación:**

- Retirar del repositorio/historial.
- Rotar si alguna vez se usó.
- Mantener certificados reales solo en `storage/app/certificates` y fuera de Git.

---

### M-02 — `/virtual-prebill/{slug}` público

**Evidencia:**

- `VirtualPrebillController::show($slug)` no exige autenticación.
- El slug de mesa es predecible (`mesa01`, etc.).
- Expone mesa, pedido, ítems y totales.

**Impacto:**

Exposición de información operativa si el slug se comparte o enumera.

**Recomendación:**

- URL firmada o token por mesa.
- Rate limiting.
- Retención/rotación del slug.

---

### M-03 — Backup/Restore con `exec` y SQL arbitrario

**Evidencia:**

- `BackupController::run()` y `restore()` usan `exec`.
- El path de backup es controlable.
- El restore ejecuta un archivo SQL subido.
- Rutas dentro del grupo `admin`.

**Impacto:**

Un administrador comprometido puede escribir/restaurar archivos y ejecutar SQL arbitrario. El riesgo es menor por ser admin, pero debe restringirse.

**Recomendación:**

- Limitar a un propietario/superadmin.
- Validar/allowlist del path.
- No permitir restore arbitrario desde la web.
- Usar `escapeshellarg` (ya se usa) y tareas programadas externas.

---

### M-04 — Rutas de prueba y logout por GET

**Evidencia:**

- `/test-json`, `/test-post` y `/test-restaurant-open/{id}`.
- `GET /logout`.

**Impacto:**

Superficie innecesaria y CSRF de cierre de sesión.

**Recomendación:**

- Eliminar rutas de prueba.
- Usar solo `POST /logout`.

---

### M-05 — Contraseña de OpenSSL en línea de comandos

**Evidencia:**

- `CompanyController.php:108,125,162,178` usan `-passin pass:...`.

**Impacto:**

La contraseña del certificado puede verse en la lista de procesos mientras se ejecuta.

**Recomendación:**

- Usar `-passin file:` con archivo temporal de permisos restringidos.
- Limpiar el temporal después.

---

### M-06 — Faltan políticas de seguridad y monitoreo

**Evidencia:**

- No hay CSP/HSTS.
- No hay escaneo automatizado de secretos.
- `composer audit`/`npm audit` no están integrados en CI.

**Impacto:**

Mayor probabilidad de que vulnerabilidades o secretos pasen desapercibidos.

**Recomendación:**

- Añadir `composer audit` y `npm audit` a CI.
- Escaneo de secretos (gitleaks/trufflehog).
- Dependabot/Renovate.
- Revisión periódica de accesos y backups.

---

## 5. Controles positivos encontrados

- CSRF activo en rutas web.
- Login con rate limiting (5 intentos) en `LoginRequest`.
- Reglas de contraseña de Laravel.
- Middleware `auth`, `IsAdmin` y permisos por rol.
- `authorize()` en caja, asistencia y otros módulos.
- Certificados de empresa en `storage/app/certificates` (privado).
- Uso de `escapeshellarg` en comandos de certificado y backup.
- `.env` no versionado.
- `APP_KEY` presente.
- `SystemLock` para bloqueo del sistema.
- Validación de imágenes y consultas Eloquent parametrizadas.
- Contraseña propia para anulaciones y permisos separados por rol.

---

## 6. Prioridad de remediación

1. **Actualizar dependencias** PHP y NPM.
2. **Corregir `.env` de producción** (`APP_DEBUG=false`, HTTPS, cookies seguras).
3. **Sacar backup y certificado de Git/historial** y rotar credenciales.
4. **Deshabilitar cuentas demo** y cambiar contraseñas.
5. **Corregir XSS** en KDS, restaurante y autopedido.
6. **Aislar por empresa** y proteger endpoints públicos.
7. **Asegurar print server** (bind/token/CORS/firewall).
8. **Mover fotos de asistencia** a almacenamiento privado.
9. **Añadir CSP/headers** y monitoreo de dependencias.

---

## 7. Limitaciones

- Revisión estática; no se ejecutaron exploits.
- No se tuvo acceso al servidor de producción, WAF, TLS ni cabeceras reales.
- El `.env` revisado es la copia local; debe verificarse el del servidor.
- Los resultados de dependencias pueden cambiar con nuevas advisories.
- No se auditó infraestructura externa (DNS, proxy, firewall, backups externos).

---

## 8. Recomendación final

El sistema tiene buenos controles base (CSRF, rate limit de login, permisos por rol, validación de imágenes y Eloquent), pero **no está listo para producción** sin corregir los puntos críticos anteriores, especialmente dependencias, configuración de debug, secretos versionados, cuentas por defecto y XSS.
