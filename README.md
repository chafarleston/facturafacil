# FacturaFácil — Sistema de Facturación Electrónica y Restaurante

Sistema integral de facturación electrónica SUNAT (Perú) con módulo completo de restaurante, POS, impresión térmica ESC/POS y gestión multi-rol.

---

## Módulos del Sistema

### Facturación Electrónica SUNAT
- Emisión de **Facturas** (01), **Boletas** (03), **Notas de Venta** (NV), **Notas de Crédito** (07), **Notas de Débito** (08)
- Envío automático a SUNAT vía Greenter
- Firma digital con certificado .p12
- PDF en formato A4 y Ticket 80mm con código QR
- Descarga de XML firmado y CDR
- Series configurables por tipo de documento

### POS (Punto de Venta)
- Interfaz simplificada para ventas rápidas
- Búsqueda de productos por nombre o código de barras (detección automática: letras → descripción, números → código de barras)
- Selección de cliente con búsqueda y creación rápida
- Múltiples métodos de pago: Efectivo, Tarjeta, Yape, Plin, Transferencia, Mixto
- Control de caja (apertura/cierre con arqueo)
- Apertura de cajón de efectivo desde el POS

### Restaurante
- Gestión de **Pisos** y **Mesas** con estado visual (Disponible/Ocupada)
- Pedidos con productos, cantidades, notas y precios
- Envío a cocina (modo **KDS** en pantalla o **Impresión 80mm** a impresora térmica)
- **KDS (Kitchen Display System)**: pantalla en tiempo real con alertas sonoras al recibir nuevos pedidos, colores por estado (Pendiente/Enviado/Listo/Entregado)
- Precuenta con selección de impresora (Precuenta 1, 2 o 3)
- Cobro con selección de cliente, tipo de documento y método de pago
- **Mover pedido** entre mesas
- Anulación de productos con autorización de administrador para items enviados a cocina
- Notas por producto y por pedido

### Impresión Térmica ESC/POS
- **Arquitectura híbrida**: el servidor Laravel encola los trabajos, los envía vía HTTP al Print Server local
- **Print Server Node.js** local en cada máquina cliente (Windows/Linux/Mac)
- 8 slots fijos de impresora: Cocina 1, Cocina 2, Bar 1, Precuenta 1/2/3, Caja
- Soporte para impresoras **locales** (USB/paralelo vía raw-print.ps1) y **red** (socket TCP puerto 9100)
- Encoding CP850 con caracteres ñ, tildes, mayúsculas
- **Cola de impresión** con reintentos automáticos (hasta 3 intentos)
- Comando de apertura de cajón de efectivo

### Roles y Permisos
- Roles: **Administrador**, **Cajero**, **Mozo**, **Usuario**
- Permisos granulares por módulo (ver/crear/editar/eliminar por cada entidad)
- Control de acceso a funcionalidades del restaurante (Cobrar/Anular restrigido a no-mozos)
- Gestión de permisos desde el panel de administración

### Gestión de Empresas
- Soporte multi-empresa con series separadas
- Configuración de **IGV**: General (18%) o Reducido Restaurante (10.5%), ambos porcentajes editables
- Certificado digital por empresa
- Datos SUNAT: tipo contribuyente, ubigeo, etc.
- Logotipo personalizado

### Caja Registradora
- Apertura y cierre con montos
- Resumen de ventas por tipo de documento y método de pago
- Reporte de líneas eliminadas (productos anulados en cocina)
- Ticket 80mm y PDF A4

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
  └── Clicks en "Abrir Caja"
      └── POST /pos/open-drawer
          └── Laravel: envía comando ESC/POS → Print Server local
```

**Print Server** (Node.js en `print-server-node/server.js`):
- Corre en la máquina local del cliente (Windows/Linux/Mac)
- Recibe datos ESC/POS en base64 vía REST API
- Envía a impresora local (raw-print.ps1) o a impresora de red (socket TCP)
- Endpoints: `GET /status`, `GET /printers`, `POST /print`, `POST /print-escpos-text`

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

# 4. Generar key
php artisan key:generate

# 5. Migrar y seedear
php artisan migrate
php artisan db:seed

# 6. Link storage
php artisan storage:link

# 7. Print Server (en cada máquina cliente)
cd print-server-node
npm install
node server.js
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
- **Cajero**: POS, facturación, caja
- **Mozo**: restaurante, cocina
- **Usuario**: POS, consultas, sin gestión de caja

---

## Uso

### Restaurante
1. `/restaurant` — Vista principal con pisos y mesas
2. Seleccionar mesa → se abre el modal de pedido
3. Agregar productos desde la lista filtrada por categoría o búsqueda
4. Enviar a cocina (modo KDS o impresión)
5. Precuenta → seleccionar impresora
6. Cobrar → seleccionar cliente, documento, método de pago
7. Cerrar mesa

### POS
1. `/pos` — Punto de venta
2. Seleccionar categoría o buscar producto
3. Agregar items al carrito
4. Seleccionar cliente y método de pago
5. Cobrar → emite comprobante, envía a SUNAT

### KDS (Cocina)
- `/restaurant/kitchen` — Pantalla de cocina, actualiza automáticamente cada 5s
- Botones: Marcar Listo / Entregar
- Alerta sonora al recibir nuevos pedidos

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

# Programar tareas (Windows)
schtasks /run /tn "FacturaFacilScheduler"

# Ver rutas
php artisan route:list

# Optimizar
php artisan optimize
```

---

## Licencia

MIT
