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
- **Routes**: `web.php` ~210 lines. Public routes (no auth) at top, then `auth` group, then `admin` sub-group. Restaurant routes are in the `auth` group.
- **Kiosko**: Mesa virtual en `restaurant_tables` con `is_for_kiosko=true`. No aparece en floor plan ni gestión. Usa `scopeExcludeKiosko()`.
  - Flujo de 2 pasos: "Enviar a Cocina" → `SENT_TO_KITCHEN`, luego "Cobrar" → `COMPLETED`
  - Numeración `A-001` ligada a caja abierta actual (resetea al cerrar/abrir caja)
  - `confirmOrder()` valida que haya caja abierta antes de crear pedido
  - Botón "Eliminar" disponible; pide admin password si ya fue enviado a cocina
- **SUNAT**: Boletas (03), NC/ND de boletas, and boleta voids go via **Resumen Diario** (SummaryService). Facturas (01) and their NC/ND go via **BillSender** (GreenterService). NV never sent to SUNAT.
- **PEM-first certificate**: All Greenter services (`setupSee()`) search for `.pem` file first (OpenSSL 3.0 compatible). Falls back to PKCS12. PEM extracted at upload via OpenSSL 1.1.1 CLI (Git Bash).
- **SOAP username**: Must be only the user part (e.g. `FACTURA1`) without RUC prefix. Greenter concatenates RUC+user automatically (`$ruc.$user`).
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
- The `PENDING_PAYMENT` status for kiosko orders is in the `status` ENUM of `restaurant_orders` (added via migration, not in original ENUM).
- `DOCUMENTACION_SISTEMA.md` contains detailed docs (~2400 lines). Read it for SUNAT error codes, module docs, and troubleshooting.
- **Elementos Auxiliares**: New module with CRUD at Restaurante → Elementos Auxiliares. Chips appear in product modal (POS + autopedido). Stored as JSON array in `restaurant_order_items.auxiliary_items`. Displayed in KDS and kitchen tickets.
- **Autopedido modal**: Product selection opens a modal with quantity (+/−), kitchen notes (with virtual keyboard), and auxiliary items chips. Cart stores notes + aux items per product.
- **Virtual keyboard**: Used for search input AND modal notes textarea. Driven by `activeInput` variable — `openKeyboard(input)` sets it, `pressKey`/`pressBackspace` write to `activeInput.value` using `selectionStart/End`.
- **Emojis in thermal tickets**: Do NOT use emojis (🧾, ✅, etc.) in ESC/POS tickets. Printers use CP850 encoding which garbles UTF-8 emojis. Use plain text alternatives.
- **Print autopedido ticket**: `PrintService::printAutoPedidoTicket()` was missing `$this->processQueue()` — always verify that print methods call `processQueue()` after `queuePrint()`.
- **PlainTextTicket::kitchenTicket**: Had a broken `$dests` filter that skipped ALL items (`$dests = ['cocina'=>'', ...]` where `$dest !== ''` was always true). Removed since `printKitchenOrder` already groups by destination.

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
