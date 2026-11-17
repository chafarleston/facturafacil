# Pendientes: pedidos que se pierden / concurrencia de restaurante

**Fecha:** 2026-11-07  
**Contexto:** 1 cajero y varios mozos (escenario real: ~5 mozos).  
**Base analizada:** `facturafacil`.
**Estado:** los riesgos criticos de perdida de items ya se corrigieron; quedan tareas de endurecimiento, datos historicos y pruebas.

> No tocar codigo ni ejecutar cambios en el cliente sin backup, rama limpia y prueba en copia/staging.

---

## 1. Ya corregido (commit `5a5073b`)

- `chargeOrder` / `splitChargeOrder`: transaccion unica con bloqueo de pedido, items, caja y serie.
- Reconsulta de items antes de cobrar y antes de dividir.
- `openTable` / `lockTable`: transaccion y bloqueo de mesa; numeracion de pedidos serializada por empresa.
- `sendToKitchen` y `KitchenBatchService::createFromItems`: reconsulta de items `PENDING` dentro del bloqueo; evita lotes duplicados.
- `syncOrderStatus`: bloquea el pedido y no sobrescribe `COMPLETED`/`CANCELLED`.
- `updateItem`, `removeItem`, `saveOrderNotes`, `moveTable`, `mergeOrder`, `closeOrder`, `cancelOrder`: version/bloqueo.
- `order_version` (hash de pedido + items): las acciones desde una vista vieja responden `409 requires_reload`.
- Frontend: refresca el pedido abierto en el polling, renueva el bloqueo cada 60 s y libera la mesa al cobrar/cerrar/anular.
- IGV de linea corregido en el detalle de factura.
- Limpieza histórica aplicada en la base anterior del cliente (ya no usada): 19 items y 1,581 lotes marcados `CANCELLED`; backup en `backups/pre-clean-orphans-20261107-171548.sql`; 0 huerfanos activos.
- Limpieza de pedidos vacios aplicada el 2026-11-08: **469 pedidos `CANCELLED` sin items** y **7 pedidos `COMPLETED` sin items pagados** (IDs 3,4,6,7,8,9,10). Backup: `backups/pre-delete-orders-20261108-000801.sql`. Facturas e items pagados intactos.

---

## 2. Pendientes por prioridad

### P0 - Antes de seguir operando con varios mozos

- [x] **Probar concurrencia real con dos navegadores.**
  - Probado: bloqueo de mesas, identificación del usuario y ausencia de pérdida/eliminación de productos entre navegadores.
  - Falta automatizar estos escenarios para regresión.

- [x] **Definir que hacer con los 6 pedidos `COMPLETED` que aun tienen items impagos (10 items).**
  - Verificado en la base actual: no quedan pedidos de esa lista con items impagos.
  - La pagina **Salud de Pedidos** permite conservar el historial o anular solo los items impagos.
  - No ejecutar `limpiar_huerfanos.bat --apply` ni `limpiar_pedidos_vacios.bat --apply` de nuevo sin revisar.

- [x] **Cerrar el tema de permisos de rutas.**
  - `RestaurantAccess` protege rutas de restaurante con `view_restaurant`, `manage_orders`, `view_kitchen`/`manage_kitchen` y `view_order_mode`.
  - `user` no accede al restaurante; pisos y CRUD de mesas quedan para admin.
  - Falta solo validar en staging el login real de admin, cajero, mozo y user.

### P1 - Datos historicos y numeracion

- [x] **Resolver los numeros de pedido duplicados.**
  - Resuelto 2026-11-08: **921 grupos** y **8,835 pedidos renumerados** conservando el pedido activo/mas antiguo.
  - `legacy_order_number` guarda el numero anterior; se agrego indice unico `(company_id, order_number)`.
  - Generacion futura mediante `OrderNumberService` y `restaurant_order_sequences`.
  - Backup: `storage/app/backup/pre-renumber-20261108-004929.sql`; auditoria: `order-number-map-20261108-004933.csv`.
  - Proceso: `reparar_numeros_pedido.bat` / `php artisan orders:renumber-duplicates --dry-run|--apply`.

- [x] **Revisar los lotes de pedidos `COMPLETED`.**
  - `getKitchenOrders` excluye pedidos `COMPLETED` y `CANCELLED`.
  - `kds:clean-stale-batches --apply` canceló 1,110 lotes históricos activos.
  - No se eliminaron pedidos ni facturas; backup en `backups/pre-kds-stale-20261114-133957.sql`.

