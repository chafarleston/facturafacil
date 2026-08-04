# TRD — Technical Requirements Document

## FacturaFácil: Especificación Técnica del Sistema

**Versión:** 2.0  
**Fecha:** Julio 2026  
**Stack:** PHP 8.2+ / Laravel 13.x / MySQL 8.0 / Node.js 18+

---

## 1. Arquitectura del Sistema

```
┌──────────────────────────────────────────────────────────┐
│                    Navegador (Cliente)                     │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐ │
│  │Restaurant│  │   POS    │  │Facturación│ │Admin Panel│ │
│  │ (Blade)  │  │ (Blade)  │  │ (Blade)   │ │  (Blade)  │ │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘ │
│       │ localStorage │              │              │       │
│       │  (POS tabs)  │              │              │       │
└───────┼──────────────┼──────────────┼──────────────┼──────┘
        │              │              │              │
        ▼              ▼              ▼              ▼
┌──────────────────────────────────────────────────────────┐
│              Laravel 13.x (Servidor PHP)                   │
│  ┌─────────────┐  ┌──────────────┐  ┌─────────────────┐ │
│  │ Controllers  │  │   Services   │  │  Models/Eloquent │ │
│  │  • Restaurant│  │ • Greenter   │  │  • Product       │ │
│  │  • POS       │  │ • Summary    │  │  • Invoice       │ │
│  │  • CashReg.  │  │ • Print      │  │  • CashRegister  │ │
│  │  • Invoice   │  │ • PlainText  │  │  • RestaurantO.  │ │
│  │  • Product   │  │ • SunatQr    │  │  • User          │ │
│  └─────────────┘  └──────────────┘  └─────────────────┘ │
│                          │                                │
│  ┌───────────────────────▼────────────────────────────┐  │
│  │               MySQL 8.0 (Base de Datos)              │  │
│  └────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────┘
        │
        │ HTTP (solo impresión)
        ▼
┌──────────────────────────────────────────────────────────┐
│           Print Server Node.js (localhost:9100)            │
│  ┌──────────┐  ┌──────────────┐  ┌────────────────────┐ │
│  │Impresora  │  │ Impresora    │  │ Cajón de Efectivo  │ │
│  │ USB Local │  │ Red (IP:9100)│  │ (Drawer Kick)      │ │
│  └──────────┘  └──────────────┘  └────────────────────┘ │
└──────────────────────────────────────────────────────────┘
```

**Comunicación entre componentes:**
- Cliente ↔ Laravel: HTTP/HTTPS (REST + Blade SSR)
- Laravel → Print Server: HTTP POST (localhost:9100)
- Print Server → Impresora: raw-print.ps1 (USB) / Socket TCP (Red)
- Laravel → SUNAT: SOAP vía Greenter 5.x

---

## 2. Base de Datos

### 2.1 Tablas del Sistema (21 tablas)

| # | Tabla | Propósito | Registros típicos |
|---|-------|-----------|-------------------|
| 1 | `companies` | Empresa (RUC, certificado, IGV) | 1 |
| 2 | `users` | Usuarios del sistema | 5-10 |
| 3 | `roles` | Roles (admin, cajero, mozo, user) | 4 |
| 4 | `permissions` | Permisos del sistema | 46 |
| 5 | `role_user` | Pivot: rol ↔ usuario | 5-10 |
| 6 | `role_permission` | Pivot: permiso ↔ rol | 200+ |
| 7 | `customers` | Clientes | 100-1000 |
| 8 | `categories` | Categorías de productos | 20-50 |
| 9 | `products` | Productos (catálogo) | 100-500 |
| 10 | `product_components` | Componentes de prod. compuestos | 0-100 |
| 11 | `invoices` | Comprobantes emitidos | 1000-5000/mes |
| 12 | `invoice_items` | Items de comprobantes | 5000-20000/mes |
| 13 | `series` | Series documentales (F001, B001) | 5-10 |
| 14 | `floors` | Pisos del restaurante | 1-3 |
| 15 | `restaurant_tables` | Mesas del restaurante | 10-50 |
| 16 | `restaurant_orders` | Pedidos | 100-500/día |
| 17 | `restaurant_order_items` | Items de pedidos | 500-3000/día |
| 18 | `cashregisters` | Apertura/cierre de caja | 1-2/día |
| 19 | `purchases` | Compras a proveedores | 10-50/mes |
| 20 | `purchase_items` | Items de compras | 50-200/mes |
| 21 | `suppliers` | Proveedores | 5-20 |
| 22 | `printers` | Configuración de impresoras | 8 |
| 23 | `print_jobs` | Cola de impresión | 100-500/día |
| 24 | `ubigeos` | Catálogo ubigeos SUNAT | 1874 |
| 25 | `sunat_products` | Catálogo productos SUNAT | ~5000 |
| 26 | `sunat_summaries` | Resúmenes diarios enviados | 1-2/día |
| 27 | `auxiliary_items` | Elementos auxiliares | 0-50 |
| 28 | `special_documents` | Docs especiales (guía, retención) | 0-10/mes |

