# INFORME DE AUDITORÍA — DOCUMENTACION_SISTEMA.md vs código real

**Fecha:** 2026-08-02
**Método:** verificación puntual capítulo por capítulo (cap. 1 → anexo), mediante agentes de análisis paralelos + validación directa de los hallazgos críticos contra el código. Sin ediciones de código durante la auditoría.
**Alcance:** los 27 capítulos + anexo de `DOCUMENTACION_SISTEMA.md` (3,833 líneas).

## Resumen ejecutivo

| Severidad | Cantidad | Estado |
|-----------|----------|--------|
| 🔴 ALTA | 1 | ✅ **Resuelto** (ítem #1) |
| 🟠 MEDIA | 20 | 20 resueltos (#2–#21) · 0 pendientes |
| 🟡 BAJA | 12 | 11 resueltos (#22–#32) · 1 pendiente |
| 🔵 INFO / NO VERIFICABLE | 10 | 2 documentados (#33, #34) · 8 pendientes |

**Capítulos 100% COINCIDE:** 9, 10, 12, 16, 18, 22, 23, 24, 25, 27 (y 15 parcialmente).

> **Actualización (2026-08-02):** ítem #1 (apertura de cajón) corregido en el código. Ítems #2 (pivot), #3 (isAdmin), #4 (estado de Invoice), #5, #6 (enums), #7 (caja por permisos), #8 (SUNAT por permiso `send_sunat`), #9 (removeItem), #10 (invoiceTicket), #11 (buildInvoice), #12 (cancelNotificationGrouped), #13 (IGV precuenta), #14 (slot autopedido), #15 (comandos DOUBLE), #16 (superadmin), #17 (búsqueda restaurante), #18 (POS actualiza caja), #19 (dashboard incluye NV), #20 (endpoint SOAP) y #21 (clean_productos.php) corregidos. Ver sección "Cambios aplicados" al final.

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
| 10 | 5.2, 19.9.3 | `invoiceTicket($invoice)` genera ticket | Es un **stub** que devuelve `''` → `PrintService::printInvoice()` encolaba un trabajo de impresión vacío. **Corregido** (Opción B): guard para no encolar datos vacíos + doc actualizado | `app/Services/PlainTextTicket.php:156-159`, `app/Services/PrintService.php:113` | ✅ RESUELTO |
| 11 | 5.3 | `buildInvoice($invoice)` público, 1 parámetro | Es **`private` con 2 parámetros obligatorios** `($invoice, $company)`. **Corregido** §5.3 | `app/Services/GreenterService.php:1316` | ✅ RESUELTO (doc) |
| 12 | 5.2, 19.9.3 | `cancelNotificationGrouped($order, $dest)` | Firma real `($order, $format='text', $dest='cocina')`; y no incluía el usuario anulador. **Corregido** (Opción B): doc + "Anulado por" en el ticket agrupado | `app/Services/PlainTextTicket.php:175` | ✅ RESUELTO |
| 13 | 8 | Precuenta usa `$company->getActiveIgvPercent()` | Usaba `$order->igvPercent ?? 18` (atributo **nunca asignado**) → siempre imprimía "IGV (18%)" aunque la empresa esté en modo restaurante (10.5%). **Corregido** (Opción B) | `app/Services/PlainTextTicket.php:150-151` | ✅ RESUELTO |
| 14 | 6 | Tabla de 7 slots de impresora | Son **8**: faltaba el slot `autopedido` ("Auto Pedido"), usado por `printAutoPedidoTicket()`. **Corregido** §6 | `database/seeders/PrinterSeeder.php:10-19` | ✅ RESUELTO (doc) |
| 15 | 6 | Comandos `DOUBLE ON/OFF` (`1B 21 30` / `1B 21 00`) | **No implementados** en `server.js` ni en `PlainTextTicket::getEscPos()` ni en ningún PHP del repo. **Corregido** (Opción A): filas eliminadas de §6 | `print-server-node/server.js:44-57`, `app/Services/PlainTextTicket.php:72-85` | ✅ RESUELTO (doc) |
| 16 | 7 | Rol `superadmin` | **No existe registro** en la tabla `roles`; solo lógica hardcodeada en `User::hasPermission()` (`isAdmin() || isSuperAdmin()`). `SuperAdminSeeder` crea un usuario con `role='cajero'`. **Corregido** (Opción A) §7 | `database/seeders/PermissionsSeeder.php:66-131`, `app/Models/User.php:72` | ✅ RESUELTO (doc) |
| 17 | 19.2.3 | Búsqueda numérica por código interno en restaurante | Solo filtra por **nombre** del producto; las tarjetas no exponen código. **Corregido** (Opción A): §19.2.3 ahora indica que la búsqueda es solo por descripción | `resources/views/restaurant/index.blade.php:1116-1128,568-573` | ✅ RESUELTO (doc) |
| 18 | 19.3 | `PosController::store()` "actualiza caja registradora" (paso 7) | No tocaba la caja; solo verificaba que exista caja abierta. **Corregido** (Opción B): ahora incrementa los contadores en vivo igual que `createInvoiceFromItems` | `app/Http/Controllers/PosController.php:54-187` | ✅ RESUELTO |
| 19 | 19.8 | Ventas del mes "excluye NV" | `currentMonthSales` suma **todas** las invoices (solo excluye `sunat_estado='ANULADO'`); las NV sí cuentan. **Corregido** (Opción A): §19.8 documenta que incluye NV (ventas reales) | `app/Http/Controllers/DashboardController.php:91-99` | ✅ RESUELTO (doc) |
| 20 | 20.4 | `soap_type_id=2 → FE_HOMOLOGACION` | Real: **`FE_PRODUCCION`** (la propia sección 20.4.1 del doc muestra el código correcto, contradiciendo esta línea). **Corregido** §20.4 | `app/Services/GreenterService.php:1270-1274`, `SummaryService.php:59-63`, `SpecialDocumentService.php:60-64` | ✅ RESUELTO (doc) |
| 21 | 26 | `clean_productos.php` / `clean_ventas.php` "almacenados en `storage/app/tmp/`" | Los scripts destructivos están en **`eliminar-ventas-productos/`** (con `readme.md`), no en `storage/app/tmp/` (donde solo hay una versión minimalista de `clean_ventas.php` y los `clean_split_*`). **Corregido** §26.1 (ubicación) | `eliminar-ventas-productos/clean_productos.php`, `eliminar-ventas-productos/clean_ventas.php` | ✅ RESUELTO (doc) |

---

## 🟡 BAJA

| # | Cap. | Afirmación del doc | Realidad | Ubicación |
|---|------|--------------------|----------|-----------|
| 22 | 14, 19.12 | PermissionsSeeder "50+ permisos" | Define **46** permisos. **Corregido** §14 y §19.12 | `database/seeders/PermissionsSeeder.php:14-59` | ✅ RESUELTO (doc) |
| 23 | 14 | `SunatProductSeeder` puebla "productos" | Puebla la tabla catálogo `sunat_products` (modelo `SunatProduct`), no `products`. **Corregido** §14 y §19.12 | `database/seeders/SunatProductSeeder.php` | ✅ RESUELTO (doc) |
| 24 | 6 | `1D 56 00` = "Corte parcial" | `1D 56 00` es **corte total**; el parcial es `1D 56 01`. El byte coincide con el código, pero la etiqueta era errónea. **Corregido** §6 | `print-server-node/server.js:54-55`, `app/Services/PlainTextTicket.php:83` | ✅ RESUELTO (doc) |
| 25 | 11 | "Usuario clickea 'Caja' en restaurante **o POS**" | `openCashDrawer()` solo existía en la vista de restaurante. **Corregido** (Opción B): botón + función agregados al POS, y apertura automática del cajón en pagos EFECTIVO | `resources/views/restaurant/index.blade.php:613,1516` vs `resources/views/pos/index.blade.php` | ✅ RESUELTO |
| 26 | 4 | Flujo envío a cocina: "7. Responder JSON con tickets" | El JSON solo devuelve `success` y `items_sent`; los tickets se imprimen internamente vía `printKitchenOrder()`. **Corregido** §4.1 | `app/Http/Controllers/Restaurant/RestaurantController.php:375-379` | ✅ RESUELTO (doc) |
| 27 | 19.5 | "Todos los comprobantes se envían con `sendInvoice`" y "carga certificado .p12" | Las boletas (03) van por **Resumen Diario** (`SummaryService`); las NV no se envían; `setupSee()` es **PEM-first** (busca `.pem`, fallback a PKCS12). **Corregido** §19.5 | `app/Http/Controllers/InvoiceController.php:344-386`, `app/Services/GreenterService.php:1232-1266` | ✅ RESUELTO (doc) |
| 28 | 20.3 | Padrón SUNAT "extrae y limpia automáticamente" | El comando descargaba y extraía el ZIP pero **no lo eliminaba**. **Corregido** (Opción A): el ZIP se borra tras extraer (el `.txt` del padrón se conserva) | `app/Console/Commands/DownloadSunatPadron.php:28-53` | ✅ RESUELTO |
| 29 | 20.7 | "index.blade.php (7 calls); kds.blade.php (3 calls)" | Hay **24** calls en index y **4** en KDS (conteo desactualizado). **Corregido** §20.7 | `resources/views/restaurant/index.blade.php`, `kds.blade.php` | ✅ RESUELTO (doc) |
| 30 | 20.4 | Lista de dependencias Greenter 5.2.0 (core/ws/xml/lite) | Instalado **5.3.0** (la propia sección 20.16 documenta la actualización). **Corregido** §20.4 | `composer.lock` | ✅ RESUELTO (doc) |
| 31 | 20.4 | Requiere `ext-soap`, `ext-intl` | `composer.json` solo declaraba `ext-openssl`, `ext-xml`, `ext-zip`. **Corregido** (Opción A): añadidas `ext-soap` y `ext-intl` (ambas usadas y cargadas) | `composer.json:9-11` | ✅ RESUELTO |
| 32 | 20.16 | Campo `fechaEntrega` en modelo `Shipment` | Se llama **`fecEntregaBienes`**. **Corregido** §20.16 | `vendor/greenter/core/src/Core/Model/Despatch/Shipment.php:93` | ✅ RESUELTO (doc) |

---

## 🔵 INFO / NO VERIFICABLE

| # | Cap. | Ítem |
|---|------|------|
| 33 | 1 | Versión de MySQL/MariaDB no pinneada en el repo (afirmación de entorno) | Aclarado en §1: el repo no pinnea la versión; el entorno del cliente usa MySQL 8.0.30 (ruta mysqldump en `BackupController.php:67`). **Documentado** | — | ✅ DOCUMENTADO |
| 34 | 4 | Texto exacto del `showConfirm` de cobro es dinámico (estructura coincide; difiere el texto literal) | Ajustado §4.1: mensaje dinámico (Total/Vuelto), "Sí" abre 80mm + recarga, "No" recarga. **Documentado** | `resources/views/restaurant/index.blade.php:2344-2354` | ✅ RESUELTO (doc) |
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
| 2026-08-02 | #10 | `app/Services/PrintService.php:113`, `DOCUMENTACION_SISTEMA.md` §5.2 y §19.9.3 | Opción B: `printInvoice()` ya no encola cuando `invoiceTicket()` devuelve vacío (`if ($data === '') return;`). Doc: `invoiceTicket()` marcado como stub/no-op; el comprobante se imprime por PDF de Greenter. Sin impacto en el flujo PDF real. Sintaxis OK, tests OK. |
| 2026-08-02 | #11 | `DOCUMENTACION_SISTEMA.md` §5.3 | `buildInvoice()` movido a la lista de helpers privados con su firma real `($invoice, $company)` y comentario aclaratorio. Solo documentación. |
| 2026-08-02 | #12 | `app/Services/PlainTextTicket.php:175-185`, `DOCUMENTACION_SISTEMA.md` §5.2 y §19.9.3 | Opción B: `cancelNotificationGrouped()` ahora imprime "Anulado por" (usa el `cancelledBy` del primer item del grupo). Doc: firma corregida a `($order, $format='text', $dest='cocina')`. La llamada en `PrintService::printCancelNotificationGrouped` no cambia. Sintaxis OK. |
| 2026-08-02 | #13 | `app/Services/PlainTextTicket.php:150` | Opción B: `prebillTicket()` ahora resuelve el IGV real con `Company::find($order->company_id)?->getActiveIgvPercent()` (fallback `?? 18`). La precuenta imprime "IGV (10.5%)" coherente con el monto para empresas en modo restaurante; sin cambio para empresas generales (18%). El doc §8.2 ya describía este comportamiento → queda correcto. Sintaxis OK, tests OK. |

Nota #12: en `cancelOrder()` la impresión agrupada ocurre ANTES de marcar los items CANCELLED, por lo que "Anulado por" no se renderiza ahí (el guard evita error). Pendiente opcional: reordenar `cancelOrder()` para imprimir tras cancelar (ver conversación).
| 2026-08-02 | #14 | `DOCUMENTACION_SISTEMA.md` §6 | Añadida la fila `Auto Pedido / autopedido / Autoservicio (kiosko)` a la tabla de slots de impresora (el seeder crea 8). Solo documentación. |
| 2026-08-02 | #15 | `DOCUMENTACION_SISTEMA.md` §6 | Opción A: eliminadas las filas `DOUBLE ON/OFF` de la tabla de comandos ESC/POS (no implementados en server.js ni PHP). Solo documentación. |
| 2026-08-02 | #16 | `DOCUMENTACION_SISTEMA.md` §7 | Opción A: `superadmin` retirado de la tabla de roles reales y documentado como valor reservado en la lógica (isSuperAdmin/hasPermission/IsAdmin) que no existe en la tabla `roles`, no se asigna por UI (`in:admin,user,mozo,cajero`) y no tiene usuario por defecto; acceso total efectivo vía `admin`. Solo documentación. |
| 2026-08-02 | #17 | `DOCUMENTACION_SISTEMA.md` §19.2.3 | Opción A: el doc ya indica que la búsqueda del restaurante es SOLO por descripción (se eliminó "Números: busca en codigo") y aclara que la búsqueda por código/código de barras solo existe en el POS (§19.3). Solo documentación. |
| 2026-08-02 | #18 | `app/Http/Controllers/PosController.php:177-192` | Opción B: `PosController::store()` ahora actualiza la caja en vivo tras crear la invoice: `cantidad_ventas +1`, `total_ventas += total`, y el campo del método de pago según `payment_method` (mismo mapeo que el recálculo: EFECTIVO/TARJETA/YAPE/PLIN → sus campos, default → ventas_otro). Sin riesgo de descuadre: `close()` recalcula desde invoices y sobrescribe. El doc §19.3 (paso 7) queda correcto. Sintaxis OK. |
| 2026-08-02 | #19 | `DOCUMENTACION_SISTEMA.md` §19.8 | Opción A: corregido "excluye NV" → el dashboard cuenta TODAS las invoices no anuladas (incluye NV, documento por defecto del restaurante). Verificado con datos reales del mes: S/ 8,159 incluye S/ 6,889 en NV (84%); excluirlas dejaría S/ 1,270. Sin cambios de código (el dashboard es correcto). |
| 2026-08-02 | #20 | `DOCUMENTACION_SISTEMA.md` §20.4 | Corregida la línea 1640: `FE_HOMOLOGACION` → **`FE_PRODUCCION`** (el código y §20.4.1 ya usaban FE_PRODUCCION para soap_type_id=2). Solo documentación; sin impacto en SUNAT. |
| 2026-08-02 | #21 | `DOCUMENTACION_SISTEMA.md` §26.1 | Corregida la ubicación de los scripts: `clean_ventas.php` / `clean_productos.php` (destructivos de reset) están en **`eliminar-ventas-productos/`** (con `readme.md`), no en `storage/app/tmp/`. Nota: una primera corrección los cambió por error a `clean_split_products.php`; revertida a la referencia real. Solo documentación. |
| 2026-08-02 | #22 | `DOCUMENTACION_SISTEMA.md` §14 y §19.12 | Contado el seeder: son **46** permisos (no "50"/"50+"). Corregidas las dos referencias. Solo documentación. |
| 2026-08-02 | #23 | `DOCUMENTACION_SISTEMA.md` §14 y §19.12 | `SunatProductSeeder` descrito como catálogo de productos SUNAT (`sunat_products`, usado en el XML), no "productos de ejemplo". Solo documentación. |
| 2026-08-02 | #24 | `DOCUMENTACION_SISTEMA.md` §6 | Corregida la etiqueta del comando CUT: `1D 56 00` = corte total (parcial = `1D 56 01`). Solo documentación. |
| 2026-08-02 | #25 | `resources/views/pos/index.blade.php`, `app/Http/Controllers/PosController.php`, `DOCUMENTACION_SISTEMA.md` §11 | Opción B (ambas): (1) botón manual "Caja" + función `openCashDrawer()` en el POS (misma lógica del restaurante, con `showError`); (2) apertura automática del cajón al pagar en EFECTIVO vía `GET http://localhost:9100/open-drawer` desde `PosController::store()` (try/catch + guard de impresora; no rompe la venta). Doc §11 actualizado. Sintaxis OK, tests OK. |
| 2026-08-02 | #26 | `DOCUMENTACION_SISTEMA.md` §4.1 | Corregido el paso 7 del flujo de envío a cocina: "Responder JSON con tickets" → "Responder JSON (success + items_sent)" y aclarado que los tickets se imprimen internamente por cola (paso 6). Solo documentación. |
| 2026-08-02 | #27 | `DOCUMENTACION_SISTEMA.md` §19.5 | Bloque "Enviar a SUNAT" reescrito: NV no se envía; Boleta (03) → Resumen Diario (`sendBoletaToSummary`); Factura (01) → `sendInvoice`; `setupSee()` PEM-first (`.pem`, fallback `.p12`); PDF/QR bajo demanda. Solo documentación. |
| 2026-08-02 | #28 | `app/Console/Commands/DownloadSunatPadron.php:38-45` | Opción A: tras extraer el ZIP del padrón, se elimina `sunat_padron.zip` (el `.txt` extraído se conserva, lo usa la vista del padrón). El doc §20.3 "extrae y limpia" queda correcto. Sintaxis OK. |
| 2026-08-02 | #29 | `DOCUMENTACION_SISTEMA.md` §20.7 | Actualizados los conteos de `fetch`: index = 24, kds = 4 (con nota de que el número crece con features). Solo documentación. |
| 2026-08-02 | #30 | `DOCUMENTACION_SISTEMA.md` §20.4 | Actualizada la lista de dependencias Greenter: core/ws/xml/lite = 5.3.0 (xmldsig 5.0.3, report/htmltopdf 5.2.0, + gre-api 1.0.2). Verificado contra composer.lock. Solo documentación. |
| 2026-08-02 | #31 | `composer.json` | Opción A: añadidas `"ext-soap": "*"` y `"ext-intl": "*"` al `require` (ambas se usan: SOAP en SUNAT, intl en NumberFormatter del total en letras; verificadas cargadas con `php -m`). Lock sincronizado con `composer update --lock`. El doc §20.4 quedó correcto sin cambios. Sintaxis JSON OK, tests OK. |
| 2026-08-02 | #32 | `DOCUMENTACION_SISTEMA.md` §20.16 | Corregido el nombre del campo del vendor: `fechaEntrega` → **`fecEntregaBienes`** (propiedad + getter/setter reales de Shipment). Solo documentación. |
| 2026-08-02 | #33 | `DOCUMENTACION_SISTEMA.md` §1 | Añadida nota: la versión de BD no está pinneada en el repo; el entorno del cliente usa MySQL 8.0.30 (ruta mysqldump en `BackupController.php:67`); código compatible con MySQL 8 / MariaDB 10.4. Ítem INFO documentado. |
| 2026-08-02 | #34 | `DOCUMENTACION_SISTEMA.md` §4.1 | Actualizado el paso `showConfirm` del flujo de cobro: mensaje dinámico (Total/Vuelto + "¿Desea imprimir el comprobante?"), "Sí" → abre 80mm + `location.reload()`, "No" → `location.reload()`. Solo documentación. |

---

## Estado final de la auditoría

- **ALTA:** 1/1 resuelto (#1).
- **MEDIA:** 20/20 resueltos (#2–#21).
- **BAJA (12):** pendientes #22–#32.
- **INFO (10):** pendientes #33–#42.
