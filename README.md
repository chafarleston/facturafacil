# FacturaFácil — Sistema de Facturación Electrónica y Restaurante

Sistema integral de facturación electrónica SUNAT (Perú) con módulo completo de restaurante, POS, impresión térmica ESC/POS, gestión multi-rol y caja registradora.

---

## Módulos del Sistema

### Facturación Electrónica SUNAT
- Emisión de **Facturas** (01), **Boletas** (03), **Notas de Venta** (NV), **Notas de Crédito** (07), **Notas de Débito** (08)
- Envío automático a SUNAT vía Greenter (propio) o **API externa pro51**
- Firma digital con certificado .p12
- PDF en formato A4 y Ticket 80mm con código QR
- Descarga de XML firmado y CDR
- Series configurables por tipo de documento
- **Integración pro51**: modo híbrido que permite elegir entre facturación propia (Greenter) o externa (pro51) por empresa. Incluye sincronización de productos, series y cola de reintentos.

### POS (Punto de Venta)
- Interfaz simplificada para ventas rápidas
- Búsqueda de productos por nombre o código de barras (detección automática: letras → descripción, números → código de barras)
- Selección de cliente con búsqueda y creación rápida
- Múltiples métodos de pago: Efectivo, Tarjeta, Yape, Plin, Transferencia, Mixto
- Control de caja (apertura/cierre con arqueo)
- Apertura de cajón de efectivo desde el POS y Restaurante

### Restaurante
- Gestión de **Pisos** y **Mesas** con estado visual (Disponible/Ocupada)
- Pedidos con productos, cantidades, notas y precios
- **Búsqueda de productos** en tiempo real (letras → descripción, números → código)
- Envío a cocina (modo **KDS** en pantalla o **Impresión 80mm** a impresora térmica)
- **KDS (Kitchen Display System)**: pantalla en tiempo real con alertas sonoras al recibir nuevos pedidos, colores por estado
- Precuenta con selección de impresora (Precuenta 1, 2 o 3)
- Cobro con **cliente por defecto** (Cliente Varios DNI 88888888) y **confirmación de impresión**
- **Mover pedido** entre mesas
- Anulación de productos con autorización de administrador para items enviados a cocina
- Notas por producto y por pedido

### Caja Registradora
- Apertura y cierre con **Nombre de referencia** (ej: "25-05-mañana", "25-05-tarde")
- Resumen de ventas por tipo de documento y método de pago en formato tabular
- Reporte de **líneas eliminadas** con cantidad, producto, usuario que canceló y hora
- Ticket 80mm y PDF A4 con formato columnas (Cant. | Producto | Precio)
- **Bloqueo de cierre** si hay mesas abiertas en restaurante
- Dashboard con **resumen mensual** (ventas del mes vs mes anterior)

### Impresión Térmica ESC/POS
- **Arquitectura híbrida**: el servidor Laravel encola los trabajos, los envía vía HTTP al Print Server local
- **Print Server Node.js** local en cada máquina cliente (Windows/Linux/Mac)
- 8 slots fijos de impresora: Cocina 1, Cocina 2, Bar 1, Precuenta 1/2/3, Caja
- Soporte para impresoras **locales** (USB/paralelo vía raw-print.ps1) y **red** (socket TCP puerto 9100)
- Encoding CP850 con caracteres ñ, tildes, mayúsculas
- **Cola de impresión** con reintentos automáticos (hasta 3 intentos)
- **Auto-reinicio** del servidor si se detiene (loop en start.bat)
- **Quick Edit Mode deshabilitado** — la ventana no se congela al hacer clic
- **start-hidden.vbs** — servidor oculto en segundo plano (sin ventana visible)
- Comando de apertura de cajón de efectivo

### Roles y Permisos
- Roles: **Administrador**, **Cajero**, **Mozo**, **Usuario**
- Permisos granulares: Abrir Caja y Cerrar Caja como permisos separados
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

### Consumo Interno (Salidas de Stock)
- Registro de consumos de cocina sin generar venta (mermas, degustaciones, consumo interno)
- Soporte para cantidades fraccionarias (gramos, kilogramos)
- **Stock convertible a decimal**: los productos manejan stock con 4 decimales
- Historial completo con stock antes/después por cada producto
- Anulación con reincorporación automática al stock
- Visualización de registros anulados en rojo (soft delete)

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

**Reintentos automáticos**: el comando `php artisan print:process-queue` se ejecuta cada minuto vía Tarea Programada de Windows (`FacturaFacilScheduler`) para reintentar trabajos fallidos (hasta 3 intentos).

---

## Requisitos

- **PHP** 8.2+
- **MySQL** 8.0+ / MariaDB 10.4+
- **Composer**
- **Node.js** 18+ (para Print Server)
- Extensiones PHP: `openssl`, `xml`, `zip`, `mbstring`, `pdo_mysql`, `curl`

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

Se crea automáticamente al ejecutar el comando de activación. Ejecuta `php artisan schedule:run` cada minuto para procesar la cola de impresión.

---

## Configuración

### Impresoras

Los slots de impresora se configuran en `/printers`:
- **Cocina 1** (cocina-1) — Cocina principal
- **Cocina 2** (cocina-2) — Cocina secundaria
- **Bar 1** (bar-1) — Barra
- **Precuenta / Precuenta 2 / Precuenta 3** — Precuentas
- **Caja** — Cajón registrador + apertura de efectivo

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
- **Cajero**: POS, facturación, caja (abrir + cerrar como permisos separados)
- **Mozo**: restaurante, cocina
- **Usuario**: POS, consultas, sin gestión de caja

---

## Uso

### Restaurante
1. `/restaurant` — Vista principal con pisos y mesas
2. Seleccionar mesa → se abre el modal de pedido
3. Agregar productos usando el **buscador** en el encabezado o filtro por categoría
4. Enviar a cocina (modo KDS o impresión)
5. Precuenta → seleccionar impresora (Precuenta 1/2/3)
6. Cobrar → se selecciona automáticamente "Clientes Varios", confirma si desea imprimir
7. Mover pedido a otra mesa si es necesario

### POS
1. `/pos` — Punto de venta
2. Seleccionar categoría o buscar producto por nombre/código
3. Agregar items al carrito
4. Seleccionar cliente y método de pago
5. Cobrar → emite comprobante, envía a SUNAT

### KDS (Cocina)
- `/restaurant/kitchen` — Pantalla de cocina, actualiza automáticamente cada 5s
- Botones: Marcar Listo / Entregar
- Alerta sonora al recibir nuevos pedidos

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