### 2.2 Estructura Detallada de Tablas Clave

#### `products`
```sql
CREATE TABLE products (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT NOT NULL,
    codigo VARCHAR(50) NOT NULL,
    codigo_barras VARCHAR(50) NULL,
    descripcion VARCHAR(255) NOT NULL,
    codigo_sunat VARCHAR(8) NULL,
    umedida_codigo VARCHAR(3) DEFAULT 'NIU',
    precio DECIMAL(12,2) DEFAULT 0,
    precio_minimo DECIMAL(12,2) NULL,
    precio_compra DECIMAL(12,4) DEFAULT 0,
    tipo_afectacion ENUM('GRA','EXO','INA','EXE') DEFAULT 'GRA',
    igv_percent DECIMAL(5,2) DEFAULT 18,
    estado ENUM('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
    category_id BIGINT NULL,
    stock DECIMAL(12,4) DEFAULT 0,
    kds_destination VARCHAR(20) NULL,
    is_composite BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    FOREIGN KEY (category_id) REFERENCES categories(id),
    UNIQUE KEY (company_id, codigo),
    INDEX idx_products_company_estado (company_id, estado),
    INDEX idx_products_barcode (codigo_barras)
);
```

#### `product_components`
```sql
CREATE TABLE product_components (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    parent_product_id BIGINT NOT NULL,
    component_product_id BIGINT NOT NULL,
    quantity DECIMAL(10,2) DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (parent_product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (component_product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_component (parent_product_id, component_product_id)
);
```

#### `restaurant_orders`
```sql
CREATE TABLE restaurant_orders (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT NOT NULL,
    table_id BIGINT NOT NULL,
    user_id BIGINT NULL,
    order_number VARCHAR(20) NOT NULL,
    status ENUM('OPEN','SENT_TO_KITCHEN','READY','DELIVERED','COMPLETED','CANCELLED','PENDING_PAYMENT'),
    order_type VARCHAR(20) DEFAULT 'mozo',
    subtotal DECIMAL(12,2) DEFAULT 0,
    igv DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) DEFAULT 0,
    notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    FOREIGN KEY (table_id) REFERENCES restaurant_tables(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_orders_company_status (company_id, status),
    INDEX idx_orders_type (order_type)
);
```

#### `restaurant_order_items`
```sql
CREATE TABLE restaurant_order_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    restaurant_order_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    kitchen_status ENUM('PENDING','SENT','READY','DELIVERED','CANCELLED') DEFAULT 'PENDING',
    notes TEXT NULL,
    auxiliary_items JSON NULL,
    kds_destination VARCHAR(20) DEFAULT 'cocina',
    sent_to_kitchen_at TIMESTAMP NULL,
    cancelled_from VARCHAR(20) NULL,
    cancelled_at TIMESTAMP NULL,
    cancelled_by BIGINT NULL,
    paid_invoice_id BIGINT NULL,   -- Dividir Cuenta: invoice que pagó el item
    FOREIGN KEY (restaurant_order_id) REFERENCES restaurant_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (cancelled_by) REFERENCES users(id),
    INDEX idx_items_order_status (restaurant_order_id, kitchen_status)
);
```

