# Documentación del Sistema FacturaFácil

---

## 1. Visión General

**FacturaFácil** es un sistema integral de gestión comercial, facturación electrónica SUNAT (Perú) y administración de restaurante, desarrollado en **Laravel 13.x** con **MySQL** y **Node.js**.

### Arquitectura General

```
┌─────────────────────────────────────────────────────────────┐
│                   Navegador Web (Cliente)                    │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────────┐  │
│  │Restaurant│  │   POS    │  │Facturación│  │Admin Panel │  │
│  └──────────┘  └──────────┘  └──────────┘  └────────────┘  │
└──────────────────────────┬──────────────────────────────────┘
                           │ HTTP
┌──────────────────────────▼──────────────────────────────────┐
│                   Servidor Laravel                           │
│  ┌─────────────┐  ┌──────────────┐  ┌──────────────────┐   │
│  │ Controllers  │  │  Services    │  │   Models/Eloquent│   │
│  └─────────────┘  └──────────────┘  └──────────────────┘   │
│                          │                                   │
│  ┌───────────────────────▼──────────────────────────────┐   │
│  │               Base de Datos MySQL                     │   │
│  └──────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────┘
                           │ HTTP (solo impresión servidor)
┌──────────────────────────▼──────────────────────────────────┐
│              Print Server Node.js (localhost:9100)           │
│  ┌──────────┐  ┌──────────────┐  ┌──────────────────────┐  │
│  │Impresora  │  │ Impresora    │  │   Cajón de Efectivo │  │
│  │ Local USB │  │ de Red (IP)  │  │   (Drawer Kick)     │  │
│  └──────────┘  └──────────────┘  └──────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

### Tecnologías

| Componente | Tecnología |
|-----------|-----------|
| Backend | PHP 8.2+ / Laravel 13.x |
| Frontend | Blade + AdminLTE + Chart.js |
| Base de Datos | MySQL 8.0+ / MariaDB 10.4+ |
| Facturación SUNAT | Greenter 5.x |
| Print Server | Node.js 18+ / Express |
| Impresión Térmica | ESC/POS via raw-print.ps1 |
| PDF | mpdf, Greenter HtmlToPdf |
| Build Tools | Vite + Tailwind CSS |

---

## 2. Base de Datos

### 2.1 Tablas Principales

| Tabla | Propósito |
|-------|-----------|
| `companies` | Empresas (RUC, datos SUNAT, certificado, IGV) |
| `users` | Usuarios del sistema (roles: admin, user, mozo, cajero, superadmin) |
| `customers` | Clientes (DNI/RUC, dirección, ubigeo) |
| `products` | Productos (código, precio, stock, IGV, kds_destination) |
| `categories` | Categorías de productos |
| `invoices` | Comprobantes emitidos (facturas, boletas, NV) |
| `invoice_items` | Items de comprobantes |
| `series` | Series documentales (F001, B001, NV01, etc.) |
| `floors` | Pisos del restaurante |
| `restaurant_tables` | Mesas del restaurante |
| `restaurant_orders` | Pedidos del restaurante |
| `restaurant_order_items` | Items de pedidos |
| `cashregisters` | Registros de apertura/cierre de caja |
| `printers` | Configuración de impresoras |
| `print_jobs` | Cola de impresión |
| `purchases` | Compras a proveedores |
| `purchase_items` | Items de compras |
| `suppliers` | Proveedores |
| `ubigeos` | Catálogo de ubigeos (departamento, provincia, distrito) |
| `roles` | Roles del sistema |
| `permissions` | Permisos del sistema |
| `role_user` | Asignación de roles a usuarios |
| `permission_role` | Asignación de permisos a roles |

### 2.2 Relaciones Clave

```
companies ──┬── users
            ├── customers
            ├── products ─── categories
            ├── invoices ─── invoice_items ─── products
            ├── series
            ├── floors ─── restaurant_tables
            ├── restaurant_orders ─── restaurant_order_items
            ├── cashregisters
            └── purchases ─── purchase_items
```

### 2.3 Migraciones Existentes

| Migración | Descripción |
|-----------|-------------|
| `2024_01_01_000001_create_companies_table` | Tabla de empresas |
| `2024_01_01_000002_create_customers_table` | Tabla de clientes |
| `2024_01_01_000003_create_products_table` | Tabla de productos |
| `2024_01_01_000005_create_invoices_table` | Tabla de comprobantes |
| `2024_01_01_000006_create_invoice_items_table` | Items de comprobantes |
| `2026_05_01_create_cashregisters_table` | Caja registradora |
| `2026_05_02_create_cashregisters_table` | Caja registradora (duplicada) |
| `2026_05_12_104536_create_tables_table` | Mesas del restaurante |
| `2026_05_12_104627_create_restaurant_orders_table` | Pedidos |
| `2026_05_13_000001_create_roles_permissions_tables` | Roles y permisos |
| `2026_05_13_230300_add_cancelled_from_to_restaurant_order_items` | Cancelación de items |
| `2026_05_25_113255_add_referencia_to_cashregisters_table` | Referencia en caja |
| `2026_05_25_114201_add_cancelled_by_to_restaurant_order_items` | Usuario que canceló |
| `2026_05_21_083855_add_igv_config_to_companies_table` | Configuración IGV |
| `2026_06_10_000001_add_lock_to_restaurant_tables` | Bloqueo de mesas |
| `2026_06_12_000001_create_special_documents_tables` | Documentos especiales |
| `2026_06_12_000002_add_fields_to_special_documents` | Campos extra docs especiales |
| `2026_06_12_000003_update_summary_correlativo_length` | Ampliar correlativo summary |
| `2026_06_19_000001_add_order_source_and_type` | order_source en invoices, order_type en restaurant_orders |

---

## 3. Modelos

### 3.1 User

```php
role: admin | user | mozo | cajero | superadmin
company_id → companies

// Métodos clave:
isAdmin()          // role === 'admin' || role === 'superadmin'
isUser()           // role === 'user'
isMozo()           // role === 'mozo'
isSuperAdmin()     // role === 'superadmin'
hasPermission($slug)  // Verifica permisos vía roles o role string
```

### 3.2 Company

```php
ruc, razon_social, nombre_comercial, direccion
departamento, provincia, distrito, ubigeo
telefono, email, logo
certificado_path, certificado_password, certificado_vence
tipo_contribuyente, estado
soap_type_id (01=Beta, 02=Producción)
soap_username, soap_password
order_mode (kds | print)
tax_type (general | restaurant)
igv_percent (default 18.00)
reduced_igv_percent (default 10.50)

// Métodos clave:
getActiveIgvPercent()  // Retorna % según tax_type
getIgvRate()           // getActiveIgvPercent() / 100
getMainCompany()       // Empresa principal o primera activa
```

### 3.3 Product

```php
company_id → companies
category_id → categories
codigo, codigo_barras, descripcion, codigo_sunat
umedida_codigo, precio, precio_minimo
tipo_afectacion, igv_percent, estado
stock, kds_destination (cocina | cocina2 | bar)
```

### 3.4 Invoice

```php
company_id → companies
customer_id → customers
tipo_documento: 01=Factura | 03=Boleta | NV=NotaVenta
serie, numero, full_number
fecha_emision, hora_emision, fecha_vencimiento
moneda, subtotal, gravado, igv, total, total_letras
metodo_pago, referencia_pago
sunat_estado: PENDIENTE | ENVIADO | ACEPTADO | RECHAZADO | ANULADO
estado: ACTIVO | ANULADO
codigo_hash (para QR)
```

### 3.5 RestaurantOrder

```php
company_id, table_id → tables
user_id → users
order_number
status: ABIERTO | ENVIADO A COCINA | LISTO | ENTREGADO | COMPLETADO | ANULADO
subtotal, igv, total, notes
```

### 3.6 RestaurantOrderItem

```php
restaurant_order_id → orders
product_id → products
product_name, quantity (decimal), unit_price, total
kitchen_status: PENDIENTE | ENVIADO | LISTO | ENTREGADO | ANULADO
notes, kds_destination
cancelled_from, cancelled_at, cancelled_by → users
sent_to_kitchen_at
```

### 3.7 CashRegister

```php
company_id, user_id
monto_apertura, monto_cierre
ventas_efectivo, ventas_tarjeta, ventas_yape, ventas_plin, ventas_otro
cantidad_ventas, total_ventas
estado: ABIERTA | CERRADA
fecha_apertura, fecha_cierre
observaciones, referencia
```

---

## 4. Controladores

### 4.1 RestaurantController

| Método | Ruta | Propósito |
|--------|------|-----------|
| `index()` | GET `/restaurant` | Vista principal con pisos, mesas, productos |
| `openTable($tableId)` | POST `/restaurant/tables/{id}/open` | Abre mesa (crea orden) |
| `getOrder($orderId)` | GET `/restaurant/orders/{id}` | Obtiene orden con items |
| `addItem($orderId)` | POST `/restaurant/orders/{id}/items` | Agrega producto al pedido |
| `updateItem($itemId)` | PUT `/restaurant/orders/items/{id}` | Actualiza cantidad/notas |
| `removeItem($itemId)` | DELETE `/restaurant/orders/items/{id}` | Cancela item (marca CANCELLED) |
| `sendToKitchen($orderId)` | POST `/restaurant/orders/{id}/send-to-kitchen` | Envía items a cocina |
| `closeOrder($orderId)` | POST `/restaurant/orders/{id}/close` | Cierra pedido (COMPLETED) |
| `cancelOrder($orderId)` | POST `/restaurant/orders/{id}/cancel` | Anula pedido completo |
| `chargeOrder($orderId)` | POST `/restaurant/orders/{id}/charge` | Cobra y genera factura |
| `moveTable($orderId)` | POST `/restaurant/orders/{id}/move-table` | Mueve pedido a otra mesa |
| `printPrebill($orderId)` | GET `/restaurant/orders/{id}/print-prebill` | Genera PDF de prebillete |
| `saveOrderNotes($orderId)` | POST `/restaurant/orders/{id}/notes` | Guarda notas del pedido |
| `getActiveOrders()` | GET `/restaurant/active-orders` | Órdenes activas (polling) |
| `getTableLocks()` | GET `/restaurant/locks` | Mesas bloqueadas (polling) |
| `lockTable($tableId)` | POST `/restaurant/tables/{id}/lock` | Bloquea mesa para usuario |
| `unlockTable($tableId)` | POST `/restaurant/tables/{id}/unlock` | Libera bloqueo de mesa |
| `unlockAllTables()` | POST `/restaurant/tables/unlock-all` | Libera todas las mesas |
| `completeOrder($orderId)` | POST `/restaurant/kitchen/{id}/complete` | Completa pedido desde KDS |
| `kitchenIndex()` | GET `/restaurant/kitchen` | Vista KDS |
| `getKitchenOrders()` | GET `/restaurant/kitchen-orders` | Órdenes de cocina (polling) |

#### Flujo de Agregar Item

```
addProductToOrder(productId) [JS]
    → Muestra modal de cantidad
    → confirmAddItem() [JS]
        → POST /restaurant/orders/{id}/items
            → addItem() [PHP]
                1. Validar request
                2. Buscar producto
                3. Buscar item existente PENDING con mismo product_id y notes
                4. Si existe: sumar cantidad
                5. Si no: crear nuevo item
                6. Recalcular totales
                7. Responder JSON
```

#### Flujo de Envío a Cocina

```
sendToKitchen() [JS]
    → Confirmación
    → POST /restaurant/orders/{id}/send-to-kitchen
        → sendToKitchen() [PHP]
            1. Obtener items PENDING
            2. Marcar como SENT + timestamp
            3. Asignar kds_destination desde producto
            4. Actualizar orden a SENT_TO_KITCHEN
            5. Disparar evento KitchenOrderUpdated
            6. Si modo impresión: generar tickets ESC/POS
            7. Responder JSON con tickets
```

#### Flujo de Cobro

```
processCharge() [JS]
    → POST /restaurant/orders/{id}/charge
        → chargeOrder() [PHP]
            1. Verificar permiso (no mozo)
            2. Verificar caja abierta
            3. Validar orden (no OPEN, con items)
            4. Buscar/crear serie y número
            5. Calcular IGV según empresa
            6. Crear Invoice + InvoiceItems
            7. Descontar stock (permite negativo)
            8. Marcar orden COMPLETED, mesa AVAILABLE
            9. Actualizar caja registradora
            10. Responder JSON
    → showConfirm("¿Desea imprimir?")
        → Sí: window.open /pos/print/{invoice}/80mm
        → No: location.reload()
```

### 4.2 PosController

| Método | Ruta | Propósito |
|--------|------|-----------|
| `index()` | GET `/pos` | Vista POS |
| `store()` | POST `/pos` | Procesa venta |
| `sendToSunat()` | POST `/pos/sunat/{id}` | Envía a SUNAT |
| `openDrawer()` | POST `/pos/open-drawer` | Devuelve config para abrir cajón |

### 4.3 CashRegisterController

| Método | Ruta | Propósito |
|--------|------|-----------|
| `index()` | GET `/cashregisters` | Vista caja (abrir/cerrar/historial) |
| `open()` | POST `/cashregister/open` | Abre caja (permiso: open_cashregister) |
| `close()` | POST `/cashregister/close` | Cierra caja (permiso: close_cashregister) |
| `show()` | GET `/cashregisters/{id}` | Resumen de caja |
| `pdf()` | GET `/cashregisters/{id}/pdf` | PDF A4 |
| `ticketPdf()` | GET `/cashregisters/{id}/ticket` | Ticket 80mm |
| `printCaja()` | POST `/cashregisters/{id}/print-caja` | Imprime en impresora Caja |

#### Flujo de Cierre de Caja

```
close() [PHP]
    1. Validar: monto_cierre requerido
    2. Verificar que no esté ya cerrada
    3. Verificar que no haya mesas abiertas en restaurante
    4. Obtener ventas del periodo (por datetime exacto)
    5. Calcular totales por método de pago y tipo documento
    6. Actualizar registro con montos
    7. Redirigir a resumen
