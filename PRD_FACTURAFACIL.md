# PRD — Product Requirements Document

## FacturaFácil: Sistema de Facturación Electrónica y Gestión de Restaurante

**Versión:** 2.0  
**Fecha:** Julio 2026  
**Estado:** En Producción

---

## 1. Objetivo del Sistema

FacturaFácil es un sistema integral para restaurantes peruanos que unifica:
- **Facturación electrónica SUNAT** (boletas, facturas, notas de crédito/débito)
- **Punto de Venta (POS)** multi-venta con persistencia en navegador
- **Gestión de restaurante** con mesas, pedidos y envío a cocina
- **KDS (Kitchen Display System)** para pantalla de cocina
- **Kiosko de autopedidos** para clientes
- **Control de caja** con apertura, cierre y reportes
- **Gestión de inventario** con productos simples y compuestos
- **Impresión térmica** mediante print server Node.js

---

## 2. Usuarios del Sistema

| Rol | Permisos Clave | Uso Principal |
|-----|---------------|---------------|
| **Admin** | Todo | Configuración, productos, usuarios, reportes |
| **Cajero** | POS, facturación, envío a SUNAT, caja (abrir, NO cerrar), autoriza anulaciones de cocina con su contraseña, registra ingresos y gastos de caja | Caja, cobros |
| **Mozo** | Restaurante, cocina (sin cobrar ni anular) | Tomar pedidos, enviar a cocina |
| **Cliente** | Solo kiosko de autopedidos | Hacer pedidos sin intervención |

---

## 3. Módulos del Sistema

### 3.1 POS (Punto de Venta)

**Propósito:** Venta rápida sin vinculación a mesas del restaurante.

| ID | Requisito | Prioridad | Estado |
|----|-----------|-----------|--------|
| POS-01 | Búsqueda de productos por nombre o código de barras | P0 | ✅ |
| POS-02 | Carrito de compras con cantidades +/− y eliminación | P0 | ✅ |
| POS-03 | Multi-venta con tabs persistentes en localStorage | P1 | ✅ |
| POS-04 | Selector de tipo documento (Boleta/Factura/NV) | P0 | ✅ |
| POS-05 | Selector de cliente con búsqueda y creación rápida | P0 | ✅ |
| POS-06 | Método de pago: Efectivo, Tarjeta, Yape, Plin, Transferencia | P0 | ✅ |
| POS-07 | Cálculo automático de IGV según tipo de empresa | P0 | ✅ |
| POS-08 | Impresión de comprobante (A4 y 80mm) | P0 | ✅ |
| POS-09 | Envío a SUNAT desde el modal de éxito | P1 | ✅ |
| POS-10 | Apertura de cajón de efectivo (botón manual) | P1 | ✅ |
| POS-11 | Persistencia de tabs al cerrar navegador | P1 | ✅ |
| POS-12 | Venta de productos compuestos con descuento de stock | P2 | ✅ |
| POS-13 | Apertura automática del cajón al cobrar en efectivo | P1 | ✅ |

### 3.2 Restaurante

**Propósito:** Gestión de pedidos vinculados a mesas con envío a cocina.

| ID | Requisito | Prioridad | Estado |
|----|-----------|-----------|--------|
| RES-01 | Vista de pisos con mesas organizadas por piso | P0 | ✅ |
| RES-02 | Apertura de mesa con generación de número de pedido (P-YYYYMMDD-NNNN) | P0 | ✅ |
| RES-03 | Agregar productos al pedido con cantidad y notas de cocina | P0 | ✅ |
| RES-04 | Modificar cantidad o eliminar items del pedido | P0 | ✅ |
| RES-05 | Items eliminados requieren contraseña admin si ya fueron enviados a cocina | P0 | ✅ |
| RES-06 | Envío a cocina con asignación de destino KDS (cocina, cocina2, bar) | P0 | ✅ |
| RES-07 | Precuenta (3 impresoras configurables: precuenta, precuenta2, precuenta3) | P1 | ✅ |
| RES-08 | Cobro de pedido con generación de comprobante | P0 | ✅ |
| RES-09 | Opción "solo consumo" para agrupar items como "POR CONSUMO" en SUNAT | P1 | ✅ |
| RES-10 | Descuento automático de stock (permite negativo) | P0 | ✅ |
| RES-11 | Bloqueo de mesas (5 min timeout) para evitar apertura simultánea | P1 | ✅ |
| RES-12 | Movimiento de pedidos entre mesas | P2 | ✅ |
| RES-13 | Polling de pedidos activos cada 10s | P1 | ✅ |
| RES-14 | Polling de bloqueos de mesas cada 10s | P1 | ✅ |
| RES-15 | Cierre de pedido (COMPLETED) desde KDS | P2 | ✅ |
| RES-16 | Venta de productos compuestos con descuento de stock de componentes | P2 | ✅ |
| RES-17 | **Dividir Cuenta**: repartir el pedido en 2+ comprobantes (NV/Boleta/Factura) por cantidades, con cliente, método de pago y solo consumo por división; items pagados se marcan "Pagado" y desaparecen del KDS | P1 | ✅ |