#### `invoices`
```sql
CREATE TABLE invoices (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT NOT NULL,
    customer_id BIGINT NULL,
    tipo_documento ENUM('01','03','NV') NOT NULL,
    serie VARCHAR(10) NOT NULL,
    numero INT NOT NULL,
    full_number VARCHAR(30) NOT NULL,
    fecha_emision DATE NOT NULL,
    hora_emision TIME NULL,
    fecha_vencimiento DATE NULL,
    moneda VARCHAR(3) DEFAULT 'PEN',
    subtotal DECIMAL(12,2) DEFAULT 0,
    gravado DECIMAL(12,2) DEFAULT 0,
    igv DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) DEFAULT 0,
    total_letras VARCHAR(255) NULL,
    metodo_pago VARCHAR(100) DEFAULT 'EFECTIVO',
    referencia_pago VARCHAR(100) NULL,
    sunat_estado VARCHAR(20) DEFAULT 'PENDIENTE',
    order_source VARCHAR(20) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    INDEX idx_invoices_company_tipo_fecha (company_id, tipo_documento, fecha_emision),
    INDEX idx_invoices_sunat_estado (sunat_estado)
);
```

#### `invoice_items`
```sql
CREATE TABLE invoice_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT NOT NULL,
    product_id BIGINT NULL,
    codigo VARCHAR(50) NULL,
    descripcion VARCHAR(255) NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    umedida VARCHAR(3) DEFAULT 'NIU',
    precio_unitario DECIMAL(12,2) NOT NULL,
    precio_venta DECIMAL(12,2) NOT NULL,
    igv DECIMAL(12,2) NOT NULL,
    tipo_afectacion VARCHAR(5) DEFAULT '10',
    igv_percent DECIMAL(5,2) DEFAULT 18,
    detalle_consumo JSON NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);
```

#### `cashregisters`
```sql
CREATE TABLE cashregisters (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    monto_apertura DECIMAL(12,2) DEFAULT 0,
    monto_cierre DECIMAL(12,2) NULL,
    ventas_efectivo DECIMAL(12,2) DEFAULT 0,
    ventas_tarjeta DECIMAL(12,2) DEFAULT 0,
    ventas_yape DECIMAL(12,2) DEFAULT 0,
    ventas_plin DECIMAL(12,2) DEFAULT 0,
    ventas_otro DECIMAL(12,2) DEFAULT 0,
    cantidad_ventas INT DEFAULT 0,
    total_ventas DECIMAL(12,2) DEFAULT 0,
    estado ENUM('ABIERTA','CERRADA') DEFAULT 'ABIERTA',
    fecha_apertura TIMESTAMP NULL,
    fecha_cierre TIMESTAMP NULL,
    observaciones TEXT NULL,
    referencia VARCHAR(255) NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_cash_company_estado (company_id, estado)
);
```

### 2.3 Migraciones del Sistema

| Migración | Descripción |
|-----------|-------------|
| `2024_01_01_000001` | Tabla companies |
| `2024_01_01_000002` | Tabla customers |
| `2024_01_01_000003` | Tabla products |
| `2024_01_01_000005` | Tabla invoices |
| `2024_01_01_000006` | Tabla invoice_items |
| `2026_04_27_000002` | Stock en products, suppliers, purchases |
| `2026_05_12_104536` | Tablas restaurant_tables |
| `2026_05_12_104627` | Tablas restaurant_orders/items |
| `2026_05_13_000001` | Roles y permisos |
| `2026_05_21_083855` | Config IGV en companies |
| `2026_05_31_000001` | stock_outputs tables |
| `2026_06_10_000001` | Bloqueo de mesas |
| `2026_06_19_000001` | order_source y order_type |
| `2026_07_05_000002` | is_composite en products |
| `2026_07_05_000003` | Tabla product_components |
| `2026_07_07_181559` | precio_compra en products |
| `2026_07_08_000001` | detalle_consumo en invoice_items |

### 2.4 Índices Críticos

| Tabla | Índice | Justificación |
|-------|--------|---------------|
| `invoices` | `(company_id, tipo_documento, fecha_emision, sunat_estado)` | Reportes, dashboard, cierre caja |
| `restaurant_orders` | `(company_id, status)` | Polling cada 10s |
| `restaurant_orders` | `(order_type)` | Filtro kiosko vs mozo |
| `restaurant_order_items` | `(restaurant_order_id, kitchen_status)` | KDS polling cada 5s |
| `products` | `(company_id, estado)` | Búsquedas en POS/Restaurante |
| `cashregisters` | `(company_id, estado)` | Apertura/cierre |
| `print_jobs` | `(status, attempts)` | Procesamiento de cola |
| `series` | `(company_id, tipo_documento, estado)` | Búsqueda de serie al crear documento |

