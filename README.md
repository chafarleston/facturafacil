# FacturaFácil — Sistema de Facturación Electrónica y Restaurante

Sistema integral de facturación electrónica SUNAT (Perú) con módulo completo de restaurante, POS, impresión térmica ESC/POS, gestión multi-rol y caja registradora.

---

## Módulos del Sistema

### Facturación Electrónica SUNAT
- Emisión de **Facturas** (01), **Boletas** (03), **Notas de Venta** (NV), **Notas de Crédito** (07), **Notas de Débito** (08)
- Envío a SUNAT por tipo de documento: **Facturas** → Greenter (BillSender); **Boletas** → Resumen Diario (`SummaryService`); **NV** no se envían a SUNAT
- Firma digital con certificado **PEM-first** (busca `{ruc}_certificate.pem`; fallback a `.p12/.pfx` con contraseña)
- PDF en formato A4 y Ticket 80mm con código QR (generados bajo demanda)
- Descarga de XML firmado y CDR
- Series configurables por tipo de documento
- **Integración pro51**: modo híbrido que permite elegir entre facturación propia (Greenter) o externa (pro51) por empresa. Incluye sincronización de productos, series y cola de reintentos.

### POS (Punto de Venta)
- Interfaz simplificada para ventas rápidas
- Búsqueda de productos por nombre o código de barras (detección automática: letras → descripción, números → código de barras)
- Selección de cliente con búsqueda y creación rápida
- Múltiples métodos de pago: Efectivo, Tarjeta, Yape, Plin, Transferencia, Mixto
- Control de caja (apertura/cierre con arqueo)
- Apertura de cajón de efectivo desde el POS y Restaurante (botón manual) y **apertura automática al cobrar en efectivo**

### Restaurante
- Gestión de **Pisos** y **Mesas** con estado visual (Disponible/Ocupada)
- Pedidos con productos, cantidades, notas, precios y **elementos auxiliares** (chips tipo "Mayonesa", "Kétchup")
- **Búsqueda de productos** en tiempo real (solo por descripción/nombre; la búsqueda por código y código de barras existe en el POS)
- Envío a cocina (modo **KDS** en pantalla o **Impresión 80mm** a impresora térmica)
- **KDS (Kitchen Display System)**: pantalla en tiempo real con alertas sonoras al recibir nuevos pedidos, colores por estado y secciones MOZO / KIOSKO
- Precuenta con selección de impresora (Precuenta 1, 2 o 3) e **IGV dinámico** según empresa
- **Dividir Cuenta**: reparte el pedido en 2+ comprobantes (NV/Boleta/Factura) por cantidades, cada división con su cliente, método de pago y solo consumo; los items pagados se marcan "Pagado" y desaparecen del KDS
- **Kiosko / Autoservicio**: pedidos por pantalla táctil (`/autopedido`), numeración A-XXX ligada a la caja abierta, estados Pendiente/En Cocina y cobro en el cajero
- Cobro con **cliente por defecto** (Cliente Varios DNI 88888888) y **confirmación de impresión**
- **Solo consumo**: cobrar todo el pedido como "POR CONSUMO" con desglose de productos
- **Mover pedido** entre mesas
- Anulación/eliminación de productos y pedidos con **autorización por contraseña** (usuario autenticado con permiso `authorize_cancel_orders` — admin o cajero, cada uno con su propia contraseña) para items ya enviados a cocina
- **Login del mozo**: el rol `mozo` se redirige directo a `/restaurant`; si no hay caja abierta se muestra el modal **"Caja no aperturada"** (debe aperturarla el Administrador o el Cajero; botones Ir a Caja / Reintentar / Entendido)
- Notas por producto y por pedido

### Caja Registradora
- Apertura y cierre con **Nombre de referencia** (ej: "25-05-mañana", "25-05-tarde")
- Resumen de ventas por tipo de documento y método de pago en formato tabular
- Reporte de **líneas eliminadas** con cantidad, producto, usuario que canceló y hora
- Ticket 80mm y PDF A4 con formato columnas (Cant. | Producto | Precio)
- **Bloqueo de cierre** si hay mesas abiertas en restaurante
- El **cajero abre caja pero NO la cierra** (cierre exclusivo con permiso `close_cashregister`; si no lo tiene, modal "Solo el Administrador puede ejecutar estas tareas")
- El **historial de cajas** (cajas anteriores) solo es visible con permiso `view_cashregister_history` (por defecto solo `admin`)
- **Ingresos y Gastos** (`/cash-movements`): registrar ingresos y egresos de caja (motivo libre) ligados a la caja abierta; **requiere caja abierta**; se concilian en el cierre (`saldo esperado` y `diferencia`) y se muestran en el resumen web/PDF/ticket
- **Configuración de Reporte** (`/cash-report-settings`): elegir qué secciones (Lista de Comprobantes, Productos Vendidos, Reporte de Líneas Eliminadas) se imprimen en los reportes **A4/80mm**; el reporte web siempre muestra todo
- Dashboard con **resumen mensual** (ventas del mes vs mes anterior)

