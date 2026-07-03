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
- `php artisan cache:clear && php artisan view:clear && php artisan route:clear` — full cache flush (do this after any route/view change)
- `php -l path/to/file.php` — PHP syntax check (no linter configured)
- `php artisan tinker --execute="..."` — inline tinker (avoid heredoc in PowerShell)
- Tests: `php artisan test` (uses SQLite :memory:, no DB needed)

## Architecture notes
- **Routes**: `web.php` has ~210 lines. Public routes (no auth) at top, then `auth` group, then `admin` sub-group. Restaurant routes are in the `auth` group.
- **Kiosko**: Mesa virtual en `restaurant_tables` con `is_for_kiosko=true`. No aparece en floor plan ni gestión. Usa `scopeExcludeKiosko()`.
- **SUNAT**: Boletas (03), NC/ND de boletas, and boleta voids go via **Resumen Diario** (SummaryService). Facturas (01) and their NC/ND go via **BillSender** (GreenterService). NV never sent to SUNAT.
- **PEM-first certificate**: All Greenter services (`setupSee()`) search for `.pem` file first (OpenSSL 3.0 compatible). Falls back to PKCS12. PEM extracted at upload via OpenSSL 1.1.1 CLI (Git Bash).
- **SOAP username**: Must be only the user part (e.g. `FACTURA1`) without RUC prefix. Greenter concatenates RUC+user automatically.
- **Series numbering**: Always use `Serie::getNextNumber()`, never query last invoice+1.
- **Clientes Varios**: Fallback DNI 88888888, name "CLIENTES VARIOS" when no customer selected.
- **Polling**: `pollActiveOrders` + `pollTableLocks` every 10s, `pollPrintServer` every 10s, `loadKitchenOrders` every 5s. Silent `.catch()` for polling, `showError()` for user actions.
- **8 printer slots**: cocina-1, cocina-2, bar-1, precuenta, precuenta2, precuenta3, caja, autopedido.

## Repo quirks
- Print server requires Node.js (see `print-server-node/`). The `scheduler.vbs` starts both Laravel scheduler and print server on Windows.
- All JS fetch calls must include `Accept: application/json` and `X-Requested-With: XMLHttpRequest` (silent redirect-to-login otherwise).
- Certificate upload must NOT use `mimes:p12,pfx` validation (rejects valid files). Use OpenSSL 1.1.1 CLI to verify.
- Table locks expire after 5 minutes. `unlockAllTables` endpoint available for admins.
- KDS has separate sections: "MOZO — Pedidos de Mesas" vs "KIOSKO — Autoservicio". Determined by `order_type` field.
- The `PENDING_PAYMENT` status for kiosko orders is in the `status` ENUM of `restaurant_orders`.
- `DOCUMENTACION_SISTEMA.md` contains detailed docs (~2400 lines). Read it for SUNAT error codes, module docs, and troubleshooting.

## Testing
- `php artisan test` — Unit + Feature (SQLite in-memory)
- No end-to-end or integration tests against real SUNAT
- Print queue not testable without a running print server
