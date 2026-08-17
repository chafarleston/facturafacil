# FacturaFácil — AGENTS.md

## Stack
- Laravel 13.x, PHP 8.2+, MySQL 8.0
- Greenter 5.x (SUNAT XML/SOAP), mpdf (PDF), Endroid QR Code
- Print Server Node.js (localhost:9100), Vite + Tailwind CSS + AdminLTE
- No broadcasting in dev (BROADCAST_DRIVER=log)

## Commands
- `php artisan serve` — dev server
- `php artisan migrate` — run pending migrations
- `php artisan schedule:work` — required for print queue + SUNAT tasks
- `php artisan print:process-queue` — process pending print jobs (runs every min via scheduler)
- `php artisan sunat:send-daily-summary` — batch boletas into daily summary
- `php artisan sunat:check-summaries` — check pending summary tickets
- `php artisan sunat:retry-pending` — retry PENDIENTE/RECHAZADO invoices (boletas→summary, facturas→sendInvoice)
- `php artisan sunat:download-padron` — download+extract SUNAT padrón (deletes the ZIP after extracting; scheduled weekly Sunday 02:00 in Kernel.php)
- `php artisan cache:clear && php artisan view:clear && php artisan route:clear` — full cache flush (do this after any route/view change)
- `php -l path/to/file.php` — PHP syntax check (no linter configured)
- `php artisan tinker --execute="..."` — inline tinker (avoid heredoc in PowerShell)
- Tests: `php artisan test` (uses SQLite :memory:, no DB needed)

## Architecture notes
- **Routes**: `web.php` ~222 lines. Public routes (no auth) at top, then `auth` group, then `admin` sub-group. Restaurant routes are in the `auth` group. Las rutas de caja están en `auth` (con `authorize` por permiso), no en el grupo admin.
- **Kiosko**: Mesa virtual en `restaurant_tables` con `is_for_kiosko=true`. No aparece en floor plan ni gestión. Usa `scopeExcludeKiosko()`.
  - Flujo de 2 pasos: "Enviar a Cocina" → `SENT_TO_KITCHEN`, luego "Cobrar" → `COMPLETED`
  - Numeración `A-001` ligada a caja abierta actual (resetea al cerrar/abrir caja)
  - `confirmOrder()` valida que haya caja abierta antes de crear pedido
  - Botón "Eliminar" disponible; si ya fue enviado a cocina pide la contraseña del usuario autenticado con permiso `authorize_cancel_orders` (admin/cajero)
