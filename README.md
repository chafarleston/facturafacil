# FacturaFácil - Sistema de Facturación Electrónica SUNAT

<p align="center">
<a href="https://laravel.com" target="_blank">
<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="250" alt="Laravel Logo">
</a>
</p>

<p align="center">
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

---

## Descripción

**FacturaFácil** es un sistema de facturación electrónica desarrollado en Laravel para Perú, que permite emitir comprobantes de pago electrónicos согласно a las disposiciones de SUNAT (Superintendencia Nacional de Aduanas y Administración Tributaria).

El sistema está integrado con la librería **Greenter**, la herramienta oficial desarrollada por SUNAT para la generación y envío de comprobantes electrónicos.

---

## Características

### Comprobantes Electrónicos

| Tipo | Código SUNAT | Descripción |
|------|-------------|--------------|
| Factura | 01 | Comprobante de pago para clientes con RUC |
| Boleta | 03 | Comprobante de pago para clientes con DNI o RUC |
| Nota de Crédito | 07 | Rectificación de facturas/boletas |
| Nota de Débito | 08 | Cargo adicional a facturas/boletas |
| Nota de Venta | NV | Venta sin obligación electrónica |

### Funcionalidades Principales

- Emisión de **Facturas** (requiere RUC de 11 dígitos)
- Emisión de **Boletas** (acepta DNI o RUC)
- Notas de Crédito y Débito
- Notas de Venta para ventas locales
- Envío automático a SUNAT
- Generación de PDF (formato A4 y Ticket 80mm)
- Código QR para consulta en portal SUNAT
- Firma digital con certificado (.p12)
- Descarga de XML firmado
- Descarga de CDR (Constancia de Recepción)
- Gestión de inventario y stock
- Registro de caja (apertura/cierre)
- Módulo de compras a proveedores
- Dashboard con estadísticas
- Soporte multi-empresa

### Reglas de Validación

- **Facturas**: Solo aceptan clientes con RUC de 11 dígitos
- **Boletas**: Aceptan clientes con DNI (8 dígitos) o RUC (11 dígitos)
- **Notas de Venta**: Sin restricción de documento

---

## Requisitos del Servidor

### Requisitos Mínimos

- **PHP**: 8.2+
- **Base de datos**: MySQL 8.0+ o MariaDB 10.4+
- **Servidor web**: Apache o Nginx
- **Composer**: Última versión

### Extensiones PHP Requeridas

- `openssl`
- `xml`
- `zip`
- `mbstring`
- `pdo_mysql`
- `curl`

### Requisitos Adicionales

- Certificado digital (.p12) emitido por SUNAT
- Acceso a internet para comunicación con servicios SUNAT

---

## Instalación

### 1. Clonar el Proyecto

```bash
git clone <repository-url> facturafacil
cd facturafacil
```

### 2. Instalar Dependencias

```bash
composer install
```

### 3. Configurar variables de entorno

```bash
cp .env.example .env
```

Edita el archivo `.env` con tu configuración de base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=facturafacil
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### 4. Generar clave de aplicación

```bash
php artisan key:generate
```

### 5. Ejecutar migraciones

```bash
php artisan migrate
```

### 6. Ejecutar seeders (datos iniciales)

```bash
php artisan db:seed
```

Esto creará:
- Usuario administrador por defecto
- Series preconfiguradas (F001, B001, NV01, etc.)
- Empresa de demo

### 7. Crear enlace simbólico para storage

```bash
php artisan storage:link
```

### 8. Iniciar servidor de desarrollo

```bash
php artisan serve
```

Accede a: `http://localhost:8000`

---

## Configuración del Certificado Digital

### Paso 1: Obtener Certificado

1. Solicita tu certificado digital en SUNAT
2. Descarga el archivo `.p12` (contiene clave privada)
3. Anota la contraseña del certificado

### Paso 2: Subir Certificado

1. Crea el directorio: `storage/app/certificates/`
2. Copia tu archivo `.p12` al directorio
3. Actualiza la configuración en el panel de empresa

