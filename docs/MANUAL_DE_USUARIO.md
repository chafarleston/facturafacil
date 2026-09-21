# Manual de Usuario — FacturaFácil

> FacturaFácil by RealComputer SAC
> Sistema de Punto de Venta, Restaurante, Caja, Facturación Electrónica SUNAT y Control de Personal.

---

## Índice

1. [Introducción](#1-introduccion)
2. [Iniciar sesión](#2-iniciar-sesion)
3. [Panel principal (Dashboard)](#3-panel-principal-dashboard)
4. [Punto de Venta (POS)](#4-punto-de-venta-pos)
5. [Restaurante y Mesas](#5-restaurante-y-mesas)
6. [Cocina (KDS)](#6-cocina-kds)
7. [Autoservicio / Autopedido](#7-autoservicio--autopedido)
8. [Caja](#8-caja)
9. [Comprobantes y SUNAT](#9-comprobantes-y-sunat)
10. [Productos](#10-productos)
11. [Clientes](#11-clientes)
12. [Series de Comprobantes](#12-series-de-comprobantes)
13. [Empresas y Bloqueo del Sistema](#13-empresas-y-bloqueo-del-sistema)
14. [Usuarios y Roles](#14-usuarios-y-roles)
15. [Control de Asistencia](#15-control-de-asistencia)
16. [Backup y Restauración](#16-backup-y-restauracion)
17. [Impresión](#17-impresion)
18. [Solución de problemas](#18-solucion-de-problemas)

---

## 1. Introducción

**FacturaFácil** es un sistema integrado que permite:

- Vender en **punto de venta (POS)** y en **restaurante** (con mesas, cocina y kiosko de autoservicio).
- Emitir comprobantes electrónicos (**Boleta**, **Factura**, **Notas de Crédito/Débito**) y enviarlos a **SUNAT**.
- Controlar la **caja** (apertura, ventas, ingresos/gastos, cierre y reportes).
- Gestionar **productos, clientes, series, usuarios y permisos**.
- Llevar el **control de asistencia** del personal (marcación por DNI o cámara).
- Generar **backups** y restaurar la base de datos.
- Imprimir en **impresoras térmicas** (cocina, bar, precuenta, caja, autopedido).

> **Importante:** al final del manual encontrarás una sección de [Solución de problemas](#18-solucion-de-problemas) con los errores más comunes (servidor de impresión, cámara, cajón de dinero).

---

## 2. Iniciar sesión

1. Abre el navegador y escribe la dirección del sistema (por ejemplo `http://facturafacil.test`).
2. Ingresa tu **correo electrónico** y tu **contraseña**.
3. Pulsa el botón **Entrar**.

![Inicio de sesión](manual-img/01-login.png)

Según el rol del usuario, el sistema abrirá directamente la pantalla correspondiente:

| Rol | Pantalla inicial |
|-----|------------------|
| Administrador | Dashboard |
| Cajero | Caja |
| Mozo | Restaurante |

---

## 3. Panel principal (Dashboard)

El **Dashboard** resume la actividad del negocio: ventas del día, recaudación por método de pago, comprobantes emitidos y acceso rápido a los módulos principales.

![Panel principal](manual-img/02-dashboard.png)

En el menú lateral encontrarás los módulos: **POS, Restaurante, Caja, Comprobantes, Productos, Clientes, Series, Empresas, Usuarios, Asistencia, Backup e Impresión**.

---

## 4. Punto de Venta (POS)

El **POS** permite vender productos de forma rápida (venta por mostrador o autoservicio).

1. Selecciona un **producto** de la lista (o usa el buscador por código/nombre).
2. Ajusta la **cantidad** y las **notas** si es necesario.
3. El producto pasa al **carrito** con su subtotal.
4. Pulsa **Cobrar**.
5. Elige el **cliente** (o se usa "Clientes Varios" por defecto), el **método de pago** (Efectivo, Tarjeta, Yape, Plin) y el **tipo de documento**.
6. Completa la venta → se genera el comprobante y se actualiza el **stock** y la **caja** en vivo.
7. Si pagas en **efectivo**, el cajón de dinero se abre automáticamente.

![Punto de venta](manual-img/03-pos.png)

**Nota:** si hay más de un producto igual, puedes cambiar el **precio mínimo/descuento** y agregar **notas** por línea antes de cobrar.

---

## 5. Restaurante y Mesas

### 5.1 Mapa de mesas

El módulo **Restaurante** muestra el mapa de mesas por piso.

![Restaurante - mesas](manual-img/04-restaurante.png)

- **Verde** = mesa disponible.
- **Rojo** = mesa ocupada.
- **Amarillo** = mesa reservada.
- Los botones superiores permiten **Cambiar Modo** (pantalla vs impresión) y abrir **Cocina**.

### 5.2 Abrir una mesa y tomar el pedido

1. Haz clic sobre una mesa (verde) para abrirla.
2. Se abre el **pedido** de la mesa.
3. Haz clic en un **producto** para agregarlo con su cantidad y notas.

![Pedido de mesa abierta](manual-img/32-restaurante-pedido.png)

![Modal de producto](manual-img/33-restaurante-modal-producto.png)

### 5.3 Enviar a cocina

Con el pedido listo, pulsa **Enviar a Cocina**. Los productos pasan a la pantalla de cocina (KDS) o se imprimen en la impresora de cocina, según el modo configurado.

### 5.4 Precuenta

El botón **Precuenta** abre un selector de impresora para imprimir el detalle de la mesa.

![Precuenta](manual-img/34-restaurante-precuenta.png)

> La precuenta **excluye los productos ya pagados** al dividir la cuenta.

### 5.5 Cobrar la mesa

- Pulsa **Cobrar** → se factura todo lo pendiente de la mesa y se libera la mesa.
- **Dividir Cuenta** permite repartir la cuenta en varios comprobantes (por producto/cantidad), cada uno con su cliente, tipo de documento y método de pago.
- **Fusionar** une la cuenta de una mesa con la de otra mesa ocupada.

---

## 6. Cocina (KDS)

La pantalla de **Cocina** (KDS) muestra en tiempo real los pedidos enviados por las mesas y el autoservicio, con sus estados.

![Cocina KDS](manual-img/05-kds-cocina.png)

- Secciones **MOZO** (pedidos de mesas) y **KIOSKO** (autoservicio).
- El cocinero avanza los estados: **En preparación → Listo → Entregado**.
- Cada pedido muestra mesa, hora, notas y elementos auxiliares (adicionales).

---

## 7. Autoservicio / Autopedido

El **kiosko de autoservicio** permite al cliente hacer su pedido en pantalla táctil.

![Autoservicio / Autopedido](manual-img/06-autopedido.png)

1. El cliente selecciona sus productos y confirma el pedido.
2. El pedido queda como **pendiente de pago** y se envía a cocina.
3. El cajero cobra el pedido desde el módulo Restaurante (sección **KIOSKO**).

---

## 8. Caja

### 8.1 Página de Caja

Aquí se **apertura** la caja (monto inicial y referencia), se consultan las cajas del día, y se realiza el **cierre**.

![Caja](manual-img/07-caja.png)

- Solo el **Administrador** cierra la caja; el cajero apertura pero no la cierra.
- El **Saldo Final de Efectivo** considera: apertura + ventas en efectivo + ingresos − egresos − cierre.
- Pagos de Yape/Plin/Tarjeta se muestran como informativos.

### 8.2 Ingresos y Gastos

Registra ingresos/egresos **en efectivo** de la caja abierta (motivo libre). Requiere tener una caja abierta.

![Ingresos y Gastos](manual-img/08-caja-movimientos.png)

### 8.3 Configuración de Reporte

Define qué secciones se imprimen en los reportes de caja (A4 / 80mm / ESC-POS): **Lista de comprobantes**, **Productos vendidos** y **Líneas eliminadas**.

![Configuración de Reporte](manual-img/09-caja-config-reporte.png)

---

## 9. Comprobantes y SUNAT

### 9.1 Lista de Comprobantes

Muestra todas las **boletas y facturas** emitidas. Puedes filtrar por fecha, cliente, serie y número.

![Comprobantes](manual-img/10-comprobantes.png)

Las **boletas** (no facturadas) se muestran en su propia pestaña.

![Boletas y NV](manual-img/11-boletas-nv.png)

### 9.2 Crear un comprobante manual

Puedes crear un **comprobante nuevo** (boleta/factura) eligiendo cliente, serie, productos y pagos.

![Nuevo comprobante](manual-img/12-comprobante-nuevo.png)

### 9.3 Resúmenes diarios (SUNAT)

Las **boletas y notas de boleta** se envían a SUNAT por **Resumen Diario**. Esta pantalla permite **enviar**, **consultar estado** y **reintentar** los resúmenes pendientes.

![Resúmenes SUNAT](manual-img/13-resumenes-sunat.png)

---

## 10. Productos

### 10.1 Lista de productos

Muestra el catálogo con código, nombre, precio, stock, unidad e impuesto.

![Productos](manual-img/14-productos.png)

Desde aquí puedes **crear, editar, eliminar, duplicar, importar y exportar** productos.

### 10.2 Crear un producto

Ingresa código, descripción, precio de venta (y mínimo), impuesto (IGV), unidad de medida y stock inicial.

![Nuevo producto](manual-img/15-producto-nuevo.png)

### 10.3 Productos compuestos

Un **producto compuesto** combina varios productos componentes. Se define en su propia pantalla.

![Producto compuesto](manual-img/16-producto-compuesto.png)

### 10.4 Reporte de inventario

Genera el **reporte de inventario** con stock actual, valorizado por costo.

![Reporte de inventario](manual-img/17-inventario.png)

---

## 11. Clientes

El módulo **Clientes** permite administrar el directorio de clientes (buscar, crear, editar). El cliente se usa al facturar y al **dividir cuenta** en el restaurante.

![Clientes](manual-img/18-clientes.png)

> "Clientes Varios" (DNI 88888888) se usa por defecto cuando no se selecciona cliente.

---

## 12. Series de Comprobantes

Administra las **series** de boleta, factura, nota de crédito/débito, etc. Se numeran automáticamente. 

![Series](manual-img/19-series.png)

![Nueva serie](manual-img/20-series-nueva.png)

> La numeración se maneja automáticamente; no editar manualmente el número actual si no es necesario.

---

## 13. Empresas y Bloqueo del Sistema

El módulo **Empresas** administra las empresas (RUC, razón social, logo, certificado digital SUNAT y credenciales SOAP).

![Empresas](manual-img/21-empresas.png)

### Bloquear / Desbloquear el sistema

- **Solo el propietario** (correo configurado como dueño) ve el botón **Bloquear Sistema** en esta pantalla.
- Al bloquear, **todo el sistema** muestra el mensaje de suspensión y ningún otro usuario puede operar.
- El propio propietario puede **Desbloquear** desde la pantalla de bloqueo o desde Empresas.

![Sistema bloqueado](manual-img/35-sistema-bloqueado.png)

> Es una herramienta útil cuando vence el pago o el soporte: el sistema queda inoperativo para el personal hasta que el propietario lo desbloquee.

---

## 14. Usuarios y Roles

### 14.1 Usuarios

Crea y administra los usuarios que acceden al sistema. Cada usuario tiene un **rol** y opcionalmente una **empresa**.

![Usuarios](manual-img/22-usuarios.png)

### 14.2 Roles y Permisos

Define los **roles** (Administrador, Cajero, Mozo, Usuario) y asigna o quita **permisos** (ver facturar, abrir caja, cancelar pedidos, gestionar productos, fusionar mesas, etc.).

![Roles](manual-img/23-roles.png)

---

## 15. Control de Asistencia

### 15.1 Personal

Registra al personal con DNI y, opcionalmente, su foto para reconocimiento facial.

![Personal](manual-img/24-personal.png)

### 15.2 Horarios

Define los horarios (corrido o dividido) por persona.

![Horarios](manual-img/25-horarios.png)

### 15.3 Reglas de tardanza

Configura umbrales de falta, falta grave, suspensión y descuentos por tramos de minutos. También el **modo de marcación** (DNI, cámara, o ambos) y el umbral de similitud facial.

![Reglas de tardanza](manual-img/26-reglas-tardanza.png)

### 15.4 Marcaciones (logs)

Consulta los registros de marcación de cada trabajador.

![Marcaciones](manual-img/27-marcaciones.png)

### 15.5 Reportes

Genera reportes diario, semanal o mensual de asistencia en **PDF o Excel**.

![Reportes de asistencia](manual-img/28-reportes-asistencia.png)

### 15.6 Marcador (kiosko)

Pantalla de marcación por **DNI** (con cámara opcional). Requiere contexto seguro (HTTPS o localhost) para usar la cámara.

![Marcador kiosko](manual-img/29-marcador.png)

---

## 16. Backup y Restauración

Genera el **backup de la base de datos** con un clic y descarga el archivo `.sql`. También permite **restaurar** desde un archivo de respaldo.

![Backup](manual-img/30-backup.png)

> Realiza backups periódicamente y guárdalos fuera del equipo.

---

## 17. Impresión

Administra los **slots de impresoras**: cocina 1, cocina 2, bar, precuenta 1/2/3, caja y autopedido. Cada uno apunta a una impresora térmica (ESC/POS) del servidor de impresión.

![Impresoras](manual-img/31-impresoras.png)

- El **servidor de impresión** debe estar activo (`print-server-node`).
- En efectivo, el **cajón de dinero** se abre automáticamente.
- No usar **emojis** en los tickets térmicos (la impresora los distorsiona).

---

## 18. Solución de problemas

| Problema | Causa probable | Solución |
|---|---|---|
| No imprime nada | Servidor de impresión apagado | Reinicia `print-server-node` (puerto 9100) |
| No abre el cajón | Impresora/cajón no configurado o server apagado | Verifica el servidor y la impresora del slot "caja" |
| La cámara del marcador no sale | Contexto no seguro | Usa `localhost`, HTTPS o el flag de Chrome con `--unsafely-treat-insecure-origin-as-secure` |
| Sistema muestra "Sistema bloqueado" | Vencimiento de pago/soporte | El propietario debe **Desbloquear** desde Empresa o la pantalla de bloqueo |
| El comprobante no llega a SUNAT | Resumen diario pendiente | En SUNAT → Resúmenes, pulsa **Enviar** y luego **Consultar estado** |
| Sesión expirada al usar el sistema | Tiempo de sesión terminado | Vuelve a iniciar sesión (las pantallas recargadas piden login) |

---

*Documento generado para el uso del sistema FacturaFácil.*