---

## 3. Modelos Eloquent

### 3.1 Product

```php
// Relaciones
public function components(): HasMany        // ProductComponent
public function category(): BelongsTo         // Category
public function invoiceItems(): HasMany       // InvoiceItem

// Scopes
public function scopeSimple($query)           // is_composite = false
public function scopeComposite($query)         // is_composite = true

// Accessors
public function isComposite(): bool           // is_composite == true

// Casts
protected $casts = [
    'stock' => 'decimal:4',
    'precio' => 'decimal:2',
    'precio_compra' => 'decimal:4',
    'is_composite' => 'boolean',
];

// Fillable
'company_id', 'codigo', 'codigo_barras', 'descripcion', 'codigo_sunat',
'umedida_codigo', 'precio', 'precio_minimo', 'precio_compra',
'tipo_afectacion', 'igv_percent', 'estado', 'category_id',
'stock', 'kds_destination', 'is_composite'
```

### 3.2 ProductComponent

```php
// Relaciones
public function parent(): BelongsTo       // Product (producto compuesto)
public function component(): BelongsTo    // Product (producto componente)

// Casts
protected $casts = ['quantity' => 'decimal:2'];

// Fillable
'parent_product_id', 'component_product_id', 'quantity'
```

### 3.3 RestaurantOrder

```php
// Constantes de estado
const STATUS_OPEN = 'OPEN';
const STATUS_SENT_TO_KITCHEN = 'SENT_TO_KITCHEN';
const STATUS_READY = 'READY';
const STATUS_DELIVERED = 'DELIVERED';
const STATUS_COMPLETED = 'COMPLETED';
const STATUS_CANCELLED = 'CANCELLED';

// Relaciones
public function table(): BelongsTo
public function user(): BelongsTo
public function items(): HasMany

// Métodos
public static function generateOrderNumber(): string       // P-YYYYMMDD-NNNN
public static function generateKioskoOrderNumber(): string // A-NNN
public function statusLabel(): string

// Casts
protected $casts = [
    'subtotal' => 'decimal:2',
    'igv' => 'decimal:2',
    'total' => 'decimal:2',
];

// Fillable
'company_id', 'table_id', 'user_id', 'order_number', 'status',
'order_type', 'subtotal', 'igv', 'total', 'notes'
```

### 3.4 RestaurantOrderItem

```php
// Relaciones
public function order(): BelongsTo
public function product(): BelongsTo
public function cancelledBy(): BelongsTo  // User

// Fillable
'restaurant_order_id', 'product_id', 'product_name', 'quantity',
'unit_price', 'total', 'kitchen_status', 'notes', 'auxiliary_items',
'kds_destination', 'sent_to_kitchen_at', 'cancelled_from',
'cancelled_at', 'cancelled_by', 'paid_invoice_id'
```

### 3.5 InvoiceItem

```php
// Fillable
'invoice_id', 'product_id', 'codigo', 'descripcion', 'cantidad',
'umedida', 'precio_unitario', 'precio_venta', 'igv',
'tipo_afectacion', 'igv_percent', 'detalle_consumo'

// Casts
protected $casts = [
    'cantidad' => 'decimal:4',
    'precio_unitario' => 'decimal:4',
    'precio_venta' => 'decimal:2',
    'igv' => 'decimal:2',
    'detalle_consumo' => 'array',
];
```

### 3.6 CashRegister

```php
// Estados: ABIERTA | CERRADA
// Fillable: company_id, user_id, monto_apertura, monto_cierre,
//   ventas_efectivo, ventas_tarjeta, ventas_yape, ventas_plin, ventas_otro,
//   cantidad_ventas, total_ventas, estado, fecha_apertura, fecha_cierre,
//   observaciones, referencia
```

---

## 4. Controladores y Rutas

### 4.1 CashRegisterController

| Método | Ruta | Método HTTP | Auth |
|--------|------|-------------|------|
| `index()` | `/cashregisters` | GET | auth + permission `view_cashregisters` |
| `open()` | `/cashregister/open` | POST | auth + permission `open_cashregister` |
| `close()` | `/cashregister/close` | POST | auth + permission `close_cashregister` |
| `show()` | `/cashregisters/{id}` | GET | auth + permission `view_cashregisters` |
| `pdf()` | `/cashregisters/{id}/pdf` | GET | auth + permission `view_cashregisters` |
| `ticketPdf()` | `/cashregisters/{id}/ticket` | GET | auth + permission `view_cashregisters` |
| `printCaja()` | `/cashregisters/{id}/print-caja` | POST | auth + permission `view_cashregisters` |