### Paso 3: Configurar en el Sistema

1. Inicia sesión como administrador
2. Ve a **Empresas** → Editar empresa
3. Ingresa:
   - Nombre del archivo certificado
   - Contraseña del certificado
   - Fecha de vencimiento (opcional)
4. Selecciona el entorno:
   - **Producción**: `https://e-factura.sunat.gob.pe`
   - **Beta**: `https://e-beta.sunat.gob.pe` (pruebas)

---

## Series Preconfiguradas

El sistema incluye las siguientes series por defecto:

| Serie | Tipo Documento | Descripción | Uso |
|-------|---------------|-------------|-----|
| F001 | 01 | Factura | Comprobante electrónico |
| B001 | 03 | Boleta | Comprobante electrónico |
| NV01 | NV | Nota de Venta | Venta sin elektroniks |
| FC01 | 07 | Nota de Crédito Factura | Rectificación |
| BC01 | 07 | Nota de Crédito Boleta | Rectificación |
| FD01 | 08 | Nota de Débito Factura | Cargo adicional |
| BD01 | 08 | Nota de Débito Boleta | Cargo adicional |

### Formato de Número

- **Serie**: 4 caracteres (ej: F001, B001)
- **Número**: 8 dígitos (ej: 00000001)
- **Formato completo**: `F001-00000001`

---

## Estructura del Proyecto

```
facturafacil/
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/    # Controladores
│   │   ├── Middleware/    # Middlewares
│   │   └── Requests/     # Validaciones
│   ├── Models/           # Modelos Eloquent
│   ├── Providers/        # Proveedores de servicios
│   ├── Services/        # Servicios externos
│   │   ├── GreenterService.php
│   │   ├── SunatService.php
│   │   ├── XmlSignerService.php
│   │   └── SunatQrService.php
│   └── CoreFacturalo/    # Nucleo de facturación
├── database/
│   ├── migrations/     # Migraciones
│   ├── seeders/        # Seeders
│   └── factories/       # Factories
├── resources/
│   ├── js/            # JavaScript
│   ├── css/           # Estilos CSS
│   └── views/         # Vistas Blade
├── routes/
│   ├── web.php       # Rutas web
│   ├── api.php      # Rutas API
│   └── auth.php    # Rutas de autenticación
├── storage/
│   ├── app/
│   │   └── certificates/  # Certificados digitales
│   └── sunat/              # XML y CDR de SUNAT
├── vendor/
├── artisan
├── composer.json
├── package.json
├── vite.config.js
└── README.md
```

---

## Modelos del Sistema

| Modelo | Tabla | Descripción |
|--------|------|-------------|
| Company | companies | Empresas/RUC |
| User | users | Usuarios del sistema |
| Customer | customers | Clientes |
| Product | products | Productos/Servicios |
| Category | categories | Categorías de productos |
| Invoice | invoices | Comprobantes emitidos |
| InvoiceItem | invoice_items | Ítems de comprobantes |
| Serie | series | Series documentales |
| Supplier | suppliers | Proveedores |
| Purchase | purchases | Compras |
| CashRegister | cash_registers | Registro de caja |

---

## Rutas Principales

| Ruta | Descripción |
|------|-------------|
| `/dashboard` | Panel principal |
| `/invoices` | Lista de comprobantes |
| `/invoices/create` | Nuevo comprobante |
| `/products` | Gestión de productos |
| `/customers` | Gestión de clientes |
| `/suppliers` | Gestión de proveedores |
| `/purchases` | Registro de compras |
| `/cashregisters` | Registro de caja |
| `/companies` | Gestión de empresas |
| `/series` | Series documentales |
| `/categories` | Categorías |

---

## Guía: Emitir Primera Factura

### 1. Configurar Empresa

1. Ve a **Empresas** → **Editar**
2. Ingresa los datos de tu empresa:
   - RUC
   - Razón Social
   - Nombre Comercial
   - Dirección
   - Departamento, Provincia, Distrito
3. Sube tu certificado digital y configura la contraseña

### 2. Crear Serie (si no existe)