```

### 4.4 InvoiceController

| Método | Ruta | Propósito |
|--------|------|-----------|
| `index()` | GET `/invoices` | Lista de comprobantes |
| `create()` | GET `/invoices/create` | Formulario de facturación |
| `store()` | POST `/invoices` | Guarda comprobante |
| `sendToSunat()` | GET `/invoices/{id}/send` | Envía a SUNAT (Factura→BillSender, Boleta→Summary) |
| `destroy()` | DELETE `/invoices/{id}` | Dar de baja (Factura→Voided, Boleta→Summary) |
| `creditNoteForm()` | GET `/invoices/{id}/credit-note` | Formulario nota de crédito |
| `sendCreditNote()` | POST `/invoices/{id}/credit-note` | Genera NC (Factura→BillSender, Boleta→Summary) |
| `debitNoteForm()` | GET `/invoices/{id}/debit-note` | Formulario nota de débito |
| `sendDebitNote()` | POST `/invoices/{id}/debit-note` | Genera ND (Factura→BillSender, Boleta→Summary) |
| `generatePdf()` | GET `/invoices/{id}/pdf` | PDF A4 |
| `generateTicketPdf()` | GET `/invoices/{id}/ticket` | Ticket 80mm |

### 4.5 Otros Controladores

| Controlador | Propósito |
|-------------|-----------|
| `ProductController` | CRUD productos + importación + duplicado |
| `CategoryController` | CRUD categorías |
| `CustomerController` | CRUD clientes |
| `SupplierController` | CRUD proveedores |
| `PurchaseController` | Compras (con incremento de stock) |
| `CompanyController` | CRUD empresas + certificado |
| `FloorController` | CRUD pisos |
| `TableController` | CRUD mesas |
| `SerieController` | CRUD series |
| `UserController` | CRUD usuarios + roles |
| `RoleController` | Gestión de roles y permisos |
| `PrinterController` | Configuración de impresoras + cola |
| `UbigeoController` | Catálogo ubigeos (departamento/provincia/distrito) |
| `SunatPadronController` | Vista y descarga del padrón SUNAT |
| `SummaryController` | Resúmenes diarios (listar, consultar tickets, enviar) |
| `DocumentController` | Documentos especiales (retención, guía, percepción) |
| `AutoPedidoController` | Kiosko de autopedidos (pantalla táctil pública) |
| `GreenterService` | Servicio de facturación SUNAT (no es controlador) |
| `SummaryService` | Resumen diario de boletas (no es controlador) |
| `SpecialDocumentService` | Documentos especiales SUNAT (no es controlador) |
| `SunatQrService` | Generación de código QR para comprobantes (no es controlador) |

---

## 5. Servicios

### 5.1 PrintService (`app/Services/PrintService.php`)

Maneja la impresión de tickets ESC/POS.

```php
printKitchenOrder($order)       // Ticket de cocina
printPrebill($order, $key)      // Precuenta (key: precuenta|precuenta2|precuenta3)
printCancelNotification($order, $item)    // Notificación de anulación individual
printCancelNotificationGrouped($order, $items)  // Notificación agrupada
printInvoice($invoice)          // Factura
processQueue()                  // Procesa cola de impresión
```

### 5.2 PlainTextTicket (`app/Services/PlainTextTicket.php`)

Genera texto ESC/POS para tickets térmicos.

```php
kitchenTicket($order)           // Ticket de cocina
prebillTicket($order)           // Precuenta
cancelNotification($order, $item)           // Anulación individual
cancelNotificationGrouped($order, $dest)    // Anulación agrupada
invoiceTicket($invoice)         // Factura
cashRegisterSummary($cashregister, $data)   // Resumen de caja
```

**Encoding**: Usa CP850 (PC850) con tabla de mapeo manual para ñ, tildes y mayúsculas acentuadas.

### 5.3 GreenterService (`app/Services/GreenterService.php`)

Integración con SUNAT para facturación electrónica.

```php
sendInvoice($invoice)          // Envía factura (01) a SUNAT (BillSender)
sendCreditNote($invoice, ...)  // Envía NC (07) - Factura→BillSender, Boleta→Summary
sendDebitNote($invoice, ...)   // Envía ND (08) - Factura→BillSender, Boleta→Summary
voidInvoice($invoice)          // Da de baja factura (Voided)
generatePdf($invoice)          // Genera PDF A4
generateTicketPdf($invoice)    // Genera PDF ticket 80mm
buildInvoice($invoice)         // Construye objeto Greenter para XML
```

setupSee() PEM-first: busca {ruc}_certificate.pem, si no existe usa .p12 con contraseña.

### 5.5 SummaryService (`app/Services/SummaryService.php`)

Resumen Diario para boletas y NC/ND de boletas.

```php
sendBoletaToSummary($invoice)          // Envía boleta (03) por Resumen Diario
sendDailySummary()                      // Agrupa boletas del día en un solo resumen
voidBoleta($invoice)                    // Anula boleta con estado=3
sendNoteToSummary($note, $orig, $tipo) // Envía NC/ND de boleta por Summary
checkTicketStatus($ticket)             // Consulta estado del ticket
```

### 5.6 SpecialDocumentService (`app/Services/SpecialDocumentService.php`)

Documentos especiales SUNAT.

```php
sendRetention($doc)           // Envía retención (R001, código 20)
sendDespatch($doc)            // Envía guía de remisión (T001, código 09)
sendPerception($doc)          // Envía percepción (P001, código 40)
```

### 5.7 PrintServerService (`app/Services/PrintServerService.php`)

Comunicación con el Print Server Node.js.

```php
isServerRunning()              // Verifica si el servidor responde
getAvailablePrinters()         // Lista impresoras del sistema
printText($printer, $text)     // Envía texto para imprimir
```

---

## 6. Módulo de Impresión

### 6.1 Arquitectura

```
Laravel (servidor) ─── HTTP POST ───→ Print Server (localhost:9100)
                                              │
                                    ┌─────────┴─────────┐
                                    │                   │
                              raw-print.ps1      Socket TCP
                              (USB/Local)       (IP:9100)
```

### 6.2 Print Server

**Ubicación:** `C:\laragon\www\print-server-node\server.js`

**Puerto:** 9100

**Endpoints:**

| Endpoint | Método | Propósito |
|----------|--------|-----------|
| `/status` | GET | Health check |
| `/printers` | GET | Lista impresoras del sistema |
| `/print` | POST | Imprime datos ESC/POS en base64 |
| `/print-raw` | POST | Imprime texto plano |
| `/print-escpos-text` | POST | Genera ticket desde texto |
| `/open-drawer` | GET | Abre cajón de efectivo |

**Slots de Impresora (configurables en `/printers`):**

| Slot | assigned_to | Uso |
|------|-------------|-----|
| Cocina 1 | cocina-1 | Cocina principal |
| Cocina 2 | cocina-2 | Cocina secundaria |
| Bar 1 | bar-1 | Barra |
| Precuenta | precuenta | Precuenta 1 |
| Precuenta 2 | precuenta2 | Precuenta 2 |
| Precuenta 3 | precuenta3 | Precuenta 3 |
| Caja | caja | Cajón + tickets |

**Archivos del Print Server:**

| Archivo | Propósito |
|---------|-----------|
| `server.js` | Servidor Express |
| `raw-print.ps1` | Impresión RAW vía API Windows |
| `start.bat` | Inicio con autoreinicio |
| `start-hidden.vbs` | Inicio oculto (sin ventana) |
| `start-minimized.vbs` | Inicio minimizado |
| `install.bat` | Instalador (accesos directos) |
| `create-shortcut.ps1` | Crea acceso en escritorio |
| `disable-quick-edit.ps1` | Desactiva Quick Edit Mode |

**Reintentos Automáticos:**
- Comando: `php artisan print:process-queue`
- Programado: cada 1 minuto vía Windows Task Scheduler
- Máximo: 3 intentos por trabajo

### 6.3 Comandos ESC/POS Soportados

| Comando | Hexadecimal | Propósito |
|---------|------------|-----------|
| INIT | 1B 40 | Inicializar impresora |
| CP850 (code page) | 1B 74 02 | Encoding latino |
| BOLD ON | 1B 45 01 | Negrita |
| BOLD OFF | 1B 45 00 | Fin negrita |
| ALIGN LEFT | 1B 61 00 | Alinear izquierda |
| ALIGN CENTER | 1B 61 01 | Centrar |
| ALIGN RIGHT | 1B 61 02 | Alinear derecha |
| DOUBLE ON | 1B 21 30 | Doble altura/ancho |
| DOUBLE OFF | 1B 21 00 | Fin doble |
| CUT | 1D 56 00 | Corte parcial |
| FEED | 1B 64 05 | Avanzar 5 líneas |
| DRAWER KICK | 1B 70 00 32 FF | Abrir cajón |

---

## 7. Módulo de Roles y Permisos

### 7.1 Roles del Sistema

| Rol (slug) | Descripción |
|-------------|-------------|
| `admin` | Acceso completo a todas las funcionalidades |
| `cajero` | POS, facturación, caja (abrir + cerrar) |
| `mozo` | Restaurante, cocina (sin cobrar ni anular) |
| `user` | POS, consultas, sin gestión de caja |
| `superadmin` | Acceso completo (equivalente a admin) |

### 7.2 Permisos

Los permisos se asignan a roles en la tabla `permission_role`. La verificación se realiza mediante `$this->authorize('permission', 'slug')` o `@can('permission', 'slug')` en las vistas.

**Mecanismo de verificación (`User::hasPermission`):**

```php
1. Si es admin/superadmin → TRUE (todo permitido)
2. Si tiene un rol en role_user con el permiso → TRUE
3. Si su string role coincide con un slug de rol con el permiso → TRUE
```

### 7.3 Permisos por Módulo

| Módulo | Permisos |
|--------|----------|
| Dashboard | view_dashboard |
| Empresas | view/create/edit/delete_companies |
| Usuarios | view/create/edit/delete_users |
| Roles | view/create/edit_roles |
| Permisos | view/create_permissions |
| Clientes | view/create/edit/delete_customers |
| Productos | view/create/edit/delete_products |
| Categorías | view/create/edit/delete_categories |
| Comprobantes | view/create_invoices, send_sunat |
| Compras | view/create_purchases |
| Proveedores | view/create_suppliers |
| Caja | view_cashregisters, open_cashregister, close_cashregister |
| POS | view_pos, use_pos |
| Restaurante | view_restaurant, manage_orders |
| Cocina (KDS) | view_kitchen, manage_kitchen |
| Series | view_series |
| Impresoras | view_printers, view_print_queue |
| Modo Pedidos | view_order_mode |

---

## 8. Configuración de IGV

### 8.1 Tipos de Impuesto

| Tipo | Porcentaje | Uso |
|------|-----------|-----|
| **General** | 18% (por defecto, editable) | IGV estándar |
| **Restaurante** | 10.5% (por defecto, editable) | Ley MYPE |

### 8.2 Dónde se Aplica

| Proceso | Archivo | Línea(s) |
|---------|---------|----------|
| Cobro en restaurante | `RestaurantController::chargeOrder()` | Uso de `$company->getIgvRate()` |
| Cálculo de totales | `RestaurantController::updateOrderTotals()` | Uso de `$company->getIgvRate()` |
| Factura desde módulo | `InvoiceController::store()` | Uso de `$company->getIgvRate()` |
| Venta POS | `PosController::store()` | Uso de `$company->getIgvRate()` |
| XML SUNAT facturas | `GreenterService::buildInvoice()` | Uso de `$company->getIgvRate()` |
| XML SUNAT NC | `GreenterService::sendCreditNote()` | Uso de `$company->getIgvRate()` |
| Ticket precuenta | `PlainTextTicket::prebillTicket()` | Uso de `$company->getActiveIgvPercent()` |
| Vistas | Varias | Display del porcentaje |

### 8.3 Configuración

En `/companies/{id}/edit` → "Configuración de IGV":
- Selector: General (18%) / Restaurante (10.5%)
- Campos editables para ambos porcentajes

---

## 9. Módulo de Caja

### 9.1 Flujo de Operación

```
1. ABRIR CAJA
   → POST /cashregister/open
   → Requiere permiso: open_cashregister
   → Validación: solo una caja abierta por empresa
   → Guarda: monto_apertura, referencia, usuario
   
2. OPERACIONES (durante turno)
   → Ventas POS: se registran en Invoice
   → Cobros restaurante: se registran en Invoice
   → Anulaciones: se registran en restaurant_order_items (cancelled_at)
   → Stock: se descuenta (permite negativo)
   
3. CERRAR CAJA
   → POST /cashregister/close
   → Requiere permiso: close_cashregister
   → Validación: no mesas abiertas en restaurante
   → Filtro ventas: por datetime exacto (fecha_emision + hora_emision)
   → Calcula: totales por método de pago y tipo documento
   → Genera: resumen con líneas eliminadas