- **SUNAT**: Boletas (03), NC/ND de boletas, and boleta voids go via **Resumen Diario** (SummaryService). Facturas (01) and their NC/ND go via **BillSender** (GreenterService). NV never sent to SUNAT.
- **Permisos (roles)**: `cajero` tiene `view_invoices`, `create_invoices`, `send_sunat` (envía a SUNAT), `view_cashregisters` + `open_cashregister` (abre caja, NO la cierra), `manage_cash_movements` (registra ingresos/gastos de caja), `view_restaurant`/`manage_orders`, `authorize_cancel_orders` (autoriza eliminaciones/anulaciones de cocina con su propia contraseña) y **gestión completa de productos y categorías** (`view/create/edit/delete_products` y `view/create/edit/delete_categories`). `view_cashregister_history` (historial de cajas) lo tiene solo `admin` por defecto. El rol `user` NO tiene `view_invoices` ni `view_cashregisters` (no ve Comprobantes ni Caja); solo lectura de productos (`view_products`/`view_categories`). `superadmin` es un valor reservado en la lógica (`isSuperAdmin()`, `hasPermission`, `IsAdmin`), NO un rol de la BD; el acceso total efectivo es vía `admin`. Todos los permisos de productos/categorías se pueden añadir/quitar por rol desde Roles/Permisos.
- **Rutas por permiso (no middleware admin)**: las rutas de comprobantes (`/invoices*`, `/sunat-summaries*`, `/documents/{tipo}`) y de caja (`/cashregisters*`, `/cashregister/open`) se protegen con `$this->authorize('permission', 'send_sunat'|'view_invoices'|'create_invoices'|'view_cashregisters'|'open_cashregister')`. El cierre de caja (`close_cashregister`) se valida con `hasPermission()` en `CashRegisterController::close()`; si no se tiene, responde redirect + flash `permission_modal` (modal "Solo el Administrador puede ejecutar estas tareas"). El middleware `admin` (IsAdmin) se usa para productos/usuarios/series/empresas/etc.; en `IsAdmin::handle()` se permite además la **lectura** de productos (`products.index/show`, `products.composite.create`, `products.inventory.report*`) y categorías (`categories.index`) a quien tenga `view_products`/`view_categories`, y la **escritura** (crear/editar/eliminar/importar/exportar/composites y CRUD de categorías) a quien tenga `create_products`/`edit_products`/`delete_products`/`create_categories`/`edit_categories`/`delete_categories` (el cajero los tiene por defecto).
- **Dividir Cuenta**: modal en el pedido (no-mozo). Reparte items por cantidad en 2+ comprobantes (NV/Boleta/Factura), cada división con su cliente ("Clientes Varios" DNI 88888888 por defecto), tipo doc, método de pago y solo consumo. Marca items pagados con `paid_invoice_id`; el remanente se cobra con "Cobrar"; si no quedan items sin pagar → orden COMPLETED + mesa AVAILABLE.
- **PEM-first certificate**: All Greenter services (`setupSee()`) search for `.pem` file first (OpenSSL 3.0 compatible). Falls back to PKCS12. PEM extracted at upload via OpenSSL 1.1.1 CLI (Git Bash).
- **SOAP username**: Must be only the user part (e.g. `FACTURA1`) without RUC prefix. Greenter concatenates RUC+user automatically (`$ruc.$user`).
- **Series numbering**: Always use `Serie::getNextNumber()`, never query last invoice+1.
- **Clientes Varios**: Fallback DNI 88888888, name "CLIENTES VARIOS" when no customer selected.
- **Búsqueda de productos**: en el Restaurante es SOLO por descripción/nombre (no por código); la búsqueda por `codigo`/`codigo_barras` existe solo en el POS.
- **POS → caja**: `PosController::store()` actualiza la caja en vivo (`cantidad_ventas`, `total_ventas`, campo del método de pago). La apertura automática del cajón ocurre en pagos EFECTIVO (server-side `GET http://localhost:9100/open-drawer`); también hay botón manual "Caja" en POS y Restaurante.
- **Ingresos y Gastos**: módulo en submenú Caja (`/cash-movements`, permiso `manage_cash_movements`). Registra ingresos/egresos (motivo texto libre) ligados a la caja abierta; **requiere caja abierta** (sin caja → aviso, formulario bloqueado). Actualiza en vivo `cashregisters.total_ingresos/total_egresos`; anular un movimiento solo si la caja sigue ABIERTA. En el cierre se concilia **solo con efectivo** (`ventas_efectivo`): `saldo_esperado = monto_apertura + ventas_efectivo + total_ingresos − total_egresos` y `diferencia = monto_cierre − saldo_esperado`. Los pagos **Yape/Plin/Tarjeta son virtuales** (no cuentan como dinero en caja); se muestran por separado en el resumen (web, PDF A4, ticket 80mm y ticket ESC/POS).
- **Precuenta IGV**: `PlainTextTicket::prebillTicket()` usa `Company::getActiveIgvPercent()` (dinámico: 18% general / 10.5% restaurante), NO un valor fijo.
- **removeItem()**: item PENDING (no enviado a cocina) se borra físicamente; SENT/READY/DELIVERED se marca CANCELLED con `cancelled_from/at/by` y requiere la contraseña del usuario autenticado con permiso `authorize_cancel_orders` (helper `checkAuthorizedPassword()`, también usado por `cancelOrder`). El mozo no puede anular.
- **Cobro con pendientes**: `chargeOrder`/`splitChargeOrder` envían automáticamente a cocina/bar los items `PENDING` antes de facturar (ticket/impresión en modo `print`; evento KDS en modo `kds`), para que ningún producto se cobre sin pasar por preparación. El total de la factura incluye todos los items (enviados y pendientes) → el cierre de caja los cuenta.
- **Login/Restaurante**: `AuthenticatedSessionController::store()` redirige a `mozo` → `/restaurant` (el resto → `/dashboard`). `RestaurantController::index()` sin caja abierta NO redirige ni devuelve 403: renderiza la vista con el modal "Caja no aperturada" (Administrador/Cajero deben aperturar; botones Ir a Caja / Reintentar / Entendido). Cobrar sin caja responde JSON de error amigable.
- **Polling**: `pollActiveOrders` + `pollTableLocks` every 10s, `pollPrintServer` every 10s (solo si hay badge, modo impresión), `loadKitchenOrders` every 5s (SOLO en Modo KDS). `handlePollResponse()` redirige a `/login` si la respuesta es 401 (sesión expirada). Silent `.catch()` for polling, `showError()` for user actions.
- **Modo KDS vs Impresión** (`companies.order_mode`): los items del menú "KDS Cocina/Cocina 2/Bar" se ocultan si el modo es `print`; `loadKitchenOrders` solo corre en modo `kds` y detecta cambios en vivo vía `getKitchenOrders.order_mode` (detiene el polling y muestra aviso "KDS INACTIVO"); las acciones KDS (`markKitchenReady`/`deliverKitchenOrder`/`completeOrder`) responden 400 en modo `print`. `Company::orderMode()`/`mainCompanyId()` usan caché (`rememberForever`) invalidada por `Company::clearCache()` en `toggleMode` y en cambios de empresa (store/update/destroy/setMain).
- **8 printer slots**: cocina-1, cocina-2, bar-1, precuenta, precuenta2, precuenta3, caja, autopedido.
- **Asistencia / Control de Personal**: módulo de marcación por **DNI** con cámara opcional.
  - Controladores: `AttendanceController` (marcador kiosco `/marcar` + logs), `EmployeeController` (Personal), `ScheduleController` (Horarios), `AttendanceRuleController` (reglas), `AttendanceReportController` (reportes diario/semanal/mensual PDF/Excel).
  - Modelos: `Personal`, `Schedule`, `Attendance`, `AttendanceLog`, `AttendanceSetting`, `AttendanceDiscountRule`.
  - **3 modos de marcación** (`attendance_settings.modo_marcacion`): `dni` (solo DNI), `webcam` (identifica por rostro), `dni_webcam` (DNI + verificación facial). Configurable en Reglas de Tardanza junto con umbral de similitud (`reconocimiento_umbral`) y tiempo de éxito (`exito_segundos`).
  - **Reglas de tardanza/faltas**: umbral de falta, umbral de **falta grave** y **suspensión** por N graves consecutivas (`personal.suspendido`), descuentos por tramos 10-60 min (fijo o % del sueldo diario).
  - **Verificación facial**: `face-api.js` (`public/js/face-api.min.js`) + modelos (`public/models/face-api`). Descriptor facial (128-d) en `personal.face_descriptor`; comparación por distancia euclidiana en `AttendanceService::verifyFace()/identifyFace()`.
  - El marcador de kiosco requiere **contexto seguro** para la cámara (ver Repo quirks).