### 3.3 Cocina (KDS)

**Propósito:** Pantalla de cocina (Kitchen Display System).

| ID | Requisito | Prioridad | Estado |
|----|-----------|-----------|--------|
| KDS-01 | Vista separada con filtro por destino (cocina, cocina2, bar) | P0 | ✅ |
| KDS-02 | Polling de pedidos cada 5s | P0 | ✅ |
| KDS-03 | Secciones: Pendientes (OPEN), Enviados (SENT_TO_KITCHEN), Listos (READY) | P0 | ✅ |
| KDS-04 | Marcar pedido como LISTO | P0 | ✅ |
| KDS-05 | Marcar pedido como ENTREGADO | P0 | ✅ |
| KDS-06 | Separación visual entre pedidos de mozo y kiosko | P0 | ✅ |
| KDS-07 | Alerta sonora cuando llega un nuevo pedido | P1 | ✅ |
| KDS-08 | Tiempo transcurrido desde el pedido con aviso si >15 min | P1 | ✅ |

### 3.4 Kiosko de Autopedidos

**Propósito:** Pantalla táctil para que clientes hagan pedidos sin intervención del mesero.

| ID | Requisito | Prioridad | Estado |
|----|-----------|-----------|--------|
| KIO-01 | Catálogo de productos con categorías | P0 | ✅ |
| KIO-02 | Carrito de compras con cantidades | P0 | ✅ |
| KIO-03 | Modal de producto con cantidad, notas y elementos auxiliares | P0 | ✅ |
| KIO-04 | Teclado virtual para búsqueda y notas | P1 | ✅ |
| KIO-05 | Confirmación de pedido con número A-NNN | P0 | ✅ |
| KIO-06 | Mesa virtual kiosko (is_for_kiosko=true, no visible en floor plan) | P0 | ✅ |
| KIO-07 | Numeración secuencial A-001 que se reinicia al abrir/cerrar caja | P0 | ✅ |
| KIO-08 | Impresión de ticket de autopedido | P1 | ✅ |

### 3.5 Caja Registradora

**Propósito:** Control de apertura, operación y cierre de caja.

| ID | Requisito | Prioridad | Estado |
|----|-----------|-----------|--------|
| CAJ-01 | Apertura de caja con monto inicial y referencia | P0 | ✅ |
| CAJ-02 | Cierre de caja con validación de pedidos abiertos (restaurante + kiosko) | P0 | ✅ |
| CAJ-03 | Resumen por tipo de documento (Facturas, Boletas, NV) | P0 | ✅ |
| CAJ-04 | Resumen por método de pago (Efectivo, Tarjeta, Yape, Plin, Otro) | P0 | ✅ |
| CAJ-05 | Resumen por categoría de productos | P1 | ✅ |
| CAJ-06 | Reporte de productos vendidos con cantidades | P1 | ✅ |
| CAJ-07 | Reporte de líneas eliminadas (items cancelados en cocina) | P1 | ✅ |
| CAJ-08 | Pedidos kiosko en resumen de caja | P2 | ✅ |
| CAJ-09 | Exportar resumen a PDF A4 | P0 | ✅ |
| CAJ-10 | Exportar resumen a Ticket 80mm | P0 | ✅ |
| CAJ-11 | Imprimir resumen directo en impresora térmica Caja | P1 | ✅ |
| CAJ-12 | Mensaje de cierre específico: mesas abiertas vs pedidos kiosko | P2 | ✅ |
| CAJ-13 | Sistema mono-empresa (siempre usa empresa principal) | P0 | ✅ |