```

### 9.2 Reporte de Líneas Eliminadas

Se genera a partir de `restaurant_order_items` con:
- `kitchen_status = 'CANCELLED'`
- `cancelled_from IS NOT NULL` (SENT, READY, o DELIVERED)
- `cancelled_at BETWEEN fecha_apertura AND fecha_cierre`

**Visualización:** Muestra cantidad, producto, usuario que canceló y hora.

---

## 10. Procesos de Stock

| Acción | Efecto en Stock | Archivo |
|--------|----------------|---------|
| Cobrar en restaurante | Decremento (permite negativo) | `RestaurantController::chargeOrder()` |
| Vender en POS | Decremento | `PosController::store()` |
| Facturar desde módulo | Decremento | `InvoiceController::store()` |
| Comprar (ingresar) | Incremento | `PurchaseController::store()` |

---

## 11. Impresión de Cajón de Efectivo

### Flujo

```
Usuario clickea "Caja" en restaurante o POS
  → POST /pos/open-drawer
      → Backend devuelve config de impresora Caja + comando base64
  → fetch a http://localhost:9100/print
      → mode: no-cors
      → Content-Type: application/x-www-form-urlencoded
      → body: printer=NAME&data=BASE64&mode=escpos
```

### Comando ESC/POS

```
1B 40        // INIT
1B 70 00 32 FF  // Drawer kick pin 2, timing 50ms/255ms
```

---

## 12. Dashboard

### Resumen del Mes

| Indicador | Fuente |
|-----------|--------|
| Ventas del mes | Suma de invoices del mes actual |
| Crecimiento vs mes anterior | Comparación con mes anterior |
| Total documentos | Conteo del mes |
| Aceptados SUNAT | Conteo con estado ACEPTADO |
| Pendientes | Conteo con estado PENDIENTE/ENVIADO |
| Gráfico 30 días | Ventas diarias del último mes |
| Top productos | Productos más vendidos del mes |

---

## 13. Componentes JavaScript

### 13.1 Polling (Tiempo Real sin WebSocket)

| Vista | Función | Intervalo |
|-------|---------|-----------|
| Restaurante | `pollActiveOrders()` | 3 segundos |
| Restaurante | `pollTableLocks()` | 3 segundos |
| Restaurante | `pollPrintServer()` | 10 segundos |
| KDS (Cocina) | `loadKitchenOrders()` | 5 segundos |

### 13.2 Funciones Globales del Restaurante

| Función | Propósito |
|---------|-----------|
| `selectTable(tableId)` | Selecciona mesa y abre modal |
| `loadOrder(orderId)` | Carga datos del pedido |
| `renderOrder(order)` | Renderiza items del pedido |
| `addProductToOrder(productId)` | Muestra modal de cantidad |
| `confirmAddItem()` | Envía POST para agregar producto |
| `sendToKitchen()` | Envía pedido a cocina |
| `showPrebillOptions(event)` | Muestra selector de impresora para precuenta |
| `printPrebillTo(printerKey)` | Imprime precuenta en impresora seleccionada |
| `showMoveTableModal()` | Muestra modal para mover mesa |
| `selectMoveTable(targetTableId)` | Envía POST para mover pedido |
| `showChargeModal()` | Muestra modal de cobro |
| `processCharge()` | Procesa el cobro |
| `openCashDrawer()` | Abre cajón de efectivo |
| `cancelOrder()` | Anula pedido |
| `searchProducts(query)` | Búsqueda de productos en tiempo real |
| `filterProducts(categoryId)` | Filtra productos por categoría |
| `pollTableLocks()` | Polling de bloqueos de mesas |
| `unlockAllTables()` | Desbloquea todas las mesas (admin/cajero) |
| `completeOrder(orderId)` | Marca pedido como completado desde KDS |

---

## 14. Seeders

| Seeder | Datos que crea |
|--------|---------------|
| `AdminUserSeeder` | Empresa demo + usuarios admin |
| `SuperAdminSeeder` | Usuario Cajero (Caja@gmail.com) |
| `TestUsersSeeder` | Usuarios demo (admin, mozo, user) + roles en pivot |
| `SeriesSeeder` | Series F001, B001, NV01, FC01, BC01, FD01, BD01 |
| `SunatProductSeeder` | Productos de ejemplo |
| `PermissionsSeeder` | 50+ permisos + roles (admin, mozo, cajero, user) |
| `PrinterSeeder` | 8 slots de impresora: cocina-1, cocina-2, bar-1, precuenta, precuenta2, precuenta3, caja, autopedido |
| `UbigeoSeeder` | 1874 registros de ubigeos |
| `CustomerSeeder` | Cliente "Clientes Varios" (DNI 88888888) |
| `DatabaseSeeder` | Ejecuta todos los anteriores |

---

## 15. Rutas API

### 15.1 Públicas

| Método | Ruta | Propósito |
|--------|------|-----------|
| GET | `/ubigeo/departamentos` | Lista departamentos |
| GET | `/ubigeo/provincias` | Provincias por departamento |
| GET | `/ubigeo/distritos` | Distritos |
| GET | `/ubigeo/by-codigo` | Ubigeo por código |
| GET | `/decolecta/search` | Búsqueda de clientes SUNAT |

### 15.2 Autenticadas (auth)

| Método | Ruta | Propósito |
|--------|------|-----------|
| GET | `/restaurant` | Vista restaurante |
| GET | `/restaurant/kitchen` | Vista KDS |
| GET | `/restaurant/active-orders` | Polling restaurante |
| GET | `/restaurant/locks` | Mesas bloqueadas (polling) |
| GET | `/restaurant/kitchen-orders` | Polling KDS |
| POST | `/restaurant/tables/{id}/open` | Abrir mesa |
| POST | `/restaurant/tables/{id}/lock` | Bloquear mesa |
| POST | `/restaurant/tables/{id}/unlock` | Desbloquear mesa |
| POST | `/restaurant/tables/unlock-all` | Desbloquear todas (admin/cajero) |
| POST | `/restaurant/orders/{id}/items` | Agregar producto |
| POST | `/restaurant/orders/{id}/send-to-kitchen` | Enviar a cocina |
| POST | `/restaurant/orders/{id}/charge` | Cobrar |
| POST | `/restaurant/orders/{id}/cancel` | Anular |
| POST | `/restaurant/orders/{id}/move-table` | Mover mesa |
| POST | `/restaurant/orders/{id}/print-prebill/{key}` | Imprimir precuenta |
| POST | `/restaurant/kitchen/{orderId}/complete` | Completar pedido desde KDS |
| GET | `/pos` | Vista POS |
| POST | `/pos` | Procesar venta |
| POST | `/pos/open-drawer` | Abrir cajón |
| GET | `/cashregisters` | Gestión de caja |
| POST | `/cashregister/open` | Abrir caja |
| POST | `/cashregister/close` | Cerrar caja |

### 15.3 Administrativas (auth + admin)

| Método | Ruta | Propósito |
|--------|------|-----------|
| GET | `/products` | Lista productos |
| POST | `/products/{id}/duplicate` | Duplicar producto |
| GET | `/invoices` | Lista comprobantes |
| GET | `/invoices/{id}/send` | Enviar a SUNAT |
| GET/POST | `/invoices/{id}/credit-note` | Nota de Crédito |
| GET/POST | `/invoices/{id}/debit-note` | Nota de Débito |
| DELETE | `/invoices/{id}` | Dar de baja en SUNAT |
| GET | `/invoices/{id}/generate-despatch` | Generar guía desde factura |
| GET | `/sunat-summaries` | Resúmenes diarios |
| POST | `/sunat-summaries/check-all` | Consultar tickets pendientes |
| POST | `/sunat-summaries/send-daily` | Enviar resumen diario |
| POST | `/sunat-summaries/retry-pending` | Reenviar pendientes |
| GET/POST | `/documents/{tipo}` | Documentos especiales (R/T/P) |
| GET | `/printers` | Configurar impresoras |
| GET | `/printers/queue` | Cola de impresión |
| POST | `/companies/{id}/certificate` | Subir certificado |
| GET | `/sunat-padron` | Vista padrón SUNAT |
| POST | `/sunat-padron/download` | Descargar padrón |
| GET | `/series` | Series de comprobantes |

---

## 16. Comandos Artisan

| Comando | Propósito |
|---------|-----------|
| `php artisan print:process-queue` | Procesa cola de impresión |
| `php artisan config:clear` | Limpia cache de configuración |
| `php artisan view:clear` | Limpia cache de vistas |
| `php artisan route:clear` | Limpia cache de rutas |
| `php artisan cache:clear` | Limpia cache de Laravel |
| `php artisan migrate` | Ejecuta migraciones |
| `php artisan db:seed` | Ejecuta seeders |
| `php artisan key:generate` | Genera APP_KEY |
| `php artisan route:list` | Lista rutas |
| `php artisan optimize` | Optimiza rendimiento |

---

## 17. Solución de Problemas Comunes

| Error | Causa | Solución |
|-------|-------|----------|
| `MissingAppKeyException` | APP_KEY inválida o cache desactualizado | `php artisan key:generate && php artisan config:clear` |
| `Duplicate entry for key 'products_company_id_codigo_unique'` | Código duplicado al duplicar producto | Corregido con `getNextProductCode()` que busca el código más alto |
| `Column 'company_id' cannot be null` | Usuario sin empresa asignada | `php artisan db:seed` o asignar manualmente |
| `Connection refused localhost:8080` | Reverb configurado sin servidor corriendo | Cambiar `BROADCAST_DRIVER=log` en `.env` |
| Print Server no responde | Quick Edit Mode de Windows | Usar `disable-quick-edit.ps1` o `start-hidden.vbs` |
| Cash drawer no abre | CORS bloqueando fetch local | Usar `mode: no-cors` + `Content-Type: application/x-www-form-urlencoded` |

---

## 18. Print Server Node.js (Referencia Rápida)

### Instalación en Cliente

```bash
cd print-server-node
npm install
```

### Inicio

| Método | Comando | Ventana |
|--------|---------|---------|
| Visible | `start.bat` | Sí (con autoreinicio) |
| Minimizado | `start-minimized.vbs` | Minimizada |
| Oculto | `start-hidden.vbs` | No (recomendado) |

### Acceso Directo + Inicio Automático

```bash
install.bat
```

### Endpoints

```bash
# Verificar estado
curl http://localhost:9100/status

# Listar impresoras
curl http://localhost:9100/printers

# Imprimir
curl -X POST http://localhost:9100/print \
  -H "Content-Type: application/json" \
  -d '{"printer":"EPSON","data":"BASE64","mode":"escpos"}'

# Abrir cajón
curl "http://localhost:9100/open-drawer?printer=EPSON"
# o
curl "http://localhost:9100/open-drawer?ip=192.168.1.100&port=9100"
```

---

## 19. Glosario

| Término | Significado |
|---------|-------------|
| KDS | Kitchen Display System (pantalla de cocina) |
| ESC/POS | Lenguaje de comandos para impresoras térmicas |
| CP850 | Code Page 850 (encoding latino para impresoras) |
| SUNAT | Superintendencia Nacional de Aduanas y Administración Tributaria |
| Greenter | Librería PHP para facturación electrónica SUNAT |
| IGV | Impuesto General a las Ventas (18% o 10.5%) |
| NV | Nota de Venta (comprobante sin envío SUNAT) |
| CDR | Constancia de Recepción SUNAT |
| .p12/.pfx | Formato de certificado digital |
| SOAP | Protocolo de comunicación con SUNAT |
| MYPE | Micro y Pequeña Empresa (IGV reducido 10.5%) |
| Quick Edit Mode | Modo de consola Windows que congela procesos al hacer clic |
| Raw Printing | Envío de datos directamente al puerto de la impresora |
| Drawer Kick | Comando ESC/POS para abrir cajón de efectivo |

---

## 19. Procedimientos Detallados

### 19.1 Instalación del Sistema

```bash
git clone <repo> facturafacil
cd facturafacil
composer install
cp .env.example .env   # configurar DB
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
cd print-server-node
npm install
```

En el cliente: `git pull` para actualizar. Los seeders crean empresa demo, usuarios, productos, series, impresoras, ubigeos y cliente por defecto.

---

### 19.2 Ciclo Completo del Pedido (Restaurante)

#### 19.2.1 Abrir Mesa

```
POST /restaurant/tables/{id}/open
→ RestaurantController::openTable()
   1. Busca orden activa para la mesa
   2. Si existe: la retorna (no duplica)
   3. Si no: crea RestaurantOrder con order_number (P-YYYYMMDD-NNNN), status OPEN
   4. Marca mesa como OCCUPIED
```

#### 19.2.2 Seleccionar Mesa (Frontend)

```
selectTable(tableId) [JS]:
   1. Lee data-order-id de la tarjeta de mesa
   2. Si tiene orden: loadOrder(orderId)
   3. Si no: openTable(tableId)
   4. Muestra modal lateral con tabs: Productos | Pedido
```

#### 19.2.3 Buscar Productos

```
searchProducts(query) [JS] — filtra productsData en tiempo real:
   - Letras: busca en descripcion
   - Números: busca en codigo (código interno)
   - Compatible con filtro por categoría (ambos se aplican simultáneamente)