**Lógica de cierre (`close()`):**
```
1. Validar: cashregister_id, monto_cierre, observaciones
2. Verificar: caja no cerrada ya
3. Contar mesas abiertas (order_type != 'kiosko')
4. Contar pedidos kiosko (order_type = 'kiosko')
5. Si hay abiertos → mensaje específico (mesas vs kiosko)
6. Filtrar ventas: CONCAT(fecha_emision, hora_emision) BETWEEN apertura AND cierre
7. Sumar totales por método de pago (EFECTIVO, TARJETA, YAPE, PLIN, OTRO)
8. Sumar totales por tipo documento (01, 03, NV)
9. Actualizar cash register con todos los montos → estado CERRADA
```

**Lógica de métodos de pago (compartida por `show()` y `printCaja()`):**
```php
foreach ($ventas as $venta) {
    $pago = $venta->metodo_pago;  // "YAPE/80 + EFECTIVO/15"
    if (str_contains($pago, ' + ')) {
        foreach (explode(' + ', $pago) as $part) {
            if (str_contains($part, '/')) {
                [$metName, $metAmt] = explode('/', $part);  // Lee monto real
                $amt = min((float) $metAmt, $venta->total);
            } else {
                $metName = $part;
                $amt = round($venta->total / count($parts), 2);
            }
            $key = strtoupper($metName);
            // match: str_starts_with EFECT/TARJ, $key === YAPE/PLIN
        }
    } else {
        $key = strtoupper(explode('/', $pago)[0]);
        // match directo con el total
    }
}
```

### 4.2 RestaurantController (métodos principales)

| Método | Ruta | Propósito |
|--------|------|-----------|
| `index()` | GET `/restaurant` | Vista principal con pisos y mesas |
| `openTable($id)` | POST `/restaurant/tables/{id}/open` | Abrir mesa (crea orden) |
| `addItem($id)` | POST `/restaurant/orders/{id}/items` | Agregar producto al pedido |
| `updateItem($id)` | PUT `/restaurant/orders/items/{id}` | Modificar cantidad/notas |
| `removeItem($id)` | DELETE `/restaurant/orders/items/{id}` | Eliminar item (PENDING→delete; SENT/READY/DELIVERED→CANCELLED con password admin) |
| `sendToKitchen($id)` | POST `/restaurant/orders/{id}/send-to-kitchen` | Enviar a cocina |
| `chargeOrder($id)` | POST `/restaurant/orders/{id}/charge` | Cobrar pedido |
| `splitChargeOrder($id)` | POST `/restaurant/orders/{id}/split-charge` | Dividir cuenta en 2+ comprobantes |
| `cancelOrder($id)` | POST `/restaurant/orders/{id}/cancel` | Anular pedido completo |
| `getActiveOrders()` | GET `/restaurant/active-orders` | Polling |
| `getTableLocks()` | GET `/restaurant/locks` | Polling bloqueos |
| `getKitchenOrders()` | GET `/restaurant/kitchen-orders` | Polling KDS |

**Lógica de `chargeOrder()`:**
```
1. Verificar: usuario no mozo
2. Verificar: caja abierta
3. Validar: orden no OPEN, tiene items
4. $soloConsumo = $request->boolean('solo_consumo')
5. Calcular IGV: $igvRate = $company->getIgvRate()
6. Crear Invoice
7. Si soloConsumo: 1 InvoiceItem "POR CONSUMO" + detalle_consumo (JSON)
8. Si no: N InvoiceItems individuales
9. Descontar stock (productos compuestos: descuentan componentes)
10. Incrementar serie, marcar orden COMPLETED, liberar mesa
```

### 4.3 PosController

| Método | Ruta | Propósito |
|--------|------|-----------|
| `index()` | GET `/pos` | Vista POS |
| `store()` | POST `/pos` | Procesar venta |
| `success($id)` | GET `/pos/success/{id}` | Página de éxito |
| `sendToSunat($id)` | POST `/pos/sunat/{id}` | Enviar a SUNAT |
| `printInvoice($id,$format)` | GET `/pos/print/{id}/{format}` | Imprimir |
| `openDrawer()` | POST `/pos/open-drawer` | Abrir cajón |