### 3.6 Productos

**Propósito:** Catálogo de productos con gestión de inventario.

| ID | Requisito | Prioridad | Estado |
|----|-----------|-----------|--------|
| PRO-01 | CRUD de productos (código, descripción, precio, stock, IGV) | P0 | ✅ |
| PRO-02 | Categorías de productos | P0 | ✅ |
| PRO-03 | Código de barras | P1 | ✅ |
| PRO-04 | Código SUNAT con buscador integrado | P1 | ✅ |
| PRO-05 | Destino KDS (cocina, cocina2, bar) | P1 | ✅ |
| PRO-06 | Precio de compra para valorización de inventario | P1 | ✅ |
| PRO-07 | Productos compuestos (combos/promociones con componentes) | P1 | ✅ |
| PRO-08 | Importación de productos desde Excel | P2 | ✅ |
| PRO-09 | Exportación de productos a Excel | P2 | ✅ |
| PRO-10 | Duplicar producto | P2 | ✅ |
| PRO-11 | Filtro por tipo (simples/compuestos/todos) | P2 | ✅ |
| PRO-12 | Stock negativo permitido (reposición vía compras) | P0 | ✅ |
| PRO-13 | Productos con precio 0 válidos (componentes de menú) | P0 | ✅ |
| PRO-14 | Elementos auxiliares para personalizar pedidos | P2 | ✅ |

### 3.7 Productos Compuestos

**Propósito:** Productos formados por otros productos (combos/promociones).

| ID | Requisito | Prioridad | Estado |
|----|-----------|-----------|--------|
| CMP-01 | Crear producto compuesto con N componentes y cantidades | P1 | ✅ |
| CMP-02 | Precio del compuesto manual (no calculado de componentes) | P1 | ✅ |
| CMP-03 | Al vender compuesto, descuenta stock de cada componente | P1 | ✅ |
| CMP-04 | Compuesto no puede ser componente de otro compuesto | P1 | ✅ |
| CMP-05 | Visibles en POS, Restaurante y Kiosko | P1 | ✅ |
| CMP-06 | Stock del compuesto = 0 (no maneja stock propio) | P1 | ✅ |

### 3.8 Comprobantes (Facturación)

**Propósito:** Gestión de facturas, boletas y notas de venta.

| ID | Requisito | Prioridad | Estado |
|----|-----------|-----------|--------|
| FAC-01 | Crear factura (01), boleta (03) y nota de venta (NV) | P0 | ✅ |
| FAC-02 | Nota de crédito (07) sobre factura y boleta | P1 | ✅ |
| FAC-03 | Nota de débito (08) sobre factura y boleta | P1 | ✅ |
| FAC-04 | Envío a SUNAT: Factura→SOAP, Boleta→Resumen Diario | P0 | ✅ |
| FAC-05 | Baja de comprobante (comunicación de baja / resumen diario) | P1 | ✅ |
| FAC-06 | PDF A4 con código QR SUNAT | P0 | ✅ |
| FAC-07 | Ticket 80mm | P0 | ✅ |
| FAC-08 | Descarga de XML y CDR | P1 | ✅ |
| FAC-09 | NC/ND de boleta por Resumen Diario | P1 | ✅ |

### 3.9 Compras y Stock

**Propósito:** Gestión de compras a proveedores y control de inventario.

| ID | Requisito | Prioridad | Estado |
|----|-----------|-----------|--------|
| COM-01 | Registro de compras con incremento de stock | P0 | ✅ |
| COM-02 | Actualización automática de precio_compra al registrar compra | P1 | ✅ |
| COM-03 | Salidas de almacén (consumo cocina, merma, degustación) | P2 | ✅ |
| COM-04 | Reporte de inventario HTML/Excel/PDF con filtro por categoría | P1 | ✅ |