filterProducts(categoryId) [JS] — muestra/oculta productos por categoría
```

#### 19.2.4 Agregar Producto al Pedido

```
addProductToOrder(productId) [JS] → modal de cantidad + notas
confirmAddItem() [JS] → POST /restaurant/orders/{id}/items
→ addItem() [PHP]:
   1. Valida: product_id existe, quantity ≥ 0.01, notes ≤ 500 chars
   2. Busca producto y orden
   3. Busca item existente PENDING con mismo product_id Y misma nota
   4. Si existe: suma cantidad
   5. Si no: crea nuevo item con product_name, quantity, unit_price (=product.precio),
      total = unit_price × quantity, kitchen_status = PENDING, kds_destination
   6. Recalcula totales vía updateOrderTotals()
```

#### 19.2.5 Modificar Item

```
PUT /restaurant/orders/items/{id}
→ updateItem() [PHP]:
   1. Si quantity_delta: suma/resta (mínimo 0.1)
   2. Si quantity: establece cantidad exacta
   3. Si notes: actualiza notas
   4. Recalcula total = quantity × unit_price
   5. Recalcula totales de la orden
```

#### 19.2.6 Eliminar Item (Anular)

```
DELETE /restaurant/orders/items/{id}
→ removeItem() [PHP]:
   1. Si el item está SENT/READY/DELIVERED: requiere admin_password
   2. Verifica contraseña con Hash::check()
   3. cancelled_from = estado actual (ej: SENT)
   4. cancelled_at = now()
   5. cancelled_by = auth()->id()
   6. kitchen_status = CANCELLED
   7. Si modo impresión: genera ticket de anulación
   8. Si todos los items cancelados: orden → CANCELLED, mesa → AVAILABLE
```

#### 19.2.7 Enviar a Cocina

```
sendToKitchen() [JS] → confirmación → POST
→ sendToKitchen() [PHP]:
   1. Obtiene items PENDING
   2. Cada item: kitchen_status = SENT, sent_to_kitchen_at = now()
   3. Asigna kds_destination desde producto
   4. Orden: status = SENT_TO_KITCHEN
   5. Si modo impresión: genera tickets ESC/POS agrupados por destino
      (cocina-1, cocina-2, bar-1) con: *** COCINA *** + pedido, mesa, hora, items
   6. Cache: marca actualización para polling
```

#### 19.2.8 KDS (Kitchen Display System)

```
Vista: GET /restaurant/kitchen
Polling: setInterval(loadKitchenOrders, 5000)

loadKitchenOrders() [JS]:
   1. GET /restaurant/kitchen-orders?_=timestamp&kds={cocina|cocina2|bar}
   2. Agrupa órdenes: OPEN (Pendientes), SENT_TO_KITCHEN (Enviados), READY (Listos)
   3. Renderiza tarjetas con pedido, mesa, hora, mozo e items
   4. Alerta sonora si hay nuevos pedidos

Marcar LISTO:
POST /restaurant/kitchen/{orderId}/ready → markKitchenReady()
   → Todos los items SENT/PENDING → READY, orden → READY

Marcar ENTREGADO:
POST /restaurant/kitchen/{orderId}/deliver → deliverKitchenOrder()
   → Todos los items SENT/READY → DELIVERED, orden → DELIVERED
```

#### 19.2.9 Cobrar Pedido

```
showChargeModal() [JS]:
   - Cliente por defecto: "Clientes Varios" (DNI 88888888)
   - Totales con IGV dinámico
   - Botón: "COBRAR S/ xxx.xx"

processCharge() [JS] → POST /restaurant/orders/{id}/charge
→ chargeOrder() [PHP]:
   1. Verifica: usuario no mozo
   2. Verifica: caja abierta en la empresa
   3. Verifica: orden no OPEN, tiene items
   4. Obtiene IGV rate: $company->getIgvRate()
   5. Busca/crea Serie para el tipo de documento
   6. Calcula: subtotal = total / (1 + igvRate), igv = total - subtotal
   7. Crea Invoice con tipo_documento, serie, número, fechas, montos
   8. Por cada item del pedido crea InvoiceItem:
      - precio_unitario = round(unit_price / (1 + igvRate), 2)  ← sin IGV
      - precio_venta = unit_price × quantity  ← con IGV × cantidad
      - igv_percent = round(igvRate × 100, 2)  ← con 2 decimales
   9. Descuenta stock (permite negativo)
   10. Incrementa serie
   11. Orden → COMPLETED, mesa → AVAILABLE
   12. Actualiza caja registradora
   13. Responde: { success, invoice_id, full_number, total }

Respuesta [JS]:
   showConfirm("¿Desea imprimir el comprobante?")
   - Sí: window.open /pos/print/{invoice_id}/80mm → recarga
   - No: solo recarga
```

#### 19.2.10 Precuenta

```
showPrebillOptions(event) [JS] → overlay modal con 3 opciones:
   - Precuenta (precuenta)
   - Precuenta 2 (precuenta2)
   - Precuenta 3 (precuenta3)

printPrebillTo(printerKey) [JS]:
   POST /restaurant/orders/{id}/print-prebill/{key}
   → printPrebillTo() [PHP]: genera ticket ESC/POS con cabecera + items + IGV dinámico
```

#### 19.2.11 Anular Pedido Completo

```
cancelOrder() [JS] → confirmación → POST /restaurant/orders/{id}/cancel
→ cancelOrder() [PHP]:
   1. Verifica: usuario no mozo
   2. Si tiene items SENT/READY/DELIVERED: requiere admin_password
   3. Por cada item:
      - cancelled_from = kitchen_status actual
      - cancelled_at = now(), cancelled_by = auth()->id()
      - kitchen_status = CANCELLED
   4. Orden → CANCELLED, mesa → AVAILABLE
   5. Los items aparecen en "Líneas Eliminadas" del reporte de caja
```

#### 19.2.12 Mover Mesa

```
showMoveTableModal() [JS]:
   - Lista todas las mesas de todos los pisos (excluye actual)
   - Muestra estado: Disponible (verde) / Ocupada (amarillo)
   - Clic en mesa destino → confirmación

selectMoveTable(targetTableId) [JS]:
   POST /restaurant/orders/{id}/move-table
   → moveTable() [PHP]:
   1. Valida: mesa destino sin pedido activo
   2. Cambia table_id de la orden
   3. Mesa anterior → AVAILABLE (si no tiene otras órdenes activas)
   4. Mesa nueva → OCCUPIED
```

---

### 19.3 POS (Punto de Venta)

```
Vista: GET /pos → verifica caja abierta (sin filtrar por usuario)
   - Búsqueda por nombre o código de barras (letras→descripción, números→código_barras)
   - Carrito de compras
   - Selector: cliente, tipo documento (Boleta/Factura/NV), método de pago

Procesar venta:
POST /pos → PosController::store()
   1. Verifica caja abierta
   2. Obtiene: customer_id, document_type, payment_method, items del JSON
   3. Busca/crea serie + número correlativo
   4. Calcula IGV dinámico
   5. Crea Invoice + InvoiceItems
   6. Descuenta stock
   7. Actualiza caja registradora
   8. Redirige a página de éxito

Abrir cajón de efectivo:
POST /pos/open-drawer → devuelve { data: base64, printer, ip, port, type }
   JS envía fetch a http://localhost:9100/print (mode: no-cors, form-urlencoded)
```

---

### 19.4 Caja Registradora

#### 19.4.1 Abrir Caja

```
POST /cashregister/open
→ open() [PHP]:
   1. Autoriza: permiso open_cashregister
   2. Obtiene company_id (request > user > empresa principal)
   3. Verifica que no haya otra caja abierta en la empresa
   4. Crea registro con: monto_apertura, referencia, user_id, fecha_apertura, estado=ABIERTA
```

#### 19.4.2 Cerrar Caja

```
POST /cashregister/close
→ close() [PHP]:
   1. Autoriza: permiso close_cashregister
   2. Valida: monto_cierre requerido
   3. Verifica: caja no esté ya cerrada
   4. Verifica: no mesas abiertas en restaurante
   5. Obtiene ventas del periodo filtrando por datetime exacto:
      CONCAT(fecha_emision, ' ', hora_emision) BETWEEN apertura AND cierre
   6. Calcula totales por método de pago (Efectivo, Tarjeta, Yape, Plin, Otro)
   7. Calcula totales por tipo documento (Facturas, Boletas, NV)
   8. Actualiza registro con todos los montos + estado CERRADA
   9. Redirige a resumen
```

#### 19.4.3 Reporte de Líneas Eliminadas

```
Se generan desde restaurant_order_items con:
   - kitchen_status = CANCELLED
   - cancelled_from IN (SENT, READY, DELIVERED)
   - cancelled_at BETWEEN fecha_apertura AND fecha_cierre

Muestra: x{cantidad} - {producto} - {usuario que canceló} {hora}
```

---

### 19.5 Facturación Electrónica SUNAT

```
Crear factura/boleta/NV:
POST /invoices → InvoiceController::store()
   1. Valida tipo_documento, serie, cliente, items
   2. Factura (01): cliente debe tener RUC 11 dígitos
   3. Boleta (03): acepta DNI o RUC
   4. Por cada item: precio_venta = cantidad × precio_con_igv
   5. Crea Invoice + InvoiceItems con IGV dinámico

Enviar a SUNAT:
GET /invoices/{id}/send
→ GreenterService::sendInvoice($invoice)
   1. Carga empresa (certificado .p12 + SOAP credentials)
   2. Construye XML firmado con Greenter
   3. Envía vía SOAP según entorno (Beta/Producción)
   4. Recibe CDR, extrae digest value
   5. Genera PDF con código QR
   6. Actualiza estado del comprobante

PDF: GET /invoices/{id}/pdf → A4
Ticket: GET /invoices/{id}/ticket → 80mm
Nota de Crédito: GET/POST /invoices/{id}/credit-note
```

---

### 19.6 Productos

```
Crear: GET /products/create → genera código PRODxxxxx automático
Store: POST /products/store → guarda con validaciones (código unique, precio min 0)

Duplicar:
POST /products/{id}/duplicate
→ duplicate() [PHP]:
   1. Genera nuevo código (getNextProductCode() busca el número más alto)
   2. Copia todos los campos excepto:
      - código: nuevo secuencial
      - descripción: original + " (Duplicado)"
      - stock: 0
   3. Redirige a edición del duplicado

Importar desde Excel:
POST /products/import → ProductController::importStore()
   1. Lee archivo .xlsx/.xls/.csv
   2. Detecta columnas por nombre (codigo, descripcion, precio, stock, etc.)
   3. Por cada fila: crea producto o salta si ya existe
   4. Crea categorías automáticamente si no existen
   5. Reporta: creados, omitidos, errores
```

---

### 19.7 Compras

```
Vista: GET /purchases
Crear: GET /purchases/create → formulario
   - Búsqueda de productos en tiempo real (código o descripción)
   - Proveedor, tipo documento, fecha

POST /purchases → PurchaseController::store()
   1. Valida tipo_documento, proveedor, fecha
   2. Por cada item:
      - Incrementa stock: $product->stock += $cantidad
      - Crea PurchaseItem
   3. Crea Purchase
```

---

### 19.8 Dashboard

```
GET /dashboard → DashboardController::index()
   - Ventas del mes (SUM de invoices, excluye NV)
   - Crecimiento vs mes anterior (%)
   - Total documentos, Aceptados SUNAT, Pendientes
   - Distribución: Facturas, Boletas, NV
   - Gráfico de ventas diarias últimos 30 días
   - Top 5 productos más vendidos del mes
   - Últimos 10 documentos emitidos
```

---

### 19.9 Impresión (Server-Side)

#### 19.9.1 Arquitectura

```
Controlador PHP → PrintService::printXxx()
   → queuePrint(): crea PrintJob (status: pending)
   → processQueue(): envía HTTP POST a localhost:9100/print

Tarea programada (cada 1 min):
php artisan print:process-queue
   → Busca PrintJobs con status pending/failed (intentos < 3)
   → Envía al Print Server
   → Éxito: status = completed | Falla: status = failed
```

#### 19.9.2 Print Server (Node.js - localhost:9100)

```
server.js — Express en puerto 9100:
   - CORS para todos los orígenes + Private Network Access
   - Disable Quick Edit Mode (evita congelamiento al hacer clic)
   - Auto-reinicio en caso de fallo (loop en start.bat)

Recepción: { printer: "EPSON", data: "BASE64", mode: "escpos" }
Opción local (USB): powershell raw-print.ps1 -printerName "EPSON" -filePath "temp.bin"
Opción red: socket TCP a IP:9100 con datos raw
Encoding: detecta UTF-8, convierte a CP850, inserta ESC t 0x02
```

#### 19.9.3 Formatos de Tickets (PlainTextTicket)

| Método | Contenido |
|--------|-----------|
| `kitchenTicket()` | **COCINA** + pedido, mesa, hora, items |
| `prebillTicket()` | **PRECUENTA** + items, subtotal, IGV dinámico, total |
| `cancelNotificationGrouped()` | **ANULACIÓN COCINA** + items + usuario |
| `invoiceTicket()` | Factura completa para impresora |
| `cashRegisterSummary()` | Resumen completo de caja |

---

### 19.10 Polling (Tiempo Real sin WebSocket)

```
Restaurante:  pollActiveOrders() [JS] → cada 3s
KDS:           loadKitchenOrders() [JS] → cada 5s
Print Server:  pollPrintServer() [JS] → cada 10s

Cache usado para señalización:
   kitchen_updated_{companyId} = timestamp
   restaurant_updated_{companyId} = timestamp