### 4.4 ProductController

| Método | Ruta | Propósito |
|--------|------|-----------|
| `index()` | GET `/products` | Lista con filtros |
| `store()` / `update()` | POST/PUT | CRUD |
| `createComposite()` | GET `/products/composite/create` | Form producto compuesto |
| `storeComposite()` | POST `/products/composite/store` | Guardar compuesto |
| `editComposite($p)` | GET `/products/{p}/composite/edit` | Editar compuesto |
| `updateComposite($p)` | PUT `/products/{p}/composite/update` | Actualizar compuesto |
| `inventoryReport()` | GET `/products/inventory-report` | Reporte inventario |
| `inventoryReportExcel()` | GET `/products/inventory-report/excel` | Excel |
| `inventoryReportPdf()` | GET `/products/inventory-report/pdf` | PDF |
| `importStore()` | POST `/products/import` | Importar Excel |
| `downloadTemplate()` | GET `/products/import/template` | Plantilla |

---

## 5. Servicios

### 5.1 GreenterService (`app/Services/GreenterService.php`)

| Método | Propósito | SUNAT |
|--------|-----------|-------|
| `sendInvoice($invoice)` | Envía factura (01) | BillSender SOAP |
| `sendCreditNote($invoice, ...)` | Nota de crédito (07) | BillSender / Summary |
| `sendDebitNote($invoice, ...)` | Nota de débito (08) | BillSender / Summary |
| `voidInvoice($invoice)` | Baja de factura | Voided |
| `setupSee($company)` | Configura certificado | PEM-first |
| `buildInvoice($invoice, $company)` | Construye XML Greenter (helper privado) | - |
| `generatePdf($invoice)` | PDF A4 | mPDF |
| `getClientData($invoice)` | Datos del cliente | - |

**Ruteo SUNAT:**
```
Factura (01) → sendInvoice() → BillSender SOAP
Boleta (03)  → sendBoletaToSummary() → SummarySender
NC Factura   → sendCreditNote() → BillSender SOAP
NC Boleta    → sendNoteViaSummary() → SummarySender
ND Factura   → sendDebitNote() → BillSender SOAP
ND Boleta    → sendNoteViaSummary() → SummarySender
Baja Factura → voidInvoice() → Voided
Baja Boleta  → voidBoleta() → Summary estado=3
```

### 5.2 SummaryService (`app/Services/SummaryService.php`)

| Método | Propósito |
|--------|-----------|
| `setupSee($company)` | Configura certificado (PEM-first) |
| `sendBoletaToSummary($invoice)` | Agrega boleta a resumen diario |
| `sendDailySummary()` | Agrupa y envía boletas del día |
| `voidBoleta($invoice)` | Anula boleta (estado=3 en summary) |
| `sendNoteToSummary($note, ...)` | NC/ND de boleta por summary |
| `checkTicketStatus($ticket)` | Consulta estado del ticket |
| `getNextCorrelativo($company)` | Correlativo RC-YYYYMMDD-NNN |

### 5.3 PrintService (`app/Services/PrintService.php`)

| Método | Propósito |
|--------|-----------|
| `printKitchenOrder($order, $items)` | Comanda de cocina (agrupada por destino) |
| `printPrebill($order, $key)` | Precuenta |
| `printCancelNotification($order, $item)` | Anulación individual |
| `printCancelNotificationGrouped($order, $items)` | Anulación agrupada |
| `printInvoice($invoice)` | No-op (invoiceTicket es stub; comprobante por PDF Greenter) |
| `printAutoPedidoTicket($order)` | Ticket kiosko |
| `processQueue()` | Procesa cola de impresión |
| `queuePrint($printer, $data, ...)` | Encola trabajo |

**Flujo de impresión:**
```
1. Controlador → PrintService::printXxx()
2. Genera texto ESC/POS vía PlainTextTicket
3. queuePrint() crea PrintJob (status: pending)
4. processQueue() envía HTTP POST a localhost:9100/print
5. Print Server recibe y envía a impresora
6. Éxito: completed | Falla: failed (reintentos < 3)
```

### 5.4 PlainTextTicket (`app/Services/PlainTextTicket.php`)

