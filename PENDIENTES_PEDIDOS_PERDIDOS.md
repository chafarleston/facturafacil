# Pendientes: pedidos que se pierden / concurrencia de restaurante

**Fecha:** 2026-11-07  
**Contexto:** 1 cajero y varios mozos (escenario real: ~5 mozos).  
**Base analizada:** `facturafacil2`.  
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
- Limpieza aplicada en `facturafacil2`: 19 items y 1,581 lotes marcados `CANCELLED`; backup en `backups/pre-clean-orphans-20261107-171548.sql`; 0 huerfanos activos.
- Limpieza de pedidos vacios aplicada el 2026-11-08: **469 pedidos `CANCELLED` sin items** y **7 pedidos `COMPLETED` sin items pagados** (IDs 3,4,6,7,8,9,10). Backup: `backups/pre-delete-orders-20261108-000801.sql`. Facturas e items pagados intactos.

---

## 2. Pendientes por prioridad

### P0 - Antes de seguir operando con varios mozos

- [ ] **Probar concurrencia real con dos navegadores.**
  - Mozo A abre mesa; mozo B intenta abrir la misma mesa.
  - Mozo A agrega/modifica; cajero cobra; la accion vieja debe recibir `409`.
  - Dos envios simultaneos a cocina no deben crear lotes duplicados.
  - Resultado esperado: no se pierden items ni se duplican comprobantes.

- [ ] **Definir que hacer con los 6 pedidos `COMPLETED` que aun tienen items impagos (10 items).**
  - Quedan: **1296, 9526, 9880, 10148, 10305, 10463**.
  - Incluye el pedido **10148** (`P-20261102-0002`, Mesa 25) con 3 Pilsen grandes impagos.
  - Los 7 pedidos sin pago (3,4,6,7,8,9,10) y los 469 pedidos vacios ya fueron eliminados.
  - No hay flujo de "reabrir y cobrar"; hoy `chargeOrder` rechaza `COMPLETED`.
  - Decision pendiente: reabrir/cobrar, anular items o dejar como historico.
  - No ejecutar `limpiar_huerfanos.bat --apply` ni `limpiar_pedidos_vacios.bat --apply` de nuevo sin revisar.

- [ ] **Cerrar el tema de permisos de rutas.**
  - Las rutas de restaurante estan bajo `auth`, sin `view_restaurant`/`manage_orders`.
  - `chargeOrder`, `closeOrder` y `cancelOrder` solo bloquean al rol `mozo`; verificar rol `user`.
  - Proteger CRUD de pisos/mesas, KDS y `getOrder`/`getPollData` por permiso/empresa.
  - No cambiar permisos sin probar login de admin, cajero, mozo y user.

### P1 - Datos historicos y numeracion

- [ ] **Resolver los numeros de pedido duplicados.**
  - La base tiene ~944 combinaciones empresa/numero repetidas; no hay indice unico.
  - No agregar `unique` sin limpiar/deduplicar primero.
  - Opciones: secuencia atomica por empresa/dia, usar el ID, o backfill controlado.
  - Revisar `RestaurantOrder::generateOrderNumber()` y el formato `P-YYYYMMDD-####`.
  - Kiosko usa `A-XXX` y su generador sigue sin bloqueo; tratarlo aparte.

- [ ] **Revisar los lotes de pedidos `COMPLETED`.**
  - El cobro puede ocurrir antes de que cocina termine; definir si el KDS debe seguir mostrando esos lotes.
  - La limpieza cancelo 1,581 lotes; confirmar que ninguno era trabajo real pendiente.
  - `getKitchenOrders` no filtra por estado del pedido: documentar o ajustar la regla de negocio.

- [ ] **Fusion con destino desactualizado.**
  - `mergeOrder` valida la version del pedido origen, pero no la del destino.
  - Agregar `destination_version`/recarga o validar el destino antes de mover items.
  - Probar fusionar mientras otro mozo agrega al destino.

### P2 - Kiosko, SUNAT e impresion

- [ ] **Kiosko/AutoPedido transaccional.**
  - `AutoPedidoController::confirmOrder()` crea pedido e items sin transaccion.
  - `generateKioskoOrderNumber()` puede duplicar `A-XXX` con varias terminales.
  - Validar caja, productos, cantidades y total; no dejar pedidos a medias.
  - Revisar stock y estados `PENDING_PAYMENT`/`SENT_TO_KITCHEN`.

- [ ] **Sacar SUNAT e impresion de la transaccion de cobro.**
  - `createInvoiceFromItems()` llama a `sendInvoice()` dentro de la transaccion.
  - El auto-envio a cocina imprime dentro de `chargeOrder`/`splitChargeOrder`.
  - Si SUNAT o la impresora tardan, el pedido queda bloqueado.
  - Mejor: commit de BD, luego SUNAT/print con reintento y estado pendiente.

- [ ] **Endurecer `cancelBatchIfEmpty` y estados de lote.**
  - Revisar bloqueo/transaccion al cancelar lotes vacios.
  - Confirmar que un lote cancelado no oculte trabajo real en KDS.

### P3 - Robustez y despliegue

- [ ] **Pruebas automatizadas de concurrencia.**
  - Hoy solo hay 2 tests (`ExampleTest`), sin MySQL real ni concurrencia.
  - Agregar pruebas de integracion: doble cobro, doble apertura, doble envio, item agregado durante cobro.
  - Probar en MySQL/staging, no solo SQLite.

- [ ] **Lock de mesa en pestanas ocultas.**
  - El heartbeat corre cada 60 s; una pestana suspendida puede perder el lock a los 5 min.
  - La version evita perdida de datos, pero la UX muestra "mesa en uso".
  - Evaluar TTL mayor, liberacion al cerrar pestana o re-lock al volver.

- [ ] **Revisar `addItem` sin version.**
  - Es aditivo, pero si se quiere consistencia total puede enviar `order_version`.
  - No es prioritario mientras el pedido este bloqueado y no este cerrado.

- [ ] **Desplegar al cliente con el actualizador seguro.**
  - Commit/push de los cambios y del nuevo `actualizar_cliente.ps1`.
  - Ejecutar primero `actualizar_cliente.bat -DryRun -Database facturafacil2`.
  - Luego `actualizar_cliente.bat -Database facturafacil2`.
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