### Impresión Térmica ESC/POS
- **Arquitectura híbrida**: el servidor Laravel encola los trabajos, los envía vía HTTP al Print Server local
- **Print Server Node.js** local en cada máquina cliente (Windows/Linux/Mac)
- 8 slots fijos de impresora: Cocina 1, Cocina 2, Bar 1, Precuenta 1/2/3, Caja, Auto Pedido (kiosko)
- Soporte para impresoras **locales** (USB/paralelo vía raw-print.ps1) y **red** (socket TCP puerto 9100)
- Encoding CP850 con caracteres ñ, tildes, mayúsculas
- **Cola de impresión** con reintentos automáticos (hasta 3 intentos)
- **Auto-reinicio** del servidor si se detiene (loop en start.bat)
- **Quick Edit Mode deshabilitado** — la ventana no se congela al hacer clic
- **start-hidden.vbs** — servidor oculto en segundo plano (sin ventana visible)
- Comando de apertura de cajón de efectivo

### Roles y Permisos
- Roles: **Administrador**, **Cajero**, **Mozo**, **Usuario** (`superadmin` es un valor reservado en la lógica, no un rol de la BD)
- Permisos granulares: Abrir Caja y Cerrar Caja como permisos separados; **Enviar a SUNAT** para cajero/admin
- Permisos gestionables por rol (Roles/Permisos): `view_cashregister_history` (historial de cajas, solo admin por defecto), `authorize_cancel_orders` (autorizar anulaciones en cocina con la propia contraseña; admin y cajero por defecto) y `view/create/edit/delete_products` + `view/create/edit/delete_categories` (el cajero gestiona productos y categorías por defecto)
- Control de acceso a funcionalidades del restaurante (Cobrar/Anular restringido a no-mozos)
- **Auto-check de permisos** al seleccionar rol principal en creación de usuarios

### Gestión de Empresas
- Soporte multi-empresa con series separadas
- Configuración de **IGV**: General (18%) o Reducido Restaurante (10.5%), ambos porcentajes editables
- Certificado digital por empresa
- Datos SUNAT: tipo contribuyente, ubigeo, etc.
- Logotipo personalizado

### Compras
- Búsqueda de productos en **tiempo real** al agregar items (letras → descripción, números → código)
- Gestión de proveedores
- Cada compra actualiza **stock** y **precio de compra** del producto

### Productos
- Productos **simples** y **compuestos** (un compuesto descuenta stock de sus componentes; no maneja stock propio)
- **Precio de compra** (`precio_compra`) para valorar inventario a costo
- **Reporte de Inventario** (`/products/inventory-report`) con totales a precio venta y costo, filtro por categoría y exportación Excel/PDF
- Importación/exportación de productos y duplicado con código secuencial automático

### Consumo Interno (Salidas de Stock)
- Registro de consumos de cocina sin generar venta (mermas, degustaciones, consumo interno)
- Soporte para cantidades fraccionarias (gramos, kilogramos)
- **Stock convertible a decimal**: los productos manejan stock con 4 decimales
- Historial completo con stock antes/después por cada producto
- Anulación con reincorporación automática al stock
- Visualización de registros anulados en rojo (soft delete)

### Asistencia / Control de Personal
- Marcación en kiosco (`/marcar`) por **DNI** con cámara opcional (webcam)
- **3 modos de marcación** configurable: `dni`, `webcam` (verificación facial con `face-api.js`) y `dni_webcam`
- **Reglas de tardanza/faltas**: umbral de falta y de falta grave, suspensión por N graves consecutivas y descuentos por tramos (fijo o % del sueldo diario)
- Administración: Personal, Horarios (corrido/dividido), Reglas de Tardanza, Marcaciones y Reportes (diario/semanal/mensual en PDF/Excel)
- La cámara requiere **contexto seguro** (localhost, HTTPS o flag de Chrome); scripts `iniciar-marcador.bat` / `iniciar-marcador-kiosko.bat`