Genera tickets en texto plano con formato ESC/POS.

| Método | Destino | Contenido |
|--------|---------|-----------|
| `kitchenTicket($order, $format, $dest)` | cocina-1/2, bar-1 | Header + items |
| `prebillTicket($order, $format)` | precuenta | Items + total + IGV |
| `cancelNotification($order, $item, ...)` | cocina/bar | Item cancelado |
| `cancelNotificationGrouped($order, ...)` | cocina/bar | Items cancelados agrupados (incluye "Anulado por") |
| `invoiceTicket($invoice, $format)` | caja | Stub (no-op); comprobante por PDF Greenter |
| `cashRegisterSummary($cash, $data, ...)` | caja | Cierre completo |

**Encoding:** CP850 con tabla de mapeo manual para caracteres especiales (ñ, tildes).

### 5.5 PrintServerService (`app/Services/PrintServerService.php`)

| Método | Propósito |
|--------|-----------|
| `isServerRunning()` | Health check (GET /status) |
| `getAvailablePrinters()` | Lista impresoras del sistema |
| `printText($printer, $text)` | Envía texto para imprimir |

---

## 6. Frontend

### 6.1 Tecnologías

| Componente | Tecnología |
|-----------|-----------|
| Plantillas | Blade (AdminLTE) |
| CSS | Tailwind CSS + AdminLTE |
| JavaScript | Vanilla JS (sin frameworks) |
| Gráficos | Chart.js |
| Impresión | ESC/POS vía fetch a localhost:9100 |

### 6.2 POS Multi-venta (localStorage)

```javascript
// Estructura de datos
let saleTabs = [
    {
        id: 1,
        name: 'Venta 1',
        items: [{id, name, price, quantity, stock, is_composite}],
        customerId: null,
        customerName: '',
        documentType: 'NV',
        paymentMethod: 'EFECTIVO',
        createdAt: '2026-07-15T10:00:00'
    }
];
let activeTabId = 1;

// Persistencia
const STORAGE_KEY = 'pos_tabs';
const STORAGE_ACTIVE = 'pos_activeTab';

// Funciones clave
function saveTabsToStorage() { localStorage.setItem(...); }
function loadTabsFromStorage() { ... }
function switchToTab(tabId) { ... }
function addNewTab() { ... }
function closeTab(tabId) { ... }
```

### 6.3 Polling (Restaurante y KDS)

| Vista | Función | Intervalo | Endpoint |
|-------|---------|-----------|----------|
| Restaurante | `pollActiveOrders()` | 10s | `/restaurant/active-orders` |
| Restaurante | `pollTableLocks()` | 10s | `/restaurant/locks` |
| Restaurante | `pollPrintServer()` | 10s | `/restaurant/print-status` |
| KDS | `loadKitchenOrders()` | 5s | `/restaurant/kitchen-orders?kds={cocina\|cocina2\|bar}` |

**Headers requeridos en todos los fetch:**
```javascript
headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
}
```

**Manejo de errores:** `.catch(() => {})` silencioso para polling, `showError()` para acciones del usuario.

---

## 7. APIs REST

### 7.1 Endpoints (propuesta para versión futura)

**Autenticación:** Laravel Sanctum (token-based)

**Productos:**
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/products` | Listar con filtros (?category_id=, ?search=) |
| GET | `/api/products/{id}` | Detalle con componentes si es compuesto |
| GET | `/api/categories` | Listar categorías |

**POS:**
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/pos/sale` | Registrar venta |
| GET | `/api/pos/series` | Series disponibles |

**Restaurante:**
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/tables` | Mesas con estado |
| POST | `/api/orders` | Crear pedido (abrir mesa) |
| GET | `/api/orders/{id}` | Detalle del pedido |
| POST | `/api/orders/{id}/items` | Agregar item |
| PUT | `/api/orders/items/{id}` | Modificar item |
| DELETE | `/api/orders/items/{id}` | Eliminar item |
| POST | `/api/orders/{id}/send-to-kitchen` | Enviar a cocina |
| POST | `/api/orders/{id}/charge` | Cobrar |
| GET | `/api/kitchen/orders` | Pedidos en cocina (?kds=cocina) |
| POST | `/api/kitchen/{id}/ready` | Marcar listo |

**Caja:**
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/cash-register/status` | Estado actual (abierta/cerrada) |
| POST | `/api/cash-register/open` | Abrir caja |
| POST | `/api/cash-register/close` | Cerrar caja |
| GET | `/api/cash-register/{id}/summary` | Resumen |

