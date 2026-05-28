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
status: OPEN (ABIERTO) | SENT_TO_KITCHEN (ENVIADO A COCINA) | READY (LISTO) | DELIVERED (ENTREGADO) | COMPLETED (COMPLETADO) | CANCELLED (ANULADO)
subtotal, igv, total, notes
```

### 3.6 RestaurantOrderItem

```php
restaurant_order_id → orders
product_id → products
product_name, quantity (decimal), unit_price, total
kitchen_status: PENDING (PENDIENTE) | SENT (ENVIADO) | READY (LISTO) | DELIVERED (ENTREGADO) | CANCELLED (ANULADO)
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
| `sendToSunat()` | GET `/invoices/{id}/send` | Envía a SUNAT |
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
| `GreenterService` | Servicio de facturación SUNAT (no es controlador) |
| `PrintService` | Servicio de impresión (no es controlador) |

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
sendInvoice($invoice)          // Envía factura/boleta a SUNAT
sendCreditNote($invoice, ...)  // Envía nota de crédito
generatePdf($invoice)          // Genera PDF A4
generateTicketPdf($invoice)    // Genera PDF ticket 80mm
buildInvoice($invoice)         // Construye objeto Greenter para XML
```

### 5.4 PrintServerService (`app/Services/PrintServerService.php`)

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

---

## 14. Seeders

| Seeder | Datos que crea |
|--------|---------------|
| `AdminUserSeeder` | Empresa demo + usuarios admin |
| `SuperAdminSeeder` | Usuario Cajero (Caja@gmail.com) |
| `TestUsersSeeder` | Usuarios demo (admin, mozo, user) + roles en pivot |
| `SeriesSeeder` | Series F001, B001, NV01, FC01, BC01, FD01, BD01 |
| `SunatProductSeeder` | Productos de ejemplo |
| `PermissionsSeeder` | Todos los permisos + roles (admin, mozo, cajero, user) |
| `PrinterSeeder` | 7 slots de impresora |
| `UbigeoSeeder` | 1874 registros de ubigeos (departamentos, provincias, distritos) |
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
| GET | `/restaurant/kitchen-orders` | Polling KDS |
| POST | `/restaurant/tables/{id}/open` | Abrir mesa |
| POST | `/restaurant/orders/{id}/items` | Agregar producto |
| POST | `/restaurant/orders/{id}/send-to-kitchen` | Enviar a cocina |
| POST | `/restaurant/orders/{id}/charge` | Cobrar |
| POST | `/restaurant/orders/{id}/cancel` | Anular |
| POST | `/restaurant/orders/{id}/move-table` | Mover mesa |
| POST | `/restaurant/orders/{id}/print-prebill/{key}` | Imprimir precuenta |
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
| GET | `/printers` | Configurar impresoras |
| GET | `/printers/queue` | Cola de impresión |
| POST | `/companies/{id}/certificate` | Subir certificado |

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