```

---

### 19.11 Eventos

```
KitchenOrderUpdated:
   - Se dispara via event() en todos los métodos que modifican pedidos
   - Ya no implementa ShouldBroadcast (eliminado para evitar error de conexión)
   - El polling del frontend es el único mecanismo de tiempo real
```

---

### 19.12 Seeders (Datos Iniciales)

```
php artisan db:seed → ejecuta en orden:

1. AdminUserSeeder     → Empresa demo + usuarios admin
2. SuperAdminSeeder    → Cajero (Caja@gmail.com / 222938)
3. TestUsersSeeder     → Usuarios demo (admin, mozo, user)
4. SeriesSeeder        → F001, B001, NV01, FC01, BC01, FD01, BD01
5. SunatProductSeeder  → Productos de ejemplo
6. PermissionsSeeder   → 50 permisos + roles (admin, mozo, cajero, user)
7. PrinterSeeder       → 8 slots de impresora (cocina, bar, precuenta, caja, autopedido)
8. UbigeoSeeder        → 1874 registros de ubigeos
9. CustomerSeeder      → "Clientes Varios" (DNI 88888888)

---

## 20. Nuevos Módulos y Mejoras (Junio 2026)

### 20.1 Sistema de Bloqueo de Mesas

```
Propósito: Evitar que dos usuarios abran la misma mesa simultáneamente.

Flujo:
1. Usuario hace clic en mesa → POST /restaurant/tables/{id}/lock
   - Si mesa bloqueada por otro → Error "Mesa en uso por [nombre]"
   - Si bloqueo expirado (>5min) → Se libera automáticamente
   - Si libre → Bloquea para el usuario actual → Abre modal

2. Usuario cierra modal → POST /restaurant/tables/{id}/unlock
   - Libera el bloqueo (owner o admin)

3. Polling cada 3s → GET /restaurant/locks
   - Actualiza visual: mesas bloqueadas por otro → borde naranja
   - Libera bloqueos expirados automáticamente

4. Admin/Cajero → Botón "Desbloquear Mesas"
   - POST /restaurant/tables/unlock-all
   - Libera todos los bloqueos de la empresa

Columnas en restaurant_tables:
- locked_by (FK → users, nullable)
- locked_at (timestamp, nullable)

Métodos en RestaurantTable:
- isLocked(): bool
- isLockedBy($userId): bool
- isLockExpired(): bool  (timeout: 5 min)
- lock($userId): void
- unlock(): void

Migración: 2026_06_10_000001_add_lock_to_restaurant_tables
```

### 20.2 Módulo de Series para Facturación Electrónica

```
Tipos de Serie SUNAT:

| Serie | Tipo Documento | Código SUNAT | Uso |
|-------|---------------|--------------|-----|
| F001-F999 | Factura Electrónica | 01 | Ventas con RUC |
| B001-B999 | Boleta Electrónica | 03 | Ventas con DNI |
| NV01-NV99 | Nota de Venta | NV | Ventas internas (sin SUNAT) |
| FC01-FC99 | Nota de Crédito Factura | 07 | Anular/corregir facturas |
| BC01-BC99 | Nota de Crédito Boleta | 07 | Anular/corregir boletas |
| FD01-FD99 | Nota de Débito Factura | 08 | Incrementar monto facturas |
| BD01-BD99 | Nota de Débito Boleta | 08 | Incrementar monto boletas |
| R001-R999 | Retención | 20 | Comprobante de Retención Electrónica |
| T001-T999 | Guía de Remisión | 09 | Guía de Remisión Remitente |
| P001-P999 | Percepción | 40 | Comprobante de Percepción Electrónica |

Validación de formato: [A-Z]{1,2}\d{2,3} (ej: F001, B001, NV01, FC01)
NV no se envía a SUNAT (validación en InvoiceController::sendToSunat)
Vista: GET /series | Crear: GET /series/create | Editar: GET /series/{id}/edit
```

### 20.3 Módulo de Padrón SUNAT

```
Vista dedicada: GET /sunat-padron
Menú: Empresa → Padrón SUNAT

Muestra información:
- Archivo, tamaño, registros, última actualización
- Estado: disponible / no disponible
- Botón: Descargar / Actualizar

Descarga: POST /sunat-padron/download
- Ejecuta: php artisan sunat:download-padron
- URL: http://www2.sunat.gob.pe/padron_reducido_ruc.zip
- Extrae y limpia automáticamente

Comando manual: php artisan sunat:download-padron

### 20.4 Facturación Electrónica con Greenter

```
Flujo según tipo de documento:

| Documento | Envío | Servicio |
|-----------|-------|----------|
| Factura (01) | Individual (BillSender) | GreenterService::sendInvoice() |
| Boleta (03) | Resumen Diario (SummarySender) | SummaryService::sendBoletaToSummary() |
| NC Factura (FC01, 07) | Individual (BillSender) | GreenterService::sendCreditNote() |
| NC Boleta (BC01, 07) | Resumen Diario (SummarySender) | sendNoteViaSummary() |
| ND Factura (FD01, 08) | Individual (BillSender) | GreenterService::sendDebitNote() |
| ND Boleta (BD01, 08) | Resumen Diario (SummarySender) | sendNoteViaSummary() |
| Baja Factura (01) | Comunicación Baja (SummarySender) | GreenterService::voidInvoice() |
| Baja Boleta (03) | Resumen Diario estado=3 (SummarySender) | SummaryService::voidBoleta() |
| Retención (R001, 20) | Individual | SpecialDocumentService::sendRetention() |
| Guía Remisión (T001, 09) | Individual | SpecialDocumentService::sendDespatch() |
| Percepción (P001, 40) | Individual | SpecialDocumentService::sendPerception() |

Nota de Venta (NV) no se envía a SUNAT (bloqueado en sendToSunat).

GreenterService (app/Services/GreenterService.php):
- Métodos: sendInvoice, sendCreditNote, sendDebitNote, voidInvoice
- sendDebitNote: TipoDoc=08, serie FD01/BD01, boleta→Summary
- sendCreditNote: TipoDoc=07, serie FC01/BC01, boleta→Summary

setupSee() PEM-first:
- Busca {ruc}_certificate.pem → lo usa directamente (sin contraseña, OpenSSL 3.0 compatible)
- Si no existe PEM → usa .p12 con contraseña via X509Certificate
- soap_username + soap_password (fallback: RUC + cert password)
- soap_type_id=1 → SunatEndpoints::FE_BETA
- soap_type_id=2 → SunatEndpoints::FE_HOMOLOGACION

Dependencias (Greenter 5.x):
- greenter/core 5.2.0, greenter/ws 5.2.0, greenter/xml 5.2.0
- greenter/xmldsig 5.0.3, greenter/report 5.2.0
- greenter/htmltopdf 5.2.0, greenter/lite 5.2.0

Extensiones PHP requeridas: ext-soap, ext-openssl, ext-xml, ext-zip, ext-intl
```

### 20.4.1 Arquitectura y Flujo Interno de Greenter

```
Greenter es una librería PHP que implementa la facturación electrónica SUNAT.
Se compone de varios paquetes que trabajan en conjunto:

greenter/lite → See (Clase principal)
  ├── setCertificate(PEM) → Configura el certificado digital
  ├── setClaveSOL(ruc, user, pass) → Credenciales SOL
  ├── setService(URL) → Endpoint SUNAT (Beta/Producción)
  └── send($document) → Envía documento a SUNAT
       └── WsSenderResolver → Selecciona el sender según tipo:
            ├── BillSender → Para Invoice, Note (envío individual)
            └── SummarySender → Para Summary, Voided, Reversion (resumen diario/baja)

Flujo de envío (sendInvoice):
1. Construir objeto Greenter (Invoice, Note, Summary, etc.)
2. See::send() → XML Builder → genera XML firmado
3. Xmldsig → Firma digital del XML con el certificado
4. SoapClient → Envía XML firmado vía SOAP a SUNAT
5. SUNAT responde con CDR (Constancia de Recepción) o Ticket

XML generation (greenter/xml):
  Para cada tipo de documento existe un builder específico:
  - Invoice → Factura/Boleta (UBL 2.1)
  - Note → Nota de Crédito/Débito
  - Summary → Resumen Diario
  - Voided → Comunicación de Baja
  - Despatch → Guía de Remisión
  - Retention → Comprobante de Retención
  - Perception → Comprobante de Percepción

Firma digital (greenter/xmldsig):
  1. X509Certificate: Lee el .p12 (PKCS12) y extrae el certificado + key
  2. Firma el XML con XML Signature (ds:Signature)
  3. Incluye el certificado X509 en el XML firmado

Envío SOAP (greenter/ws):
  1. BillSender: Envío individual (Facturas, NC/ND individuales)
     → Respuesta inmediata con CDR (BaseResult)
     → CDR contiene: estado (aceptado/rechazado), digest value, descripción
  2. SummarySender: Envío por lote (Resumen Diario, Baja)
     → Respuesta con ticket (SummaryResult)
     → Luego se consulta el ticket con getStatus($ticket)
     → ConsultCdrService::getStatus($ticket) → obtiene CDR final

Selección de endpoint por entorno:

```php
// GreenterService::setupSee() - selección de URL
if ($company->soap_type_id == 2) {
    // Producción real
    $this->see->setService(SunatEndpoints::FE_PRODUCCION);
    // https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService
} else {
    // Beta (pruebas SUNAT)
    $this->see->setService(SunatEndpoints::FE_BETA);
    // https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService
}
```

Certificado digital (PEM-first para OpenSSL 3.0):

```php
// Busca primero el PEM (no necesita contraseña, compatible con OpenSSL 3.0)
$pemPath = storage_path("app/certificates/{$company->ruc}_certificate.pem");
if (file_exists($pemPath)) {
    $this->see->setCertificate(file_get_contents($pemPath));
} else {
    // Fallback a PKCS12 (.p12) con contraseña
    $cert = new X509Certificate($pfxContent, $password);
    $this->see->setCertificate($cert->export(X509ContentType::PEM));
}
```

Manejo de documentos en el sistema:

┌─────────────────────┐
│  Generar documento  │  ← Sistema crea Invoice/Boleta/NC/ND en BD
│  sunat_estado:      │     Estado inicial: PENDIENTE
│  PENDIENTE          │
└─────────┬───────────┘
          │
          ▼ Enviar a SUNAT
┌─────────────────────┐
│  Factura (01)       │──→ GreenterService::sendInvoice()
│  NC/ND Factura      │──→ GreenterService::sendCreditNote/DebitNote()
│  Retención/Guía/Per │──→ SpecialDocumentService::sendXxx()
│                     │     → BillSender → CDR inmediato
│  ─── Resultado ───  │
│  success=true:      │  sunat_estado=ACEPTADO, guarda XML y CDR
│  success=false:     │  sunat_estado=RECHAZADO, guarda error
└─────────────────────┘

┌─────────────────────┐
│  Boleta (03)        │──→ SummaryService::sendBoletaToSummary()
│  NC/ND Boleta       │──→ sendNoteViaSummary()
│  Baja Boleta        │──→ SummaryService::voidBoleta()
│                     │     → SummarySender → ticket
│  ─── Resultado ───  │
│  success=true:      │  sunat_estado=ENVIADO, guarda ticket
│  success=false:     │  sunat_estado=RECHAZADO
└─────────────────────┘
          │
          ▼ Consultar ticket
┌─────────────────────┐
│  checkTicketStatus  │──→ ConsultCdrService::getStatus($ticket)
│                     │     → Si ACEPTADO: actualiza a ACEPTADO
│                     │     → Si RECHAZADO: actualiza a RECHAZADO
│                     │     → Si PENDIENTE: esperar y reintentar
└─────────────────────┘

Ejemplo de código (enviar factura):

  // 1. Obtener empresa y configurar See
  $company = Company::getMainCompany();
  $greenterService = new GreenterService();  // setupSee() automático
  
  // 2. Construir documento Greenter
  $invoice = new Invoice();
  $invoice->setUblVersion('2.1');
  $invoice->setTipoDoc('01');
  $invoice->setSerie('F001');
  $invoice->setCorrelativo('00000001');
  $invoice->setCompany($greenterCompany);
  $invoice->setClient($client);
  $invoice->setDetails($saleDetails);
  
  // 3. Enviar
  $result = $this->see->send($invoice);
  
  // 4. Procesar respuesta
  if ($result->isSuccess()) {
      $cdrZip = $result->getCdrZip();   // CDR en ZIP
      $xmlContent = $this->see->getFactory()->getLastXml();  // XML firmado
      $digestValue = $this->extractDigestValueFromXml($xmlContent);  // Hash QR
  } else {
      $error = $result->getError();
      // $error->getCode() y $error->getMessage()
  }

Ejemplo de código (Resumen Diario):

  // 1. Crear Summary con SummaryDetail(s)
  $summary = new Summary();
  $summary->setFecGeneracion(new \DateTime('2026-06-12'));
  $summary->setFecResumen(new \DateTime());
  $summary->setCorrelativo('001');
  
  $detail = new SummaryDetail();
  $detail->setTipoDoc('03');  // Boleta
  $detail->setSerieNro('B001-00000001');
  $detail->setEstado('1');    // 1=Agregar, 3=Anular
  $detail->setTotal(100.00);
  $detail->setMtoOperGravadas(84.75);
  $detail->setMtoIGV(15.25);
  
  $summary->setDetails([$detail]);
  
  // 2. Enviar
  $result = $this->see->send($summary);
  
  // 3. Respuesta con ticket
  if ($result->isSuccess()) {
      $ticket = $result->getTicket();  // Ej: "1781302448095"
      // Consultar después con getStatus($ticket)
  }
