# INFORME DE AUDITORÍA — DOCUMENTACION_SISTEMA.md vs código real

**Fecha:** 2026-08-02
**Método:** verificación puntual capítulo por capítulo (cap. 1 → anexo), mediante agentes de análisis paralelos + validación directa de los hallazgos críticos contra el código. Sin ediciones de código durante la auditoría.
**Alcance:** los 27 capítulos + anexo de `DOCUMENTACION_SISTEMA.md` (3,833 líneas).

## Resumen ejecutivo

| Severidad | Cantidad | Estado |
|-----------|----------|--------|
| 🔴 ALTA | 1 | ✅ **Resuelto** (ítem #1) |
| 🟠 MEDIA | 20 | 8 resueltos (#2–#9) · 12 pendientes |
| 🟡 BAJA | 12 | Pendiente |
| 🔵 INFO / NO VERIFICABLE | 10 | Informativo |

**Capítulos 100% COINCIDE:** 9, 10, 12, 16, 18, 22, 23, 24, 25, 27 (y 15 parcialmente).

> **Actualización (2026-08-02):** ítem #1 (apertura de cajón) corregido en el código. Ítems #2 (pivot), #3 (isAdmin), #4 (estado de Invoice), #5, #6 (enums), #7 (caja por permisos), #8 (SUNAT por permiso `send_sunat`) y #9 (removeItem) corregidos. Ver sección "Cambios aplicados" al final.

---

## 🔴 ALTA

| # | Cap. | Afirmación del doc | Realidad | Ubicación | Estado |
|---|------|--------------------|----------|-----------|--------|
| 1 | 11 | Apertura de cajón: comando `1B 40 1B 70 00 32 FF` (INIT + pin 2, 50ms/255ms) | `PosController::openDrawer()` generaba `1B 70 00 00 FF` (sin INIT, pin 0, t1=0, t2=255ms). **Corregido** → ahora genera `1B 40 1B 70 00 32 FF` | `app/Http/Controllers/PosController.php:274` | ✅ RESUELTO |

---

## 🟠 MEDIA

| # | Cap. | Afirmación del doc | Realidad | Ubicación |
|---|------|--------------------|----------|-----------|
| 2 | 2, 7 | Pivot `permission_role` | La tabla real es **`role_permission`** (migración + modelos). **Corregido** en `DOCUMENTACION_SISTEMA.md` y `TRD_FACTURAFACIL.md` | `database/migrations/2026_05_13_000001_create_roles_permissions_tables.php:31`, `app/Models/Role.php:22`, `app/Models/Permission.php:17` | ✅ RESUELTO |
| 3 | 3.1 | `isAdmin()` = admin \|\| superadmin | `isAdmin()` solo devuelve true para `admin`; superadmin se maneja en `hasPermission()`/middleware `IsAdmin`. **Corregido** el comentario del doc | `app/Models/User.php:45-48` | ✅ RESUELTO |
| 4 | 3.4 | Invoice campo `estado: ACTIVO\|ANULADO` | **No existe columna `estado`**; solo `sunat_estado`. **Corregido**: eliminadas las 2 asignaciones muertas (`'estado'=>'ACTIVO'` sobre Invoice), quitado `estado_sunat` del fillable y ajustado §3.4 del doc | `database/migrations/2024_01_01_000005_create_invoices_table.php:35`, `app/Models/Invoice.php:12-20` | ✅ RESUELTO |
| 5 | 3.5 | `status` en español (ABIERTO/ENVIADO A COCINA/LISTO/ENTREGADO/COMPLETADO/ANULADO) | Enum real en inglés: `OPEN/SENT_TO_KITCHEN/READY/DELIVERED/COMPLETED/CANCELLED` + **`PENDING_PAYMENT`** (kiosko) omitido en el doc. **Corregido** §3.5 | `database/migrations/2026_05_12_104627_create_restaurant_orders_table.php:17`, `2026_07_02_205824_add_pending_payment_to_restaurant_orders_status.php:14` | ✅ RESUELTO |
| 6 | 3.6 | `kitchen_status` en español (PENDIENTE/ENVIADO/LISTO/ENTREGADO/ANULADO) | Enum real: `PENDING/SENT/READY/DELIVERED/CANCELLED` (español solo como etiquetas). **Corregido** §3.6 | `database/migrations/2026_05_13_210541_add_cancelled_to_kitchen_status_enum.php:10` | ✅ RESUELTO |
| 7 | 15 | `/cashregisters`, `/cashregister/open`, `/cashregister/close` en grupo auth | Estaban en el sub-grupo **`admin`** (middleware `IsAdmin`), bloqueando al cajero pese a tener `open_cashregister`/`close_cashregister`. **Corregido**: rutas movidas a `auth` + autorización por permiso (`view_cashregisters`/`open_cashregister`/`close_cashregister`) | `routes/web.php`, `CashRegisterController.php` | ✅ RESUELTO |
| 8 | 15 | `/invoices*`, `/sunat-summaries*`, `/documents/{tipo}` en grupo auth+admin | Estaban fuera del grupo admin → **solo auth** (cualquier autenticado, incl. mozo, alcanzaba envío/anulación SUNAT). **Corregido** con enfoque por permiso `send_sunat` (cajero puede enviar; user/mozo bloqueados) | `routes/web.php`, `InvoiceController.php`, `SummaryController.php`, `DocumentController.php` | ✅ RESUELTO |
| 9 | 4 | `removeItem()` siempre marca CANCELLED | PENDING se elimina físicamente (`$item->delete()`); CANCELLED solo para SENT/READY/DELIVERED (con password admin + auditoría + ticket). **Corregido** §4.1 y §19.2.6 | `app/Http/Controllers/Restaurant/RestaurantController.php:268-331` | ✅ RESUELTO (doc) |
| 10 | 5.2, 19.9.3 | `invoiceTicket($invoice)` genera ticket | Es un **stub** que devuelve `''` → `PrintService::printInvoice()` encola un trabajo de impresión vacío | `app/Services/PlainTextTicket.php:156-159` |
| 11 | 5.3 | `buildInvoice($invoice)` público, 1 parámetro | Es **`private` con 2 parámetros obligatorios** `($invoice, $company)` | `app/Services/GreenterService.php:1316` |
| 12 | 5.2, 19.9.3 | `cancelNotificationGrouped($order, $dest)` | Firma real `($order, $format='text', $dest='cocina')`; además **no incluye el usuario** anulador (solo la variante individual `cancelNotification` lo muestra) | `app/Services/PlainTextTicket.php:175` |
| 13 | 8 | Precuenta usa `$company->getActiveIgvPercent()` | Usa `$order->igvPercent ?? 18` (atributo **nunca asignado**) → siempre imprime "IGV (18%)" aunque la empresa esté en modo restaurante (10.5%) | `app/Services/PlainTextTicket.php:150-151` |
| 14 | 6 | Tabla de 7 slots de impresora | Son **8**: falta el slot `autopedido` ("Auto Pedido") | `database/seeders/PrinterSeeder.php:10-19` |
| 15 | 6 | Comandos `DOUBLE ON/OFF` (`1B 21 30` / `1B 21 00`) | **No implementados** en `server.js` ni en `PlainTextTicket::getEscPos()` ni en ningún PHP del repo | `print-server-node/server.js:44-57`, `app/Services/PlainTextTicket.php:72-85` |
| 16 | 7 | Rol `superadmin` | **No existe registro** en la tabla `roles`; solo lógica hardcodeada en `User::hasPermission()` (`isAdmin() || isSuperAdmin()`). `SuperAdminSeeder` crea un usuario con `role='cajero'` | `database/seeders/PermissionsSeeder.php:66-131`, `app/Models/User.php:72` |
| 17 | 19.2.3 | Búsqueda numérica por código interno en restaurante | Solo filtra por **nombre** del producto; las tarjetas no exponen código | `resources/views/restaurant/index.blade.php:1116-1128,568-573` |
| 18 | 19.3 | `PosController::store()` "actualiza caja registradora" (paso 7) | **No toca la caja**; solo verifica que exista caja abierta. La actualización real está en `RestaurantController::createInvoiceFromItems()` | `app/Http/Controllers/PosController.php:54-187` |
| 19 | 19.8 | Ventas del mes "excluye NV" | `currentMonthSales` suma **todas** las invoices (solo excluye `sunat_estado='ANULADO'`); las NV sí cuentan | `app/Http/Controllers/DashboardController.php:91-99` |
| 20 | 20.4 | `soap_type_id=2 → FE_HOMOLOGACION` | Real: **`FE_PRODUCCION`** (la propia sección 20.4.1 del doc muestra el código correcto, contradiciendo esta línea) | `app/Services/GreenterService.php:1270-1274`, `SummaryService.php:59-63`, `SpecialDocumentService.php:60-64` |
| 21 | 26 | Script `clean_productos.php` en `storage/app/tmp/` | **No existe** (solo `clean_ventas.php` y `clean_split_products.php`) | `DOCUMENTACION_SISTEMA.md:3560` |

---

## 🟡 BAJA

| # | Cap. | Afirmación del doc | Realidad | Ubicación |
|---|------|--------------------|----------|-----------|
| 22 | 14, 19.12 | PermissionsSeeder "50+ permisos" | Define **46** permisos | `database/seeders/PermissionsSeeder.php:14-59` |
| 23 | 14 | `SunatProductSeeder` puebla "productos" | Puebla la tabla catálogo `sunat_products` (modelo `SunatProduct`), no `products` | `database/seeders/SunatProductSeeder.php:7` |
| 24 | 6 | `1D 56 00` = "Corte parcial" | `1D 56 00` es **corte total**; el parcial es `1D 56 01`. El byte coincide con el código, pero la etiqueta es errónea | `print-server-node/server.js:54-55`, `app/Services/PlainTextTicket.php:83` |
| 25 | 11 | "Usuario clickea 'Caja' en restaurante **o POS**" | `openCashDrawer()` solo existe en la vista de restaurante; la vista POS no tiene botón ni fetch al print server | `resources/views/restaurant/index.blade.php:613,1516` vs `resources/views/pos/index.blade.php` |
| 26 | 4 | Flujo envío a cocina: "7. Responder JSON con tickets" | El JSON solo devuelve `success` y `items_sent`; los tickets se imprimen internamente vía `printKitchenOrder()` | `app/Http/Controllers/Restaurant/RestaurantController.php:375-379` |
| 27 | 19.5 | "Todos los comprobantes se envían con `sendInvoice`" y "carga certificado .p12" | Las boletas (03) van por **Resumen Diario** (`SummaryService`); `setupSee()` es **PEM-first** (busca `.pem`, fallback a PKCS12) | `app/Http/Controllers/InvoiceController.php:344-386`, `app/Services/GreenterService.php:1232-1266` |
| 28 | 20.3 | Padrón SUNAT "extrae y limpia automáticamente" | El comando descarga y extrae el ZIP pero **no lo elimina** ni limpia | `app/Console/Commands/DownloadSunatPadron.php:28-53` |
| 29 | 20.7 | "index.blade.php (7 calls); kds.blade.php (3 calls)" | Hoy hay **24** calls en index y **4** en KDS (conteo desactualizado) | `resources/views/restaurant/index.blade.php`, `kds.blade.php` |
| 30 | 20.4 | Lista de dependencias Greenter 5.2.0 (core/ws/xml/lite) | Instalado **5.3.0** (la propia sección 20.16 documenta la actualización) | `composer.lock` |
| 31 | 20.4 | Requiere `ext-soap`, `ext-intl` | `composer.json` solo declara `ext-openssl`, `ext-xml`, `ext-zip` | `composer.json:9-11` |
| 32 | 20.16 | Campo `fechaEntrega` en modelo `Shipment` | Se llama **`fecEntregaBienes`** | `vendor/greenter/core/src/Core/Model/Despatch/Shipment.php:93` |

---

## 🔵 INFO / NO VERIFICABLE

| # | Cap. | Ítem |
|---|------|------|
| 33 | 1 | Versión de MySQL/MariaDB no pinneada en el repo (afirmación de entorno) |
| 34 | 4 | Texto exacto del `showConfirm` de cobro es dinámico (estructura coincide; difiere el texto literal) |
| 35 | 5.6 | Retención/Guía/Percepción: doc sugiere serie fija R001/T001/P001; real usa `$doc->serie` desde BD |
| 36 | 6 | Mecanismo "Windows Task Scheduler" no está en el repo (el intervalo de 1 min sí coincide) |
| 37 | 17 | `fetch` con `mode: no-cors` (configuración de cliente, no verificable en servidor) |
| 38 | 19.4.1 | `company_id` siempre `Company::getMainCompany()->id` (no hay precedencia request/usuario) |
| 39 | 19.5 | El PDF/QR no se genera dentro de `sendInvoice` (se genera bajo demanda vía `generatePdf()`/`generateTicketPdf()`) |
| 40 | 19.6 | Ruta `POST /products/store` inexistente (es resource `/products`); la unicidad de `codigo` se impone por índice compuesto de BD |
| 41 | 19.11 | `KitchenOrderUpdated` no se dispara en `addItem`/`updateItem`/`saveOrderNotes` (sí en removeItem/sendToKitchen/charge/split/etc.) |
| 42 | 20.16 | "lite/ws mismo commit entre 5.2.0 y 5.3.0" y "template despatch2022 +5 líneas" no verificables sin historial de vendor |

---

## Capítulos sin discrepancias

- ✅ **9** Módulo de Caja — flujo completo, líneas eliminadas, permisos, cierre con kiosko.
- ✅ **10** Procesos de Stock — decremento/incremento, productos compuestos.
- ✅ **12** Dashboard — resumen del mes, crecimiento, aceptados/pendientes, gráfico 30 días, top productos.
- ✅ **16 / 22** Comandos Artisan — `print:process-queue`, `sunat:*`, agendamiento en Kernel.
- ✅ **18** Print Server Node.js — instalación, scripts, endpoints, ejemplos curl.
- ✅ **23** Productos Compuestos — BD, modelo, controlador, rutas, vistas, descuento de stock, fix POS.
- ✅ **24** `precio_compra` y Reporte de Inventario — migración, vistas, Excel/PDF, menú.
- ✅ **25** Correcciones de Seguridad y Bugs — los 15 fixes (25.1–25.15) presentes y correctos.
- ✅ **27** Dividir Cuenta — verificado por separado; coincide con la documentación actualizada.

---

## Observaciones de impacto (no solo redacción)

1. **#13 prebillTicket (Cap. 8):** bug real — la precuenta impresa muestra IGV 18% para restaurantes en modo 10.5% (`$order->igvPercent` nunca se asigna).
2. **#10 invoiceTicket (Cap. 5/19):** `PrintService::printInvoice()` encola un ticket vacío (no afecta al PDF de Greenter, que es el flujo real de comprobante).
3. **#8 rutas de invoices/summaries/documents (Cap. 15):** cualquier usuario autenticado (mozo/user) puede acceder a envío/anulación SUNAT → riesgo de permisos.
4. **#1 openDrawer (Cap. 11):** ✅ resuelto — `PosController::openDrawer()` ahora genera `1B 40 1B 70 00 32 FF` (INIT + pin 2, 50ms/255ms), alineado con el print server y el doc.
5. **#13, #20, #32** son imprecisiones técnicas del doc que la propia documentación corrige en otras secciones (20.4.1, 20.16).

---

## Sugerencia de siguiente paso

La mayoría de discrepancias requieren **actualizar la documentación** (redacción, versiones, enums, rutas). Un subconjunto pequeño requiere **cambios de código**:
- ✅ `#1` — apertura de cajón: **EJECUTADO** (comando corregido).
- `#13` — precuenta con `getActiveIgvPercent()`.
- `#8` — decidir middleware para rutas de invoices/summaries/documents.
- `#10` — decidir si `invoiceTicket()` debe generar ticket ESC/POS o documentarse como no usado.
- `#17` — implementar (o descartar) búsqueda por código en restaurante.

---

## Cambios aplicados

| Fecha | Ítem | Archivo | Cambio |
|-------|------|---------|--------|
| 2026-08-02 | #1 | `app/Http/Controllers/PosController.php:274` | `openDrawer()` ahora genera `"\x1B\x40\x1B\x70\x00\x32\xFF"` (base64 `G0AbcAAy/w==` = INIT + drawer kick pin 2, 50ms/255ms), alineado con `print-server-node/server.js:412-415` y con el capítulo 11 del doc. Sintaxis OK, tests OK (falla solo el trivial pre-existente). |
| 2026-08-02 | #2 | `DOCUMENTACION_SISTEMA.md:83,603` · `TRD_FACTURAFACIL.md:71` | Tabla pivote corregida: `permission_role` → **`role_permission`** (el nombre real de la migración y de los modelos). Solo documentación; sin cambios de código. |
| 2026-08-02 | #3 | `DOCUMENTACION_SISTEMA.md:142` | Comentario de `isAdmin()` corregido: ya no dice "admin \|\| superadmin" sino que aclara que solo cubre `admin` y que `hasPermission()` otorga true a admin/superadmin. Solo documentación. |
| 2026-08-02 | #4 | `PosController.php:138`, `RestaurantController.php:1196`, `Invoice.php:20`, `DOCUMENTACION_SISTEMA.md:213` | Limpieza del campo fantasma `estado` en Invoice: eliminadas las 2 asignaciones muertas `'estado'=>'ACTIVO'` sobre `Invoice::create` (las de `GreenterService` eran de `Serie::create` y se conservaron), quitado `'estado_sunat'` del fillable y eliminada la línea `estado: ACTIVO\|ANULADO` de §3.4. Sin impacto en facturación electrónica (usa `sunat_estado`). Sintaxis OK, tests OK. |
| 2026-08-02 | #5 | `DOCUMENTACION_SISTEMA.md:222` | §3.5 corregido: `status` ahora muestra el enum real en inglés (`OPEN/SENT_TO_KITCHEN/READY/DELIVERED/COMPLETED/CANCELLED/PENDING_PAYMENT`), aclara que `PENDING_PAYMENT` es del kiosko y que los nombres en español son solo etiquetas de `statusLabel()`. Solo documentación. |
| 2026-08-02 | #6 | `DOCUMENTACION_SISTEMA.md:236` | §3.6 corregido: `kitchen_status` ahora muestra el enum real (`PENDING/SENT/READY/DELIVERED/CANCELLED`) y aclara que los nombres en español son solo etiquetas de `kitchenStatusLabel()`. Solo documentación. |
| 2026-08-02 | #7 | `DOCUMENTACION_SISTEMA.md` §15.2/§15.3 | Rutas de caja (`/cashregisters`, `/cashregister/open`, `/cashregister/close`) movidas de §15.2 (auth) a §15.3 (admin), marcadas "(admin)" — el código real las protege con middleware `IsAdmin`. Solo documentación. |
| 2026-08-02 | #7 (código) | `routes/web.php`, `CashRegisterController.php` | Caja pasa de middleware admin a **permisos**: rutas movidas al grupo auth; `authorize('permission','view_cashregisters')` en index/show/pdf/ticketPdf/printCaja. Ahora el **cajero puede abrir y cerrar caja** (tiene open/close_cashregister); user conserva solo vista; mozo → 403. |
| 2026-08-02 | #8 | `PermissionsSeeder.php`, `InvoiceController.php`, `SummaryController.php`, `DocumentController.php`, `invoices/show.blade.php`, `DOCUMENTACION_SISTEMA.md` §15.3 | SUNAT protegido por permiso `send_sunat`: agregado al rol **cajero**; `authorize('permission','send_sunat')` en send/destroy/NC/ND/resúmenes/documentos; `view_invoices` en lecturas de comprobantes; botones de SUNAT ocultos sin `@can('permission','send_sunat')`. Rol **user** perdió `view_invoices` y `view_cashregisters` (no ve esos módulos). Efecto: cajero+admin envían a SUNAT; user/mozo bloqueados. |
| 2026-08-02 | #9 | `DOCUMENTACION_SISTEMA.md` §4.1 y §19.2.6 | Opción A (doc): `removeItem()` documentado con su comportamiento real — PENDING → elimina físicamente; SENT/READY/DELIVERED → CANCELLED con password admin, auditoría y ticket; items pagados bloqueados (A4). Sin cambios de código (comportamiento intencional). |