## Repo quirks
- Print server requires Node.js (see `print-server-node/`). The `scheduler.vbs` starts both Laravel scheduler and print server on Windows.
- All JS fetch calls must include `Accept: application/json` and `X-Requested-With: XMLHttpRequest` (silent redirect-to-login otherwise).
- Certificate upload must NOT use `mimes:p12,pfx` validation (rejects valid files). Use OpenSSL 1.1.1 CLI to verify.
- Table locks expire after 5 minutes. `unlockAllTables` endpoint available for admins.
- KDS has separate sections: "MOZO — Pedidos de Mesas" vs "KIOSKO — Autoservicio". Determined by `order_type` field.
- The `PENDING_PAYMENT` status for kiosko orders is in the `status` ENUM of `restaurant_orders` (added via migration, not in original ENUM).
- **Elementos Auxiliares**: New module with CRUD at Restaurante → Elementos Auxiliares. Chips appear in product modal (POS + autopedido). Stored as JSON array in `restaurant_order_items.auxiliary_items`. Displayed in KDS and kitchen tickets.
- **Autopedido modal**: Product selection opens a modal with quantity (+/−), kitchen notes (with virtual keyboard), and auxiliary items chips. Cart stores notes + aux items per product.
- **Virtual keyboard**: Used for search input AND modal notes textarea. Driven by `activeInput` variable — `openKeyboard(input)` sets it, `pressKey`/`pressBackspace` write to `activeInput.value` using `selectionStart/End`.
- **Marcador de asistencia (cámara)**: `getUserMedia` exige **contexto seguro** — `localhost`, HTTPS, o lanzar Chrome con `--unsafely-treat-insecure-origin-as-secure="http://IP:PUERTO"`. Incluye scripts `iniciar-marcador.bat` (normal) e `iniciar-marcador-kiosko.bat` (pantalla completa). Sin permiso de cámara, el marcador funciona igual (solo DNI, sin foto).
- **Emojis in thermal tickets**: Do NOT use emojis (🧾, ✅, etc.) in ESC/POS tickets. Printers use CP850 encoding which garbles UTF-8 emojis. Use plain text alternatives.
- **Print autopedido ticket**: `PrintService::printAutoPedidoTicket()` was missing `$this->processQueue()` — always verify that print methods call `processQueue()` after `queuePrint()`.
- **PlainTextTicket::kitchenTicket**: Had a broken `$dests` filter that skipped ALL items (`$dests = ['cocina'=>'', ...]` where `$dest !== ''` was always true). Removed since `printKitchenOrder` already groups by destination.
- **PrintService::printInvoice()**: `invoiceTicket()` is a stub (returns `''`); `printInvoice()` does NOT queue empty data (`if ($data === '') return;`). El comprobante se imprime por PDF de Greenter (`/pos/print/{id}/{format}`).
- **buildInvoice()**: private helper con firma `($invoice, $company)` — no es parte de la API pública.
- **cancelNotificationGrouped()**: imprime "Anulado por" (usa `cancelledBy` del primer item del grupo). Firma: `($order, $format='text', $dest='cocina')`.
- **Extensions**: composer.json requiere `ext-openssl`, `ext-xml`, `ext-zip`, `ext-soap`, `ext-intl` (soap = SUNAT SOAP; intl = NumberFormatter del total en letras).
- **Kernel.php**: `print:process-queue` agendado UNA vez (`everyMinute()`). `schedule:run` se invoca vía `scheduler.vbs` o una tarea de Windows creada fuera del repo.
- **`sunat:download-padron`**: elimina el ZIP tras extraer (el `.txt` del padrón se conserva).
- `DOCUMENTACION_SISTEMA.md` contains detailed docs (~3860 lines). Read it for SUNAT error codes, module docs, and troubleshooting. La auditoría doc↔código está en `INFORME_DISCREPANCIAS.md` (42 ítems, todos los accionables resueltos).