### 3.10 Reportes

**Propósito:** Reportes y resúmenes del sistema.

| ID | Requisito | Prioridad | Estado |
|----|-----------|-----------|--------|
| REP-01 | Dashboard con ventas del mes, crecimiento, top productos | P0 | ✅ |
| REP-02 | Reporte de inventario con valor de venta y costo | P1 | ✅ |
| REP-03 | Exportación Excel y PDF de reportes | P1 | ✅ |
| REP-04 | Resumen diario de boletas (SUNAT) | P0 | ✅ |

### 3.11 Impresión

**Propósito:** Impresión térmica de tickets y comandas.

| ID | Requisito | Prioridad | Estado |
|----|-----------|-----------|--------|
| IMP-01 | 8 slots de impresora: cocina-1, cocina-2, bar-1, precuenta, precuenta2, precuenta3, caja, autopedido | P0 | ✅ |
| IMP-02 | Impresión de comandas agrupadas por destino (cocina, cocina2, bar) | P0 | ✅ |
| IMP-03 | Header de comanda muestra destino correcto (BAR, COCINA, COCINA 2) | P0 | ✅ |
| IMP-04 | Notificación de anulación de items en cocina | P1 | ✅ |
| IMP-05 | Precuenta con IGV dinámico | P0 | ✅ |
| IMP-06 | Ticket de cierre de caja completo (documentos, métodos, productos, líneas eliminadas) | P1 | ✅ |
| IMP-07 | Print Server Node.js con reintentos automáticos | P0 | ✅ |
| IMP-08 | Apertura de cajón de efectivo vía ESC/POS | P1 | ✅ |

### 3.12 Asistencia / Control de Personal

**Propósito:** Control de entrada/salida del personal mediante marcación por DNI, con opción de verificación facial por webcam.

| ID | Requisito | Prioridad | Estado |
|----|-----------|-----------|--------|
| ASI-01 | Marcador kiosco (`/marcar`) con pantalla de inicio y botón grande | P0 | ✅ |
| ASI-02 | Registro por DNI (modo `dni`) | P0 | ✅ |
| ASI-03 | Registro por webcam identificando el rostro (modo `webcam`) | P0 | ✅ |
| ASI-04 | Registro con DNI + verificación facial (modo `dni_webcam`) | P0 | ✅ |
| ASI-05 | Horarios corrido y dividido (2 entradas) con tolerancias | P0 | ✅ |
| ASI-06 | Reglas de tardanza con descuentos por tramos (10-60 min), fijo o % del sueldo | P0 | ✅ |
| ASI-07 | Falta por tardanza (umbral) y falta grave con suspensión por N graves consecutivas | P0 | ✅ |
| ASI-08 | Verificación facial con `face-api.js` y registro de foto por marcación | P0 | ✅ |
| ASI-09 | Reportes diario/semanal/mensual por trabajador (faltas, tardanzas, descuentos) | P0 | ✅ |
| ASI-10 | Exportar reportes a PDF y Excel | P1 | ✅ |
| ASI-11 | Historial de marcaciones con foto y estado de verificación | P0 | ✅ |
| ASI-12 | Tiempo de éxito configurable y temporizador de inactividad en el marcador | P1 | ✅ |

---

## 4. Reglas de Negocio

### Stock
| Regla | Descripción |
|-------|-------------|
| **STK-01** | Stock negativo permitido — se repone posteriormente con compras |
| **STK-02** | Productos con precio 0 son válidos (componentes de menú) |
| **STK-03** | Productos compuestos tienen stock = 0 (descuentan componentes) |
| **STK-04** | Al vender producto compuesto: `stock_componente -= cantidad × cantidad_vendida` |

### Empresa
| Regla | Descripción |
|-------|-------------|
| **EMP-01** | Sistema mono-empresa — una sola empresa principal |
| **EMP-02** | IGV configurable: General (18%) o Restaurante (10.5%) |