```

### 20.5 KDS - Botón "Completado"

```
Nuevo botón en pantalla de cocina (KDS):
- Botón naranja "COMPLETADO" en cada tarjeta de pedido
- Acción: Marca todos los items como DELIVERED
- Cambia orden a COMPLETED y mesa a AVAILABLE
- Útil para cerrar pedidos sin cobrar

Endpoint: POST /restaurant/kitchen/{orderId}/complete
Nombre: restaurant.kitchenComplete
```

### 20.6 Ubigeo con Selects en Cascada

```
Empresa /companies/{id}/edit:
- Departamento (select) → carga provincias vía AJAX
- Provincia (select) → carga distritos vía AJAX
- Distrito (select) → auto-asigna código ubigeo
- Ubigeo (input readonly) → código de 6 dígitos

Funciones JavaScript:
- loadDepartamentos() → GET /ubigeo/departamentos
- loadProvincias() → GET /ubigeo/provincias?departamento=X
- loadDistritos() → GET /ubigeo/distritos?departamento=X&provincia=Y
- setUbigeo() → Actualiza código al seleccionar distrito

Datos: 1874 registros en tabla ubigeos, 25 departamentos.
```

### 20.7 Polling - Headers JSON

```
Todos los fetch calls del restaurante y KDS ahora incluyen:
- 'Accept': 'application/json'
- 'X-Requested-With': 'XMLHttpRequest'

Esto evita que Laravel redirija a login (HTML) cuando la sesión expira,
permitiendo que los errores se manejen como JSON correctamente.

Archivos modificados:
- resources/views/restaurant/index.blade.php (7 calls)
- resources/views/restaurant/kds.blade.php (3 calls)
```

### 20.8 Resumen Diario (SummaryService)

```
Propósito: Las boletas (03), NC/ND de boletas y anulaciones de boletas deben
comunicarse a SUNAT mediante Resumen Diario. Las facturas (01) se envían individualmente.

SummaryService (app/Services/SummaryService.php):
- sendBoletaToSummary($invoice) → Envía una boleta individual por Resumen Diario
- sendDailySummary() → Agrupa TODAS las boletas PENDIENTES del día en un solo resumen
- voidBoleta($invoice) → Anula boleta con estado=3 via Summary
- sendNoteToSummary($note, $original, $tipoDoc) → Envía NC/ND de boleta por Summary
- checkTicketStatus($ticket) → Consulta estado del ticket via ConsultCdrService

Cada método crea un objeto Summary con SummaryDetail(s) y lo envía con See::send().
El WsSenderResolver detecta Summary::class y automáticamente usa SummarySender,
que comprime el XML en ZIP y lo envía por SOAP a SUNAT.

Ejemplo de código (sendBoletaToSummary):
```php
// 1. Crear el detalle del resumen (1 por boleta)
$detail = new SummaryDetail();
$detail->setTipoDoc('03');  // Boleta
$detail->setSerieNro($invoice->serie . '-' . str_pad($invoice->numero, 8, '0', STR_PAD_LEFT));
$detail->setClienteTipo($client->documento_tipo == '6' ? '6' : '1');
$detail->setClienteNro($client->documento_numero);
$detail->setEstado('1');  // 1=Agregar, 3=Anular
$detail->setTotal($invoice->total);
$detail->setMtoOperGravadas($invoice->gravado ?? $invoice->subtotal);
$detail->setMtoIGV($invoice->igv);
$detail->setPorcentajeIgv($company->getActiveIgvPercent());

// 2. Crear el resumen
$correlativo = '001';  // Número secuencial del día
$summary = new Summary();
$summary->setFecGeneracion(new \DateTime($invoice->fecha_emision));
$summary->setFecResumen(new \DateTime());  // Fecha del envío
$summary->setCorrelativo($correlativo);
$summary->setMoneda('PEN');
$summary->setCompany($greenterCompany);
$summary->setDetails([$detail]);

// 3. Enviar a SUNAT
$result = $this->see->send($summary);

// 4. Respuesta: ticket en lugar de CDR
if ($result->isSuccess()) {
    $ticket = $result->getTicket();  // Ej: "1781302448095"
    // Guardar ticket y consultar después con checkTicketStatus()
}

// 5. Consultar estado del ticket
$statusResult = $this->see->getStatus($ticket);
if ($statusResult->isSuccess()) {
    $cdrZip = $statusResult->getCdrZip();
    // Actualizar invoice a ACEPTADO
}
```

Ejemplo de código (sendDailySummary):
```php
// Agrupa todas las boletas PENDIENTES del día en un solo Summary
$invoices = Invoice::where('tipo_documento', '03')
    ->where('sunat_estado', 'PENDIENTE')
    ->whereDate('fecha_emision', today())
    ->get();

// Crea un SummaryDetail por cada boleta
$details = [];
foreach ($invoices as $invoice) {
    $detail = new SummaryDetail();
    // ... configurar detalle ...
    $details[] = $detail;
}

$summary->setDetails($details);  // Múltiples boletas en un solo resumen
$result = $this->see->send($summary);
```

Filas afectadas:
- summary_documents: guarda el resumen enviado con su ticket
- invoices: actualiza sunat_estado a 'ENVIADO', guarda ticket
- Al consultar ticket ACEPTADO: invoices → sunat_estado='ACEPTADO'

Vista: GET /sunat-summaries (dos tablas: resúmenes diarios + envíos individuales)
Menú: Comprobantes → Resúmenes Diarios
- cantidad_documentos, ticket, sunat_estado
- sunat_response, sunat_fecha

Tabla: summary_documents (migración 2024_01_01_000008)
- correlativo ampliado a 30 chars (migración 2026_06_12_000003)

Vista: GET /sunat-summaries
Menú: Comprobantes → Resúmenes Diarios

Rutas:
- GET /sunat-summaries → index
- POST /sunat-summaries/{summary}/check → checkStatus
- POST /sunat-summaries/check-all → checkAllPending
- POST /sunat-summaries/send-daily → sendDaily
- POST /sunat-summaries/retry-pending → retryPending

Flujo:
1. Crear boleta → queda PENDIENTE
2. Al final del día: Enviar Resumen Diario (web o comando)
3. SUNAT responde con un ticket
4. Consultar ticket cada 10 min hasta que esté ACEPTADO
5. La boleta individual actualiza su estado automáticamente
```

### 20.9 Nota de Débito

```
sendDebitNote() en GreenterService:
- TipoDoc: 08 (Nota de Débito)
- Serie: FD01 (para factura original) / BD01 (para boleta original)
- Motivos: Intereses por mora, Aumento en el valor, Penalidades, Otros
- Si la ND es de boleta → se envía por Resumen Diario
- Si la ND es de factura → se envía individualmente (BillSender)

Rutas:
- GET /invoices/{id}/debit-note → debitNoteForm (formulario)
- POST /invoices/{id}/debit-note → sendDebitNote (procesar)

Vista: resources/views/invoices/debit-note.blade.php
Botón en show: "Nota de Débito" (rojo) junto a "Nota de Crédito"
```

### 20.10 Documentos Especiales SUNAT

```
Documentos implementados:

| Documento | Serie | Código SUNAT | Ruta |
|-----------|-------|--------------|------|
| Retención | R001 | 20 | /documents/R |
| Guía de Remisión | T001 | 09 | /documents/T |
| Percepción | P001 | 40 | /documents/P |

SpecialDocumentService (app/Services/SpecialDocumentService.php):
- sendRetention($doc) → Envía comprobante de retención
- sendDespatch($doc) → Envía guía de remisión (con items, direcciones)
- sendPerception($doc) → Envía comprobante de percepción

DocumentController (app/Http/Controllers/DocumentController.php):
- index($tipo) → Lista documentos por tipo
- create($tipo) → Formulario de creación
- store($tipo, $request) → Guarda documento
- show($tipo, $document) → Detalle con botón "Enviar a SUNAT"
- send($tipo, $document) → Envía a SUNAT
- createFromInvoice($invoice) → Genera guía T001 desde factura/boleta

Tablas (migraciones 2026_06_12_000001 y 000002):
- special_documents: datos generales + regimen, tasa, imp_retenido, direcciones
- special_document_entities: proveedor/destinatario
- special_document_items: items (para guías de remisión)

Guía desde factura:
- Botón "Guía de Remisión" en /invoices/{id}
- Pre-carga cliente, items y direcciones automáticamente

Menú: Comprobantes → Retenciones | Guías de Remisión | Percepciones
```

### 20.11 Certificado Digital (PEM-first con OpenSSL 1.1.1)

```
Problema: PHP 8.4 usa OpenSSL 3.0 que no puede leer certificados PKCS12
antiguos (MAC verify failure) sin el provider legacy.

Solución:
1. Al SUBIR el certificado:
   - OpenSSL 1.1.1 (Git Bash: C:\laragon\bin\git\mingw64\bin\openssl.exe)
     verifica la contraseña
   - Guarda el .p12 original en storage/app/certificates/
   - Extrae un .pem compatible con OpenSSL 3.0

2. Al USAR el certificado (todos los servicios):
   - Busca primero {ruc}_certificate.pem → lo usa directamente (sin contraseña)
   - Si no existe PEM → usa .p12 con contraseña via X509Certificate

Archivos actualizados:
- CompanyController.php → Usa OpenSSL CLI para verificar y extraer PEM
- GreenterService.php → setupSee() PEM-first
- SummaryService.php → setupSee() PEM-first
- SpecialDocumentService.php → setupSee() PEM-first
```

### 20.12 Kiosko de Autopedidos (Pantalla Táctil)

```
Propósito: Pantalla táctil en la entrada del local para que los clientes realicen sus
pedidos directamente, sin intervención del mozo.

Arquitectura:
┌──────────────────────────────────────┐
│  Pantalla Táctil (Entrada)           │
│  http://facturafacil.test/autopedido │
│  - Sin autenticación                 │
│  - Interfaz touch-friendly           │
│  - Modal de producto con:            │
│    · Cantidad (+/−)                  │
│    · Nota para cocina                │
│    · Elementos Auxiliares (chips)    │
└──────────┬───────────────────────────┘
           │ HTTP
┌──────────▼───────────────────────────┐
│  AutoPedidoController                 │
│  - Valida caja abierta               │
│  - Busca mesa virtual "Kiosko"       │
│    (is_for_kiosko=true)              │
│  - Crea RestaurantOrder              │
│    table_id=id_mesa_kiosko           │
│    status=PENDING_PAYMENT            │
│    order_type=kiosko                 │
│    order_number=A-001 (por caja)     │
│  - Imprime ticket con N° y total     │
└──────────┬───────────────────────────┘
           │
┌──────────▼───────────────────────────┐
│  Cajero (vista pendientes)           │
│  - Ve pedidos kiosko pendientes      │
│    y en cocina (2 estados)           │
│  - Botón "Enviar a Cocina"           │
│    (cambia status a SENT_TO_KITCHEN) │
│  - Botón "Cobrar" (solo si ya        │
│    fue enviado a cocina)             │
│  - Botón "🗑️ Eliminar"               │
│    (pide admin si ya fue enviado)    │
│  - Modal de cobro (existente)        │
│    Yape / Plin / Efectivo / Tarjeta  │
│  - Genera Invoice con                │
│    order_source='kiosko'             │
└──────────┬───────────────────────────┘
           │
┌──────────▼───────────────────────────┐
│  Cocina (KDS)                        │
│  - Sección separada "KIOSKO"         │
│  - Badge morado con etiqueta KIOSKO  │
│  - Prepara y deja en mostrador       │
│  - Muestra elementos auxiliares      │
│    en cada item (+ Mayonesa)         │
└──────────────────────────────────────┘

Flujo completo:
1. Cliente llega al kiosko en la entrada
2. Navega por categorías o busca productos con el teclado virtual
3. Toca un producto → se abre modal con:
   - Cantidad ajustable (+/−)
   - Nota para cocina (opcional, con teclado virtual)
   - Elementos Auxiliares (chips seleccionables)
4. Toca "Agregar al Carrito" → producto se agrega con sus opciones
5. Repite hasta completar el pedido
6. Confirma pedido → se imprime ticket con N° de pedido y total a pagar
7. Cliente se acerca a caja con su ticket
8. Cajero abre la vista de pedidos kiosko pendientes
9. Cajero hace clic en "Enviar a Cocina" → cocina comienza a preparar
10. Cajero hace clic en "Cobrar" → se abre modal de cobro
11. Cajero cobra (Yape/Plin/Efectivo/Tarjeta, pagos mixtos)
12. KDS muestra pedido en sección "KIOSKO" separada de mesas
13. Cocina prepara y deja en mostrador de recogida

Rutas:
- GET  /autopedido                → Interfaz táctil del kiosko (pública)
- POST /autopedido/confirm        → Confirma el pedido (pública)
- GET  /autopedido/success/{id}   → Pantalla de éxito con N° y total
- GET  /restaurant/kiosk-orders   → Lista de pedidos pendientes y en cocina (auth)
- POST /restaurant/kiosk-send/{id}   → Enviar pedido a cocina (auth)
- POST /restaurant/kiosk-charge/{id} → Cobrar pedido kiosko (auth)