1. Ve a **Series** → **Nueva Serie**
2. Selecciona tipo: Factura (01)
3. Ingresa serie: F001
4. Guarda

### 3. Crear Productos

1. Ve a **Productos** → **Nuevo Producto**
2. Ingresa:
   - Código interno
   - Descripción
   - Código SUNAT (opcional)
   - Unidad de medida
   - Precio
   - Tipo de afectación (Gravado/Exonerado/Inafecto)
3. Guarda

### 4. Emitir Factura

1. Ve a **Comprobantes** → **Nuevo Comprobante**
2. Selecciona tipo: Factura
3. Selecciona serie: F001
4. Ingresa datos del cliente:
   - Tipo: RUC (6)
   - Número: 11 dígitos
   - Nombre/Razón Social
   - Dirección
5. Agrega productos
6. Guarda

### 5. Enviar a SUNAT

1. En la vista del comprobante, click en **Enviar a SUNAT**
2. Espera la respuesta (puede tomar unos segundos)
3. Descarga el PDF y XMLfirmado

---

## Credenciales por Defecto

Después de ejecutar `php artisan db:seed`:

| Campo | Valor |
|------|-------|
| Email | admin@local.com |
| Contraseña | password |

**Nota**: Cambia esta contraseña después del primer inicio de sesión.

---

## Entornos de SUNAT

### Entorno de Producción

- **URL**: `https://e-factura.sunat.gob.pe`
- **Uso**: Comprobantes oficiales
- **Validación**: Requiere certificado válido

### Entorno de Beta (Pruebas)

- **URL**: `https://e-beta.sunat.gob.pe`
- **Uso**: Pruebas y desarrollo
- **Certificado**: Puede usar certificado de pruebas

---

## API Externa

### Búsqueda de Clientes

```
GET /decolecta/search?company_id=1&documento=12345678901
```

### Búsqueda de Productos SUNAT

```
GET /sunat-products/search?query=producto
```

---

## Solución de Problemas

### Error: "No hay certificado configurado"

**Solución**: Verifica que el certificado esté en `storage/app/certificates/` y la configuración en el panel de empresa.

### Error: "Tiempo de conexión agotado"

**Solución**: Verifica tu conexión a internet y que los puertos firewall estén abiertos (443/HTTPS).

### Error: "XML mal formado"

**Solución**: Verifica que los datos del cliente estén completos (RUC, dirección, etc.).

### Error: "Certificado vencido"

**Solución**: Renueva tu certificado digital en SUNAT y actualiza el archivo.

---

## Comandos Útiles

```bash
# Limpiar cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Regenerar клавиши
php artisan key:generate

# Ver rutas
php artisan route:list

# Modo de producción
php artisan optimize

# Ver información de la app
php artisan about
```

---

## Tecnologías Utilizadas

### Framework y Lenguaje

- **Laravel** 13.x - Framework PHP
- **PHP** 8.2+ - Lenguaje de programación

### Librerías

- **Greenter** - Librería oficial SUNAT
- **TailwindCSS** - Framework de estilos
- **Vite** - Build tool
- **mpdf** - Generación de PDF
- **endroid/qr-code** - Código QR

### Base de Datos

- **MySQL** 8.0+
- **MariaDB** 10.4+

---

## Contribuir

1. Haz un **Fork** del proyecto
2. Crea una rama (`git checkout -b feature/nueva-caracteristica`)
3. Realiza tus cambios y haz **commit** (`git commit -am 'Agrega nueva característica'`)
4. Haz **push** a la rama (`git push origin feature/nueva-caracteristica`)
5. Abre un **Pull Request**

---

## Licencia

Este proyecto está licenciado bajo la [MIT License](LICENSE).

---

## Soporte

- Documentación oficial SUNAT: [https://www.sunat.gob.pe](https://www.sunat.gob.pe)
- Greenter: [GitHub](https://github.com/giankpo/greenter)
- Laravel: [Documentación](https://laravel.com/docs)

---

<p align="center">Desarrollado con ❤️ para Perú</p>