### Pedidos
| Regla | Descripción |
|-------|-------------|
| **ORD-01** | Número de pedido restaurante: `P-YYYYMMDD-NNNN` (secuencial diario) |
| **ORD-02** | Número de pedido kiosko: `A-NNN` (secuencial por caja abierta) |
| **ORD-03** | Pedido kiosko requiere caja abierta para crearse |
| **ORD-04** | Eliminar item PENDING → borrado físico; SENT/READY/DELIVERED → CANCELLED con password admin |
| **ORD-05** | Pedido con solo items CANCELLED se marca como CANCELADO automáticamente |
| **ORD-06** | Dividir cuenta: no exceder cantidades disponibles; items pagados se marcan `paid_invoice_id`; remanente queda pendiente para "Cobrar"; si no quedan items sin pagar → COMPLETED + mesa AVAILABLE |

### Caja
| Regla | Descripción |
|-------|-------------|
| **CAJ-01** | Solo una caja abierta por empresa a la vez |
| **CAJ-02** | No se puede cerrar caja con pedidos abiertos (restaurante o kiosko) |
| **CAJ-03** | Apertura de caja sin filtrar por usuario |

### SUNAT
| Regla | Descripción |
|-------|-------------|
| **SUN-01** | Boletas (03) → Resumen Diario, Facturas (01) → SOAP individual |
| **SUN-02** | NC/ND de boleta → Resumen Diario |
| **SUN-03** | NV no se envía a SUNAT |
| **SUN-04** | Factura requiere RUC 11 dígitos, Boleta acepta DNI o RUC |
| **SUN-05** | Cliente por defecto: "CLIENTES VARIOS" (DNI 88888888) |

### Impresión
| Regla | Descripción |
|-------|-------------|
| **PRT-01** | Encoding CP850 para impresoras térmicas |
| **PRT-02** | No usar emojis en tickets (CP850 no los soporta) |
| **PRT-03** | Cola de impresión con 3 reintentos máximos |

---

## 5. Flujos Principales

### 5.1 Flujo de Venta POS
```
1. Cajero abre caja
2. Selecciona productos → carrito
3. Selecciona cliente (opcional)
4. Elige tipo documento (Boleta/Factura/NV)
5. Elige método de pago
6. Click COBRAR → genera Invoice + descuenta stock
7. Modal de éxito: opción enviar SUNAT + imprimir
```

### 5.2 Flujo de Pedido Restaurante
```
1. Mozo selecciona mesa → abre pedido (P-YYYYMMDD-NNNN)
2. Agrega productos con cantidad y notas
3. Si hay error: modifica cantidad o elimina items
4. Click "Enviar a Cocina" → items SENT + ticket comanda
5. Cocina ve items en KDS → prepara → marca LISTO
6. Mozo entrega → marca ENTREGADO
7. Click "Cobrar" → genera Invoice + descuenta stock → mesa AVAILABLE
```

### 5.3 Flujo de Kiosko
```
1. Cliente navega catálogo → agrega productos
2. Click "Confirmar Pedido" → PENDING_PAYMENT + ticket
3. Cajero ve en "Pedidos Kiosko Pendientes"
4. Click "Enviar a Cocina" → SENT_TO_KITCHEN
5. Click "Cobrar" → genera Invoice
```

### 5.4 Flujo de Cierre de Caja
```
1. Cajero click "Cerrar Caja"
2. Sistema verifica: ¿hay pedidos abiertos? (mesas + kiosko)
3. Si hay → mensaje específico: "3 mesas abiertas" y/o "2 kiosko pendientes"
4. Si no hay → calcula totales por método de pago y tipo documento
5. Cierra caja → muestra resumen → opción PDF/Ticket/Imprimir
```

### 5.5 Flujo de Dividir Cuenta
```
1. No-mozo abre "Dividir" en un pedido enviado a cocina
2. Asigna cantidades por item a 2+ divisiones (cliente, tipo doc, método pago, solo consumo por división)
3. Cliente por defecto: "Clientes Varios" (DNI 88888888); permite crear cliente nuevo por división
4. Valida: no exceder la cantidad disponible por item
5. Confirmar → POST /restaurant/orders/{id}/split-charge
6. Por división: crea Invoice (NV/Boleta/Factura) + marca items pagados (paid_invoice_id)
7. Si no quedan items sin pagar → pedido COMPLETED + mesa AVAILABLE
8. Si quedan → remanente se cobra con el botón "Cobrar"
```