Numeración A-001:
- Se genera con RestaurantOrder::generateKioskoOrderNumber($companyId)
- La numeración está ligada a la caja abierta actual
- Busca el último pedido kiosko creado después de la fecha_apertura de la caja
- Al cerrar caja y abrir una nueva, la secuencia se reinicia a A-001
- Si no hay caja abierta, lanza excepción (el controlador valida antes)

Validación de caja abierta:
- confirmOrder() verifica que exista una caja con estado 'ABIERTA'
- Si no hay caja abierta, responde con error:
  "No hay caja abierta. No se puede realizar el pedido."
- Todas las ventas del sistema dependen de caja abierta

Mesa virtual Kiosko:
- Se almacena en restaurant_tables con is_for_kiosko=true
- Se crea automáticamente al ejecutar la migración add_is_for_kiosko
- No aparece en el floor plan del restaurante (scope excludeKiosko)
- No aparece en la gestión de pisos/mesas
- completeOrder() no cambia su status (salta si order_type=kiosko)
- El pedido kiosko usa table_id = id de esta mesa virtual
- Esto evita tener table_id=NULL en restaurant_orders
- Se crea una por cada empresa (si tiene al menos un piso)

Teclado virtual:
- Se usa tanto para el campo de búsqueda como para la nota en el modal
- Variable global activeInput apunta al input/textarea activo
- openKeyboard(input) recibe el elemento, lo asigna a activeInput y muestra el teclado
- pressKey(k) escribe en activeInput.value usando selectionStart/selectionEnd
- pressBackspace() borra el caracter anterior a selectionStart
- closeKeyboard() limpia activeInput y oculta el teclado
- Si activeInput.id === 'searchInput' se ejecuta applyFilters()

Modal de producto:
- Se abre al tocar un producto en la cuadrícula
- Muestra nombre, precio, control de cantidad con +/−
- Textarea para nota de cocina (con teclado virtual)
- Chips de elementos auxiliares cargados vía GET /auxiliary-items/list
- Botón "Agregar al Carrito" → guarda en cart[] con notes y auxiliary_items
- Si mismo producto + mismas notas + mismos auxiliares → incrementa cantidad
- Botón "Cancelar" → cierra modal sin agregar

Ticket de autopedido:
- No usar emojis (🧾, ✅) en tickets ESC/POS. Las impresoras usan CP850
  que distorsiona los emojis UTF-8. Usar texto plano como "A-001".

Código del controlador (AutoPedidoController):
```php
// confirmOrder() - valida caja abierta, crea pedido, imprime ticket
$cashRegister = CashRegister::where('company_id', $companyId)
    ->where('estado', 'ABIERTA')->first();
if (!$cashRegister) {
    return response()->json(['success' => false, 'message' => 'No hay caja abierta']);
}

$kioskoTable = RestaurantTable::where('company_id', $companyId)
    ->where('is_for_kiosko', true)->first();

$order = RestaurantOrder::create([
    'company_id' => $companyId,
    'table_id' => $kioskoTable->id,
    'user_id' => null,
    'order_number' => RestaurantOrder::generateKioskoOrderNumber($companyId),
    'status' => 'PENDING_PAYMENT',
    'order_type' => 'kiosko',
]);

foreach ($items as $item) {
    $product = Product::find($item['product_id']);
    RestaurantOrderItem::create([
        'restaurant_order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->descripcion,
        'quantity' => $item['quantity'],
        'unit_price' => $product->precio,
        'total' => $product->precio * $item['quantity'],
        'kitchen_status' => 'PENDING',
        'notes' => $item['notes'] ?? null,
        'auxiliary_items' => $item['auxiliary_items'] ?? null,
        'kds_destination' => $product->kds_destination ?? 'cocina',
    ]);
}
$printService->printAutoPedidoTicket($order);
```

Código del envío a cocina (kioskSendToKitchen):
```php
// 1. Marcar items como enviados a cocina
foreach ($order->items as $item) {
    $item->kitchen_status = 'SENT';
    $item->sent_to_kitchen_at = now();
    $item->save();
}
$order->status = 'SENT_TO_KITCHEN';
$order->save();
event(new KitchenOrderUpdated(...));

// 2. Imprimir ticket de cocina si modo 80mm
if ($company->order_mode === 'print') {
    $printService->printKitchenOrder($order->fresh(), $order->items);
}
```

Código del cobro (chargeKioskOrder):
```php
// 1. Validar que el pedido ya fue enviado a cocina
if ($order->status !== 'SENT_TO_KITCHEN') {
    return response()->json(['success' => false, 'message' => 'Debe enviar el pedido a cocina antes de cobrar']);
}

// 2. Cobrar (reutiliza chargeOrder() existente)
$request->merge(['document_type' => $request->document_type ?? 'NV']);
$chargeResult = $this->chargeOrder($request, $orderId);

// 3. Marcar invoice como kiosko
Invoice::where('id', $data['invoice_id'])->update(['order_source' => 'kiosko']);
```

Ticket de autopedido (PlainTextTicket::autoPedidoTicket):
```
         *** AUTO PEDIDO ***
           FacturaFácil

             A-001

    2x Hamburguesa     S/ 24.00
    1x Papas Fritas    S/  8.00
    ---------------------------------
    TOTAL:             S/ 32.00

      Pase a Caja para pagar
      ¡Gracias por su pedido!
```

Características de la interfaz táctil:
- Teclado virtual en pantalla (sin necesidad de teclado físico)
- Modal de producto con cantidad, nota y elementos auxiliares
- Categorías como tabs (Todos, Entradas, Principales, Bebidas...)
- Productos en grid con precio
- Carrito con modificación de cantidades (+/−), nota y auxiliares
- Barra inferior fija con total y botón confirmar
- Sin necesidad de autenticación (público)

Reporte de cierre de caja:
- Los pedidos kiosko se muestran por separado en el resumen de caja
- Se identifican con order_source='kiosko' en la tabla invoices
- Aparece una tarjeta "Pedidos Kiosko" con conteo y total en S/

Vista del cajero:
- Menú: Restaurante → Pedidos Kiosko
- Tabla con: N° Pedido, Items, Total, Estado, Fecha, Acción
- Estados: "Pendiente" (PENDING_PAYMENT) y "En Cocina" (SENT_TO_KITCHEN)
- Botones contextuales según estado:
  - Pendiente → "Enviar a Cocina" + 🗑️
  - En Cocina → "Cobrar" + 🗑️
- Modal de cobro incluido en la misma vista (no depende del index del restaurante)
- Búsqueda de clientes con dropdown