- [x] **Fusion con destino desactualizado.**
  - `mergeOrder` valida `destination_order_id` y `destination_order_updated_at` antes de mover items.
  - Si el destino cambió, responde `409 requires_reload`.
  - Pendiente: prueba manual con dos mozos agregando al mismo destino.

### P2 - Kiosko, SUNAT e impresion

- [x] **Kiosko/AutoPedido transaccional.**
  - `AutoPedidoController::confirmOrder()` crea pedido e items dentro de `DB::transaction()`.
  - Validar en staging caja, productos, cantidades y total.

- [ ] **Sacar SUNAT e impresion de la transaccion de cobro.**
  - `createInvoiceFromItems()` llama a `sendInvoice()` dentro de la transaccion.
  - El auto-envio a cocina imprime dentro de `chargeOrder`/`splitChargeOrder`.
  - Si SUNAT o la impresora tardan, el pedido queda bloqueado.
  - Mejor: commit de BD, luego SUNAT/print con reintento y estado pendiente.

- [x] **Endurecer `cancelBatchIfEmpty` y estados de lote.**
  - Ahora usa `DB::transaction()` y `lockForUpdate()`.
  - Pendiente: validación manual en staging.

### P3 - Robustez y despliegue

- [ ] **Automatizar pruebas de concurrencia.**
  - Prueba manual con dos navegadores completada.
  - Ya existen pruebas MySQL de lotes, versionado, KDS y fusión.
  - Faltan pruebas automatizadas de doble cobro, doble apertura, doble envío simultáneo e ítem agregado durante cobro.

- [ ] **Lock de mesa en pestanas ocultas.**
  - El heartbeat corre cada 60 s; una pestana suspendida puede perder el lock a los 5 min.
  - La version evita perdida de datos, pero la UX muestra "mesa en uso".
  - Evaluar TTL mayor, liberacion al cerrar pestana o re-lock al volver.

- [x] **Revisar `addItem` sin version.**
  - El frontend envía `order_version` y el backend responde `409` si la vista esta desactualizada.

- [ ] **Desplegar al cliente con el actualizador seguro.**
  - Commit/push de los cambios y del nuevo `actualizar_cliente.ps1`.
  - Ejecutar primero `actualizar_cliente.bat -DryRun`.
  - Luego `actualizar_cliente.bat`.
  - La base se detecta automáticamente desde `.env` (`facturafacil`).
  - Verificar Print Server, scheduler, login admin/cajero/mozo y dos mesas concurrentes.

---

## 3. Pruebas de aceptacion para cerrar el tema

- [ ] Dos mozos abren la misma mesa: solo un pedido activo.
- [ ] Dos mozos agregan a la misma cuenta: no se pierde ninguno.
- [ ] Mozo edita mientras cajero cobra: no queda item impago en `COMPLETED`.
- [ ] Dos envios simultaneos: un solo lote por estacion.
- [ ] Dos cobros simultaneos: un solo comprobante.
- [ ] Cocina entrega mientras caja cobra: no se revierte el estado.
- [ ] Vista vieja intenta mover/fusionar/anular: recibe `409 requires_reload`.
- [ ] Mesa cobrada/cerrada/anulada queda `AVAILABLE` y sin lock.
- [ ] Caja, stock y detalle de factura cuadran con lo cobrado.
- [ ] No aparecen nuevos huerfanos en `restaurant_order_items`/`restaurant_kitchen_batches`.

---

## 4. Referencias utiles

- `app/Http/Controllers/Restaurant/RestaurantController.php`
- `app/Models/RestaurantOrder.php` (`getVersionAttribute`)
- `app/Services/KitchenBatchService.php`
- `app/Http/Controllers/AutoPedidoController.php`
- `resources/views/restaurant/index.blade.php`
- `routes/web.php`
- `AGENTS.md`
- `DOCUMENTACION_SISTEMA.md` seccion 32
- `ACTUALIZACION_GIT.md` seccion 16
- `limpiar_huerfanos.bat` / `scripts/limpiar_huerfanos.php`
- `limpiar_pedidos_vacios.bat` / `scripts/limpiar_pedidos_vacios.php`
- `actualizar_cliente.bat` / `actualizar_cliente.ps1`

---

## 5. Regla de seguridad

Antes de implementar cualquier pendiente:

1. `node .gitnexus/run.cjs analyze` si el indice esta viejo.
2. `node .gitnexus/run.cjs impact <simbolo> --repo facturafacil --direction upstream`.
3. Backup de BD + `.env` + certificados.
4. Implementar en rama/copia y probar con dos navegadores.
5. `node .gitnexus/run.cjs detect-changes --repo facturafacil` antes de commit.
6. No commit ni despliegue sin backup y prueba de rollback.