---

## 6. Modelo de Datos (Alto Nivel)

```
companies (1)
├── users (N)
├── customers (N)
├── products (N)
│   └── product_components (N) ← productos compuestos
├── categories (N)
├── invoices (N)
│   └── invoice_items (N)
├── series (N)
├── floors (N)
│   └── restaurant_tables (N)
│       └── restaurant_orders (N)
│           └── restaurant_order_items (N)
├── cashregisters (N)
├── purchases (N)
│   └── purchase_items (N)
├── suppliers (N)
└── printers (N)
    └── print_jobs (N)
```

**Campos clave por tabla:**

| Tabla | Campos importantes |
|-------|-------------------|
| `companies` | ruc, razon_social, soap_username, soap_password, tax_type, igv_percent |
| `products` | codigo, descripcion, precio, precio_compra, stock, is_composite, kds_destination |
| `restaurant_orders` | order_number, status, order_type (mozo/kiosko), table_id |
| `restaurant_order_items` | product_name, quantity, kitchen_status, cancelled_from/at/by, paid_invoice_id |
| `invoices` | tipo_documento, full_number, metodo_pago, sunat_estado |
| `invoice_items` | descripcion, cantidad, detalle_consumo (JSON) |
| `cashregisters` | estado, monto_apertura/cierre, ventas_efectivo/tarjeta/yape/plin |

---

## 7. Stack Tecnológico

| Componente | Tecnología |
|-----------|-----------|
| Backend | PHP 8.2+ / Laravel 13.x |
| Base de Datos | MySQL 8.0+ |
| Facturación SUNAT | Greenter 5.x |
| PDF | mpdf, Greenter HtmlToPdf |
| Excel | PhpSpreadsheet |
| Frontend | Blade + AdminLTE + Chart.js |
| Print Server | Node.js 18+ / Express (localhost:9100) |
| Impresión Térmica | ESC/POS vía raw-print.ps1 |

---

## 8. Criterios de Aceptación

### Funcionalidad
- [ ] El sistema permite abrir y cerrar caja diariamente
- [ ] El POS permite vender con Boleta, Factura o NV
- [ ] El restaurante permite gestionar mesas, pedidos y envío a cocina
- [ ] El KDS muestra pedidos en tiempo real con polling
- [ ] Los productos compuestos descuentan stock de sus componentes
- [ ] El cierre de caja cuadra los totales con los comprobantes emitidos

### Rendimiento
- [ ] Polling del KDS no afecta el rendimiento general
- [ ] Cierre de caja con 100+ comprobantes en < 3 segundos
- [ ] Impresión de comandas en < 2 segundos desde el envío

### Seguridad
- [ ] Passwords de certificado y SOAP cifrados en BD
- [ ] Rutas protegidas con middleware auth + admin según corresponda
- [ ] Sin exposición de stack traces en producción

---

## 9. No Incluido (Out of Scope)

| Ítem | Razón |
|------|-------|
| Multi-empresa | Sistema diseñado para una sola empresa |
| Delivery / Take away | Solo restaurante en sitio |
| Integración con POS hardware | Solo impresión térmica |
| App móvil nativa | Solo web responsive |
| Contabilidad completa | Solo facturación y caja básica |
| Control de acceso biométrico | Login con usuario/contraseña |
| Reportes contables avanzados | Solo resumen de caja e inventario |

---

## 10. Control de Versiones

| Versión | Fecha | Cambios |
|---------|-------|---------|
| 1.0 | Junio 2026 | Versión inicial: POS, Restaurante, KDS, Kiosko, Caja |
| 2.0 | Julio 2026 | Productos compuestos, precio_compra, reporte inventario, POS multi-venta, fix método de pago Yape/Plin, ticket caja completo |
| 2.1 | Agosto 2026 | **Dividir Cuenta** (paid_invoice_id + split-charge), permisos SUNAT (cajero con envío), apertura de cajón en POS (manual + automática en efectivo), IGV dinámico en precuenta, 8º slot de impresora (autopedido) |