---

## Arquitectura de Impresión

```
Navegador (cliente)
  │
  ├── POST /restaurant/orders/{id}/send-to-kitchen
  │   └── Laravel: marca items como SENT, encola trabajo en DB
  │       └── PrintService::processQueue()
  │           └── HTTP POST → Print Server local (127.0.0.1:9100/print)
  │
  ├── Clic en "Caja" (abrir cajón)
  │   └── POST /pos/open-drawer → devuelve config
  │       └── fetch POST → localhost:9100/print (no-cors, form-urlencoded)
  │
  └── Clic en "Cobrar" → confirmación de impresión
      └── Sí → window.open /pos/print/{invoice}/80mm
```

**Print Server** (Node.js en `print-server-node/server.js`):
- Corre en la máquina local del cliente (Windows/Linux/Mac)
- Recibe datos ESC/POS en base64 vía REST API
- Envía a impresora local (raw-print.ps1) o a impresora de red (socket TCP)
- Endpoints: `GET /status`, `GET /printers`, `POST /print`, `POST /print-escpos-text`, `GET /open-drawer`
- **Auto-reinicio** en caso de fallo (loop infinito en start.bat)
- **Quick Edit Mode desactivado** para evitar congelamiento por clic
- **start-hidden.vbs** para ejecución en segundo plano sin ventana

**Reintentos automáticos**: el comando `php artisan print:process-queue` está programado cada minuto en `Kernel.php` (`everyMinute`). Se invoca ejecutando `php artisan schedule:run` vía `scheduler.vbs` o una tarea de Windows creada fuera del repo (`FacturaFacilScheduler`).

---

## Requisitos

- **PHP** 8.2+
- **MySQL** 8.0+ / MariaDB 10.4+ (entorno del cliente: MySQL 8.0.30 — Laragon)
- **Composer**
- **Node.js** 18+ (para Print Server)
- Extensiones PHP: `openssl`, `xml`, `zip`, `soap`, `intl`, `mbstring`, `pdo_mysql`, `curl`

---

## Instalación

```bash
# 1. Clonar
git clone <repo-url> facturafacil
cd facturafacil

# 2. Dependencias PHP
composer install

# 3. Configurar .env
cp .env.example .env
# Editar DB_DATABASE, DB_USERNAME, DB_PASSWORD
# Generar key
php artisan key:generate

# 4. Migrar y seedear
php artisan migrate
php artisan db:seed

# 5. Link storage
php artisan storage:link

# 6. Print Server (en cada máquina cliente)
cd print-server-node
npm install
```

### Instalación en Linux (VPS)