---

## 8. Seguridad

### 8.1 Medidas Implementadas

| Área | Medida | Implementación |
|------|--------|---------------|
| **Command Injection** | Sanitización de exec() | `escapeshellarg()` en CompanyController, BackupController |
| **CSRF** | Protección en formularios | `@csrf` en todos los forms vía Blade |
| **Auth** | Middleware en rutas | `auth`, `admin`, permisos vía `@can` |
| **XSS** | Escape automático | Blade `{{ }}` por defecto |
| **Passwords** | Pendiente de cifrar | `certificado_password`, `soap_password` en texto plano |
| **API Auth** | Sanctum token | Solo ruta `/api/user` actualmente |

### 8.2 Pendientes de Implementar

| Área | Riesgo | Acción |
|------|--------|--------|
| Passwords BD | Alto | Cifrar con `encrypt()`/`decrypt()` de Laravel |
| SSL verification | Alto | Habilitar `CURLOPT_SSL_VERIFYPEER` en DecolectaController |
| GET logout | Medio | Eliminar ruta GET `/logout`, solo POST |
| Column injection | Medio | Whitelist en `searchType` de ProductController |
| Stack traces | Bajo | No exponer en producción (`APP_DEBUG=false`) |

---

## 9. Comandos Artisan y Tareas Programadas

### 9.1 Comandos

| Comando | Propósito | Frecuencia |
|---------|-----------|------------|
| `print:process-queue` | Procesa cola de impresión | Cada 1 min (scheduler) |
| `sunat:send-daily-summary` | Agrupa boletas en resumen diario | Manual / scheduler |
| `sunat:check-summaries` | Consulta estado de tickets | Manual / scheduler |
| `sunat:retry-pending` | Reintenta comprobantes PENDIENTE/RECHAZADO | Manual |
| `sunat:download-padron` | Descarga y extrae el padrón SUNAT (elimina el ZIP) | Semanal (domingo 02:00) |

### 9.2 Limpieza de Caché

```bash
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear
```

**Regla:** Siempre ejecutar después de cambiar rutas o vistas.

### 9.3 Migraciones

```bash
php artisan migrate           # Ejecutar pendientes
php artisan migrate:rollback   # Revertir última (--step=1)
php artisan migrate:status     # Ver estado
```

---

## 10. Despliegue

### 10.1 Requisitos del Servidor

| Componente | Requisito |
|-----------|-----------|
| PHP | 8.2+ con extensiones: bcmath, gd, mbstring, openssl, pdo_mysql, xml, zip, soap, intl |
| MySQL | 8.0+ o MariaDB 10.4+ |
| Node.js | 18+ (solo para print server) |
| Composer | 2.x |
| Git | Para despliegue |
| Windows | Para print server (raw-print.ps1) |

### 10.2 Instalación

```bash
git clone <repo> facturafacil
cd facturafacil
composer install --no-dev
cp .env.example .env   # Configurar DB, APP_KEY
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
cd print-server-node && npm install
```

### 10.3 Tareas Programadas (Windows)

**Archivo:** `scheduler.vbs`

```vbs
' Inicia scheduler de Laravel + Print Server
CreateObject("WScript.Shell").Run "php artisan schedule:work", 0
CreateObject("WScript.Shell").Run "node print-server-node/server.js", 0
```

---

## 11. Control de Versiones

| Versión | Fecha | Cambios Técnicos |
|---------|-------|-----------------|
| 1.0 | Junio 2026 | Laravel 13.x base, Greenter 5.x, print server Node.js |
| 2.0 | Julio 2026 | is_composite + product_components, precio_compra, detalle_consumo (JSON), POS multi-tab localStorage, fix método pago Yape/Plin, ticket caja completo, fix command injection, fix $dest en kitchenTicket |
| 2.1 | Agosto 2026 | Dividir Cuenta (paid_invoice_id + split-charge), permisos SUNAT (send_sunat a cajero; user sin comprobantes/caja), rutas de caja por permiso, apertura de cajón en POS (manual + automática en efectivo), IGV dinámico en precuenta, Greenter v5.3.0 |