KDS (Cocina):
- Los pedidos kiosko aparecen en una sección separada "KIOSKO — Autoservicio"
- Los pedidos de mozo aparecen en sección "MOZO — Pedidos de Mesas"
- Cada tarjeta kiosko tiene badge morado "KIOSKO" en el número de orden
- El borde izquierdo de las tarjetas kiosko es morado (#9c27b0) vs rojo (mozo)
- La sección KIOSKO tiene un contador (ej. Autoservicio (3))
- Muestra "Autoservicio" en lugar de "Mesa: Kiosko"
- Icono de carrito (fa-shopping-cart) en vez de silla (fa-chair)
- Muestra elementos auxiliares en cada item (+ Mayonesa, Kétchup)
- El cocinero sabe que los pedidos kiosko van al mostrador de recogida

Eliminación de pedidos:
- Botón 🗑️ disponible en ambos estados
- PENDING_PAYMENT → cancela directo (sin contraseña)
- SENT_TO_KITCHEN → requiere contraseña de administrador
- Usa el mismo endpoint cancelOrder() del RestaurantController
- Si se cancela con items en cocina, imprime notificación de cancelación
  en modo print

Archivos involucrados:
- app/Http/Controllers/AutoPedidoController.php          → Controlador del kiosko
- app/Http/Controllers/Restaurant/RestaurantController.php → kioskOrders, kioskSendToKitchen, chargeKioskOrder
- app/Models/RestaurantOrder.php                          → generateKioskoOrderNumber()
- app/Models/RestaurantTable.php                          → scope excludeKiosko, is_for_kiosko
- app/Services/PlainTextTicket.php                        → Ticket de autopedido
- app/Services/PrintService.php                           → Impresión ticket
- resources/views/autopedido/index.blade.php              → Interfaz táctil con modal
- resources/views/autopedido/success.blade.php            → Confirmación
- resources/views/restaurant/kiosk-orders.blade.php       → Vista del cajero con modal de cobro
- resources/views/restaurant/kds.blade.php                 → KDS con secciones separadas
- database/migrations/2026_06_19_000001_add_order_source_and_type.php
- database/migrations/2026_07_02_202441_add_is_for_kiosko_to_restaurant_tables.php
- database/migrations/2026_07_02_205824_add_pending_payment_to_restaurant_orders_status.php

Impresora (slot):
- assigned_to: "autopedido"
- Se crea automáticamente con PrinterSeeder o manualmente
- Imprime ticket de 80mm con N° de pedido y total
- Método: PrintService::printAutoPedidoTicket($order)
- Importante: este método debe llamar a $this->processQueue() después de
  $this->queuePrint() (bug corregido: faltaba la llamada)
- Formato: PlainTextTicket::autoPedidoTicket($order)
- No usar emojis en el ticket (CP850 no los soporta)

Menú del sistema:
- Restaurante → Pedidos Kiosko (lista para cajero)
- Restaurante → 🖥️ Kiosko (Pantalla) (abre /autopedido en nueva pestaña)
```

### 20.14 Elementos Auxiliares (Chips en Productos)

```
Propósito: Permitir agregar acompañamientos a los productos del pedido
(ej. Mayonesa, Kétchup, Mostaza, Ají) tanto en el POS del restaurante
como en el kiosko de autopedido.

Base de datos:
- Tabla: auxiliary_items
- Columnas: id, company_id, name, status (ACTIVO/INACTIVO), timestamps
- Los items activos se cargan vía scopeActive()

Modelo: App\Models\AuxiliaryItem
- $fillable: company_id, name, status
- belongsTo(Company::class)
- scopeActive(): where('status', 'ACTIVO')

Almacenamiento en pedidos:
- Columna auxiliary_items (JSON, nullable) en restaurant_order_items
- Cast en modelo: $casts = ['auxiliary_items' => 'array']
- Se almacena como array de IDs: [1, 3, 5]
- fillable incluye 'auxiliary_items'

CRUD:
- Controlador: AuxiliaryItemController
- Rutas: Route::resource('auxiliary-items', ...) dentro del grupo admin
- Vista index con tabla de items y botones crear/editar/eliminar
- Vista create/edit con campos: nombre, estado
- Ruta pública GET /auxiliary-items/list?company_id=X → JSON con items activos

Integración en POS (restaurant/index.blade.php):
- En el modal de cantidad (qtyOverlay), sección "Elementos Auxiliares:"
- Chips cliqueables con toggle .selected
- CSS: .aux-chip (borde gris) / .aux-chip.selected (fondo rojo e94560)
- loadAuxiliaryItems(): fetch a /auxiliary-items/list, crea chips
- confirmAddItem(): recoge IDs seleccionados, envía como auxiliary_items[]
- En el renderizado del pedido: muestra nombres via window._auxNames cache

Integración en Kiosko (autopedido/index.blade.php):
- En el modal de producto, sección con chips
- loadModalAuxItems(): misma mecánica que POS
- confirmAddToCart(): almacena en cart[] como c.auxiliary_items

Visualización en KDS (kds.blade.php):
- getKitchenOrders() resuelve nombres via AuxiliaryItem::whereIn()
- Retorna auxiliary_items (IDs) y auxiliary_names (nombres) por item
- Renderizado: + Mayonesa, Kétchup en color morado (#ce93d8)

Visualización en comanda impresa (PlainTextTicket::kitchenTicket):
- Si item->auxiliary_items tiene IDs, resuelve nombres y los imprime
- Formato: "    + Mayonesa, Kétchup"

Archivos involucrados:
- app/Models/AuxiliaryItem.php
- app/Http/Controllers/AuxiliaryItemController.php
- database/migrations/...create_auxiliary_items_table.php
- database/migrations/...add_auxiliary_items_to_restaurant_order_items.php
- resources/views/auxiliary-items/{index,create,edit}.blade.php
- resources/views/restaurant/index.blade.php (modal + render)
- resources/views/restaurant/kds.blade.php (render en KDS)
- resources/views/autopedido/index.blade.php (modal kiosko)
- app/Http/Controllers/Restaurant/RestaurantController.php (getKitchenOrders)
- app/Services/PlainTextTicket.php (kitchenTicket)
```

### 20.15 Mejoras en Impresión Térmica

```
Propósito: Correcciones y mejoras en el sistema de impresión térmica ESC/POS.

1. processQueue() faltante en printAutoPedidoTicket:
   - Todos los métodos de PrintService que llaman a queuePrint() deben llamar
     también a processQueue() para enviar el trabajo al print server.
   - printKitchenOrder(), printPrebill(), printInvoice() ya lo hacían.
   - printAutoPedidoTicket() NO lo hacía → el ticket quedaba en cola "pending"
     sin enviarse nunca al print server.
   - Corregido agregando $this->processQueue() al final del método.

2. Filtro $dests roto en kitchenTicket:
   - PlainTextTicket::kitchenTicket() tenía un filtro que saltaba TODOS los items:
     $dests = ['cocina'=>'', 'cocina2'=>'', 'bar'=>''];
     $dest = $item->kds_destination ?? 'cocina';
     if (isset($dests[$dest]) && $dest !== $dests[$dest]) continue;
   - $dests[$dest] siempre es '' (string vacío), por lo que $dest !== ''
     siempre es true para cualquier destino válido → todos los items se saltaban.
   - Como printKitchenOrder() ya agrupa items por destino antes de llamar
     a kitchenTicket(), el filtro era redundante. Se eliminó.

3. Emojis no compatibles con CP850:
   - Las impresoras térmicas usan encoding CP850.
   - Los emojis UTF-8 (🧾, ✅, ❌, etc.) se imprimen como caracteres
     basura (ðŸ§¾, etc.).
   - Usar siempre texto plano en tickets ESC/POS.

4. Proceso de impresión:
   queuePrint() → crea PrintJob (status=pending)
   processQueue() → envía al print server vía HTTP
   PrintServer Node.js → recibe y envía a la impresora
   Reintentos: hasta 3 intentos para jobs failed
```

### 20.16 Actualización de Greenter v5.3.0

```
Propósito: Actualización de los paquetes Greenter de v5.2.0 a v5.3.0.

Paquetes actualizados:
- greenter/core: v5.2.0 → v5.3.0
- greenter/lite: v5.2.0 → v5.3.0 (sin cambios, mismo commit)
- greenter/ws: v5.2.0 → v5.3.0 (sin cambios, mismo commit)
- greenter/xml: v5.2.0 → v5.3.0

Cambios reales (solo en core + xml):
- Agregado campo "fecha de entrega de bienes" (fechaEntrega) al modelo
  Shipment de guía de remisión.
- Template XML despatch2022.xml.twig actualizado con 5 líneas nuevas.
- No hay cambios en APIs existentes. Actualización segura.
- Comando: composer update greenter/core greenter/lite greenter/ws greenter/xml
```

### 20.13 Mejoras en Polling y Fetch

```
Propósito: Optimizar el polling del lado cliente y asegurar manejo correcto de errores.

Pollings activos en restaurant/index.blade.php:
- pollActiveOrders()     → cada 10s (antes 3s) → GET /restaurant/active-orders
- pollTableLocks()       → cada 10s (antes 3s) → GET /restaurant/locks
- pollPrintServer()      → cada 10s            → GET /restaurant/print-status
- loadKitchenOrders()    → cada 5s (KDS)       → GET /restaurant/kitchen-orders

Cambios realizados:
1. Intervalos de 3s subidos a 10s (reduce carga del servidor)
2. Headers Accept: application/json agregados a:
   - POST /restaurant/kiosk-charge/{id}
   - POST /autopedido/confirm
3. .catch() con showError() agregados a funciones sin manejo de errores:
   - saveItemNotes(), changeItemQty(), removeItem()
   - sendToKitchen(), closeTable(), cancelOrderRequest()
   - confirmAdminPassword()
4. .catch() con alert() agregados en KDS:
   - markOrderReady(), deliverOrder(), completeOrder()
5. completeOrder() ya no cambia status de mesa Kiosko a AVAILABLE

Comportamiento de .catch() según contexto:
- Polling automático → catch silencioso (no molestar al usuario)
- Acciones del usuario → catch con showError() o alert() (feedback visual)
```

### 20.17 Modal "Solo Consumo" en Cobro de Restaurante

```
Propósito: Permitir al cajero agrupar todos los productos del pedido en una sola
línea "POR CONSUMO" en el comprobante, usando el código SUNAT 90101801.

Motivación: Algunos clientes no quieren que su boleta/factura muestre el detalle
de cada producto (ej. "Ceviche", "Limonada", "Menú"), sino solo el total como
"Por Consumo". Esto es común en restaurantes donde el consumo es general.

Arquitectura:
┌─────────────────────────────────────────┐
│ Modal de Cobro (Restaurante)            │
│ ┌─────────────────────────────────────┐ │
│ │ Checkbox: ☐ Solo Consumo            │ │
│ │          (todo como "POR CONSUMO")  │ │
│ └─────────────────────────────────────┘ │
└────────────────┬────────────────────────┘
                 │ POST /restaurant/orders/{id}/charge? solo_consumo=true
┌────────────────▼────────────────────────┐
│ RestaurantController@chargeOrder         │
│                                          │
│ if (solo_consumo) {                      │
│   InvoiceItem::create([                  │
│     'codigo' => '90101801',             │
│     'descripcion' => 'POR CONSUMO',     │
│     'cantidad' => 1,                    │
│     'precio_venta' => $total,           │
│   ]);                                    │
│ } else {                                 │
│   // Lógica actual: 1 item por producto  │
│ }                                        │
│                                          │
│ // Stock: siempre descuenta productos    │
│ foreach ($items as $item) {              │
│   $product->decrement('stock', ...);     │
│ }                                        │
└──────────────────────────────────────────┘

Código SUNAT 90101801:
- Corresponde a "Servicios de restaurante" en el catálogo de productos SUNAT
- Se usa en el campo codigo del InvoiceItem para que el XML sea válido
- Si se usara un código vacío o incorrecto, SUNAT rechazaría el comprobante
- Solo aplica cuando el checkbox "Solo Consumo" está activo

Stock:
- El stock se descuenta SIEMPRE de los productos reales del pedido
- El invoice item "POR CONSUMO" es solo para el comprobante fiscal
- No afecta el inventario ni el cuadre de caja

Cierre de caja:
- No afectado. La caja registradora lee Invoice.total y métodos de pago,
  no los items individuales. El total es el mismo.

Cambios en el código:
- Frontend (restaurant/index.blade.php): checkbox #chargeSoloConsumo en modal
- JS processCharge(): envía solo_consumo: true/false en el body del fetch
- RestaurantController@chargeOrder: cuando solo_consumo es true, crea un
  solo InvoiceItem con código 90101801 y descripción "POR CONSUMO"
```

### 20.18 Corrección de Validación de Stock en Productos

```
Propósito: Corregir la validación del campo stock en el formulario de productos
para que acepte decimales y no bloquee la edición cuando el stock es negativo.

Problema original:
- La columna stock en DB es decimal(12,4) (acepta decimales)
- La validación PHP decía: 'stock' => 'nullable|integer|min:0'
- integer no acepta decimales (5.5 falla)
- min:0 no acepta negativos (-5.5 falla)
- Al editar un producto con stock negativo, la validación fallaba aunque
  el usuario estuviera editando otro campo como codigo_sunat

Solución:
1. store (creación): 'stock' => 'nullable|numeric'
   - numeric acepta enteros y decimales
   - Se eliminó min:0 para permitir stock negativo si es necesario
2. update (edición): se eliminó stock de la validación
   - El stock no se puede modificar manualmente al editar
   - Se controla mediante compras, consumos internos y ventas
3. Edit view: campo stock readonly (fondo gris, no editable)
   - El usuario ve el stock actual pero no puede cambiarlo
4. Create view: step="0.01" en lugar de min="0"
   - Permite ingresar decimales al crear el producto

Justificación:
- La primera vez que se crea el producto se puede establecer un stock inicial
- Después, el stock solo se modifica mediante transacciones:
  - Compra (purchase): incrementa stock
  - Consumo interno (stock-output): decrementa stock
  - Venta (invoice/pos): decrementa stock
- No es buena práctica permitir edición manual del stock
```

---

## 21. Solución de Problemas Comunes (Actualizado)

| Error | Causa | Solución |
|-------|-------|----------|
| `MissingAppKeyException` | APP_KEY inválida o cache desactualizado | `php artisan key:generate && php artisan config:clear` |
| `Duplicate entry for key 'products_company_id_codigo_unique'` | Código duplicado al duplicar producto | Corregido con `getNextProductCode()` que busca el código más alto |
| `Column 'company_id' cannot be null` | Usuario sin empresa asignada | `php artisan db:seed` o asignar manualmente |
| `Connection refused localhost:8080` | Reverb configurado sin servidor corriendo | Cambiar `BROADCAST_DRIVER=log` en `.env` |
| Print Server no responde | Quick Edit Mode de Windows | Usar `disable-quick-edit.ps1` o `start-hidden.vbs` |
| Cash drawer no abre | CORS bloqueando fetch local | Usar `mode: no-cors` + `Content-Type: application/x-www-form-urlencoded` |
| Certificado no se sube | Validación mimes rechaza .p12 | No usar `mimes:p12,pfx` en la validación |
| Certificado contraseña incorrecta | OpenSSL no puede leer .p12 | Verificar contraseña del certificado |
| Pedido no aparece en mesa | Fetch sin headers JSON | Agregar `Accept: application/json` a todos los fetch |
| Dos usuarios abren misma mesa | Race condition en openTable | Usar sistema de bloqueo con locked_by/locked_at |
| Serie no se puede editar | Parámetro de ruta incorrecto | Usar `->parameters(['series' => 'serie'])` en Route::resource |
| Ubigeo no funciona | Inputs de texto en lugar de selects | Usar selects con cascada JavaScript |
| Class "Address" not found | Falta import en SummaryService | Agregar `use Greenter\Model\Company\Address` |
| JSON.parse: unexpected character | Excepción PHP devuelve HTML 500 | Revisar `storage/logs/laravel.log` |
| Data too long for correlativo | Correlativo > 10 chars en DB | Migración para ampliar columna a 30 |
| "No se ha configurado la contraseña" | setupSee() verifica password antes de buscar PEM | Reordenar: PEM primero, PKCS12 después |
| Guía de remisión falla | Falta Address import en servicio | Agregar uso de Greenter\Model\Company\Address |
| Column 'status' no acepta PENDING_PAYMENT | ENUM no incluye el valor | Migración add_pending_payment_to_restaurant_orders_status |
| Error de conexión al confirmar autopedido | table_id = null en FK NOT NULL | Mesa virtual Kiosko (is_for_kiosko=true) ahora provee table_id válido |
| Fetch one-shot sin feedback al usuario | Falta .catch() | Agregar showError()/alert() en catch |
| Ticket de autopedido no se imprime | printAutoPedidoTicket() no llama processQueue() | Agregar $this->processQueue() después de queuePrint() |
| Comanda impresa sin productos | Filtro $dests roto en kitchenTicket() | Eliminar filtro redundante (printKitchenOrder ya agrupa) |
| Caracteres extraños en ticket (ðŸ§¾) | Emoji no compatible con CP850 | Usar texto plano, no emojis |
| Modal de cobro en kiosk-orders no funciona | HTML del modal faltaba en la vista | Agregar charge-overlay en kiosk-orders.blade.php |
| Numeración kiosko no se reinicia | generateKioskoOrderNumber() usaba today() | Cambiar a contar desde fecha_apertura de caja abierta |
| Kiosko permite pedidos sin caja abierta | Falta validación en confirmOrder() | Verificar CashRegister ABIERTA antes de crear pedido |
| Stock validation falla con decimales | Validación usaba integer pero DB es decimal | Cambiar a nullable\|numeric |
| Producto con stock negativo no se puede editar | min:0 en validación de update | Eliminar stock de validación de update |
| SUNAT rechaza XML con "Por Consumo" | Código de producto incorrecto | Usar código 90101801 (servicios restaurante) |
| Tildes y ñ se ven como caracteres extraños en ticket | Texto enviado como UTF-8 a impresora CP850 | Usar getEscPos() que convierte a CP850 vía utf8ToCp850() |

---

## 22. Comandos Artisan (Actualizado)

| Comando | Propósito |
|---------|-----------|
| `php artisan print:process-queue` | Procesa cola de impresión |
| `php artisan sunat:download-padron` | Descarga el padrón reducido de SUNAT |
| `php artisan sunat:check-summaries` | Consulta estado de resúmenes diarios pendientes |
| `php artisan sunat:send-daily-summary` | Agrupa y envía boletas del día en un Resumen Diario |
| `php artisan sunat:retry-pending` | Reenvía todas las facturas/boletas pendientes |
| `php artisan sunat:retry-pending --type=01` | Reenvía solo facturas pendientes |
| `php artisan sunat:retry-pending --type=03` | Reenvía solo boletas pendientes |
| `php artisan config:clear` | Limpia cache de configuración |
| `php artisan view:clear` | Limpia cache de vistas |
| `php artisan route:clear` | Limpia cache de rutas |
| `php artisan cache:clear` | Limpia cache de Laravel |
| `php artisan migrate` | Ejecuta migraciones |
| `php artisan db:seed` | Ejecuta seeders |
| `php artisan key:generate` | Genera APP_KEY |
| `php artisan route:list` | Lista rutas |
| `php artisan optimize` | Optimiza rendimiento |
| `php artisan migrate:rollback --step=1` | Revierte última migración |

---

## Anexo: Códigos de Error SUNAT

El archivo `docs/sunat/codigos-error-sunat.txt` contiene el listado completo de códigos de error de SUNAT (anexo 2), utilizado para depurar respuestas al enviar comprobantes electrónicos.

| Rango | Descripción |
|-------|-------------|
| 0100-0159 | Errores de autenticación, archivo ZIP y nombre de archivo |
| 0200-0307 | Errores de procesamiento batch y extracción ZIP |
| 0400-0403 | Errores de casos de prueba |
| 1001-1040 | Errores de validación XML (formato, tags obligatorios) |
| 2010-2101 | Errores de validación de datos del emisor y receptor |
| 2102-2200 | Errores de facturas, notas de crédito y débito |
| 2210-2280 | Errores de resumen diario (Summary) |
| 2281-2340 | Errores de comunicación de baja (Voided) |
| 2341-2420 | Errores de validación de negocio y reglas SUNAT |
| 4000-4041 | Errores de validación avanzada (RUC, montos, cálculos) |