```bash
# Permisos de directorios
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Permisos para mpdf (generación de PDFs)
chmod -R 775 vendor/mpdf/mpdf/ttfontdata
# Si el error persiste, agregar en public/index.php:
# define('_MPDF_TTFONDTADATAPATH', base_path('storage/fonts/'));
mkdir -p storage/fonts
chmod -R 775 storage/fonts

# Cola de impresión (programar en crontab)
# Ejecutar: crontab -e
# Agregar: * * * * * cd /ruta/del/proyecto && php artisan schedule:run >> /dev/null 2>&1

# Queue worker (para jobs asíncronos)
# Ejecutar en segundo plano: nohup php artisan queue:work --queue=default --tries=3 --timeout=120 > storage/logs/queue.log 2>&1 &

# Optimización para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> **Nota**: El error `mkdir(): Permission denied` en `vendor/mpdf/mpdf/src/Cache.php` es común en servidores Linux nuevos. Se soluciona con los comandos `chmod` indicados arriba.

### Iniciar Print Server (Windows)

**Opción recomendada — oculto en segundo plano:**
```bash
start-hidden.vbs
```

**Opción con ventana visible (con autoreinicio):**
```bash
start.bat
```

**Opción con ventana minimizada:**
```bash
start-minimized.vbs
```

**Instalación definitiva (acceso directo + inicio automático):**
```bash
install.bat
```

### Tarea Programada (Windows)

El comando `php artisan schedule:run` se ejecuta cada minuto para procesar la cola de impresión. Puede invocarse con `scheduler.vbs` o creando una tarea de Windows (`schtasks /create`) que lo llame; el registro de la tarea se crea en el sistema (fuera del repo).

---

## Configuración

### Impresoras

Los slots de impresora se configuran en `/printers`:
- **Cocina 1** (cocina-1) — Cocina principal
- **Cocina 2** (cocina-2) — Cocina secundaria
- **Bar 1** (bar-1) — Barra
- **Precuenta / Precuenta 2 / Precuenta 3** — Precuentas
- **Caja** — Cajón registrador + apertura de efectivo
- **Auto Pedido** (autopedido) — Ticket del kiosko/autoservicio

Cada slot permite:
- Tipo: `local` (USB) o `network` (IP+puerto)
- Nombre de impresora Windows (para tipo local)
- IP y puerto (para tipo network, ej: `192.168.1.100:9100`)

### IGV Configurable

En `/companies/{id}/edit`:
- **General**: IGV 18% (por defecto)
- **Restaurante**: IGV 10.5% (Ley MYPE)
- Ambos porcentajes son editables

### Roles y Permisos

En `/roles` se gestionan los roles. Por defecto:
- **Administrador**: acceso completo
- **Cajero**: POS, facturación, **envío a SUNAT**, caja (**abrir, NO cerrar**), autoriza anulaciones de cocina con su contraseña, registra **ingresos y gastos** de caja y **gestiona productos/compuestos/inventario y categorías (crear, editar, eliminar)**
- **Mozo**: restaurante, cocina (sin cobrar ni anular); entra directo a `/restaurant` al iniciar sesión
- **Usuario**: POS, consultas; **sin comprobantes ni caja**

Los permisos `view_cashregister_history` (historial de cajas) y `authorize_cancel_orders` (anulaciones en cocina) se pueden **añadir/quitar por rol** desde Roles/Permisos.

---

## Uso

### Restaurante
1. `/restaurant` — Vista principal con pisos y mesas
2. Seleccionar mesa → se abre el modal de pedido
3. Agregar productos usando el **buscador** en el encabezado o filtro por categoría
4. Enviar a cocina (modo KDS o impresión)
5. Precuenta → seleccionar impresora (Precuenta 1/2/3)
6. Cobrar → se selecciona automáticamente "Clientes Varios", confirma si desea imprimir
7. **Dividir Cuenta** → reparte items en 2+ comprobantes (cantidades, cliente, método de pago y solo consumo por división)
8. Mover pedido a otra mesa si es necesario

### POS
1. `/pos` — Punto de venta
2. Seleccionar categoría o buscar producto por nombre/código
3. Agregar items al carrito
4. Seleccionar cliente y método de pago
5. Cobrar → emite comprobante, envía a SUNAT

### KDS (Cocina)
- `/restaurant/kitchen` — Pantalla de cocina, actualiza automáticamente cada 5s (**solo en Modo KDS**; en Modo Impresión 80mm el menú oculta los ítems KDS, la pantalla muestra "KDS INACTIVO" y no hace polling)
- Botones: Marcar Listo / Entregar (responden 400 en Modo Impresión)
- Alerta sonora al recibir nuevos pedidos
- Si la sesión expira, el polling redirige a `/login`

### Caja Registradora
1. `/cashregisters` — Abrir caja con "Nombre de referencia" (ej: "25-05-mañana")
2. Durante el turno se registran todas las ventas y anulaciones
3. Al cerrar: verifica que no haya mesas abiertas en el restaurante
4. Muestra resumen en web, ticket 80mm y PDF A4
5. Las **líneas eliminadas** muestran: cantidad, producto, usuario que canceló y hora

---

## Credenciales por Defecto

| Email | Contraseña | Rol |
|-------|-----------|-----|
| manager@example.com | adminpass | Administrador |
| Caja@gmail.com | 222938 | Cajero |
| mozo@gmail.com | mozo123 | Mozo |
| demo@example.com | password | Usuario |

---

## Comandos Útiles

```bash
# Cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Seeders específicos
php artisan db:seed --class=PrinterSeeder
php artisan db:seed --class=PermissionsSeeder
php artisan db:seed --class=UbigeoSeeder

# Cola de impresión
php artisan print:process-queue

# pro51 (facturación externa)
php artisan pro51:sync-products          # Sincronizar productos
php artisan pro51:retry-pending          # Reintentar comprobantes pendientes

# Programar tareas (Windows)
schtasks /run /tn "FacturaFacilScheduler"

# Ver rutas
php artisan route:list

# Optimizar
php artisan optimize

# Logs
tail -50 storage/logs/laravel.log
```

---

## Licencia

MIT