## Testing
- `php artisan test` — Unit + Feature (SQLite in-memory)
- No end-to-end or integration tests against real SUNAT
- Print queue not testable without a running print server

<!-- gitnexus:start -->
# GitNexus — Code Intelligence

This project is indexed by GitNexus as **facturafacil** (2898 symbols, 5611 relationships, 222 execution flows). Use the GitNexus MCP tools to understand code, assess impact, and navigate safely.

> Index stale? Run `node .gitnexus/run.cjs analyze` from the project root — it auto-selects an available runner. No `.gitnexus/run.cjs` yet? `npx gitnexus analyze` (npm 11 crash → `npm i -g gitnexus`; #1939).

## Always Do

- **MUST run impact analysis before editing any symbol.** Before modifying a function, class, or method, run `impact({target: "symbolName", direction: "upstream"})` and report the blast radius (direct callers, affected processes, risk level) to the user.
- **MUST run `detect_changes()` before committing** to verify your changes only affect expected symbols and execution flows. For regression review, compare against the default branch: `detect_changes({scope: "compare", base_ref: "main"})`.
- **MUST warn the user** if impact analysis returns HIGH or CRITICAL risk before proceeding with edits.
- When exploring unfamiliar code, use `query({search_query: "concept"})` to find execution flows instead of grepping. It returns process-grouped results ranked by relevance.
- When you need full context on a specific symbol — callers, callees, which execution flows it participates in — use `context({name: "symbolName"})`.
- For security review, `explain({target: "fileOrSymbol"})` lists taint findings (source→sink flows; needs `analyze --pdg`).

## Never Do

- NEVER edit a function, class, or method without first running `impact` on it.
- NEVER ignore HIGH or CRITICAL risk warnings from impact analysis.
- NEVER rename symbols with find-and-replace — use `rename` which understands the call graph.
- NEVER commit changes without running `detect_changes()` to check affected scope.

## Resources

| Resource | Use for |
|----------|---------|
| `gitnexus://repo/facturafacil/context` | Codebase overview, check index freshness |
| `gitnexus://repo/facturafacil/clusters` | All functional areas |
| `gitnexus://repo/facturafacil/processes` | All execution flows |
| `gitnexus://repo/facturafacil/process/{name}` | Step-by-step execution trace |

## CLI

| Task | Read this skill file |
|------|---------------------|
| Understand architecture / "How does X work?" | `.claude/skills/gitnexus/gitnexus-exploring/SKILL.md` |
| Blast radius / "What breaks if I change X?" | `.claude/skills/gitnexus/gitnexus-impact-analysis/SKILL.md` |
| Trace bugs / "Why is X failing?" | `.claude/skills/gitnexus/gitnexus-debugging/SKILL.md` |
| Rename / extract / split / refactor | `.claude/skills/gitnexus/gitnexus-refactoring/SKILL.md` |
| Tools, resources, schema reference | `.claude/skills/gitnexus/gitnexus-guide/SKILL.md` |
| Index, status, clean, wiki CLI commands | `.claude/skills/gitnexus/gitnexus-cli/SKILL.md` |

<!-- gitnexus:end -->
