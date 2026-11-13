# Instalación de print-server-node en Linux

Guía para instalar el servidor de impresión de FacturaFácil en una máquina Linux. El servidor usa **Node.js 18 o superior**, **CUPS** para impresoras locales y sockets TCP para impresoras de red.

> En Linux **no se usa `raw-print.ps1` ni los archivos `.bat`**. Esos archivos son exclusivos de Windows.

## 1. Requisitos

- Node.js 18 o superior.
- CUPS y `lp` instalados para impresoras locales/USB.
- Acceso a la impresora de red por IP y puerto `9100`, si aplica.

### Ubuntu / Debian

```bash
sudo apt update
sudo apt install -y nodejs npm cups cups-client
sudo systemctl enable --now cups
```

Verificar versiones:

```bash
node -v
npm -v
lpstat -p -d
```

Si `node -v` es menor a 18, instalar Node.js 20 desde NodeSource:

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### RHEL / Rocky / AlmaLinux

```bash
sudo dnf install -y nodejs npm cups cups-client
sudo systemctl enable --now cups
```

## 2. Copiar el servidor

Opción recomendada: instalarlo fuera del proyecto Laravel.

```bash
sudo mkdir -p /opt/facturafacil-print
sudo cp -r print-server-node/. /opt/facturafacil-print/
cd /opt/facturafacil-print
sudo npm install --omit=dev
```

Si se usa `package-lock.json`, se puede usar:

```bash
sudo npm ci --omit=dev
```

Verificar que existan `server.js`, `package.json` y `node_modules`:

```bash
ls -la
```

## 3. Probar manualmente

```bash
cd /opt/facturafacil-print
node server.js
```

En otra terminal:

```bash
curl http://127.0.0.1:9100/status
curl http://127.0.0.1:9100/printers
```

La respuesta de `/status` debe incluir:

```json
{
  "status": "ok",
  "platform": "linux"
}
```

Detener la prueba con `Ctrl+C`.

## 4. Configurar impresoras locales con CUPS

1. Abrir la administración de CUPS:

```text
http://localhost:631
```

2. Agregar la impresora USB/local.
3. Verificar que CUPS tenga una cola activa:

```bash
lpstat -p -d
```

4. Probar una impresión RAW:

```bash
lp -d NOMBRE_COLA -o raw /ruta/al/archivo.bin
```

El nombre de la cola que muestra `lpstat` es el mismo que debe configurarse en FacturaFácil y el que devuelve `GET /printers`.

Si el usuario del servicio necesita administrar CUPS:

```bash
sudo usermod -aG lpadmin USUARIO
```

## 5. Impresora de red

Para impresoras ESC/POS conectadas por Ethernet/WiFi no se necesita CUPS. En FacturaFácil se configura la IP y el puerto:

```text
IP: 192.168.1.50
Puerto: 9100
```

Prueba directa:

```bash
curl -X POST http://127.0.0.1:9100/print-escpos-text \
  -H "Content-Type: application/json" \
  -d '{"ip":"192.168.1.50","port":9100,"text":"Prueba de impresion","cut":true}'
```

## 6. Crear el servicio systemd

Crear el usuario del servicio si se desea aislar el proceso:

```bash
sudo useradd --system --home /opt/facturafacil-print --shell /usr/sbin/nologin facturaprint
sudo chown -R facturaprint:lp /opt/facturafacil-print
```

Crear `/etc/systemd/system/facturafacil-print.service`:

```ini
[Unit]
Description=FacturaFacil Print Server
After=network.target cups.service

[Service]
Type=simple
WorkingDirectory=/opt/facturafacil-print
ExecStart=/usr/bin/node server.js
Restart=always
RestartSec=5
User=facturaprint
Group=lp
Environment=PORT=9100
Environment=HOST=127.0.0.1

[Install]
WantedBy=multi-user.target
```

Notas:

- Usar `HOST=127.0.0.1` si Laravel y el print server están en la misma máquina.
- Usar `HOST=0.0.0.0` solo si otros equipos de la red van a llamar al print server.
- Ajustar `User` y `Group` según la instalación.
- El usuario del servicio debe poder escribir en `/opt/facturafacil-print` para crear `print-server.log`.

Activar el servicio:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now facturafacil-print
sudo systemctl status facturafacil-print
```

Ver logs:

```bash
journalctl -u facturafacil-print -f
```

Reiniciar después de actualizar el código:

```bash
sudo systemctl restart facturafacil-print
```

## 7. Firewall

Si el print server solo se usa localmente, no abrir el puerto.

Si otros equipos de la red deben conectarse:

```bash
sudo ufw allow 9100/tcp
```

No exponer el puerto `9100` directamente a Internet: el servicio no tiene autenticación propia.

## 8. Prueba desde FacturaFácil

1. Verificar que el servicio esté activo:

```bash
curl http://127.0.0.1:9100/status
```

2. En FacturaFácil, abrir **Impresoras** y comprobar que aparezcan las colas de CUPS.
3. Asignar la impresora a cada módulo: cocina, bar, precuenta, caja o autopedido.
4. Imprimir una prueba desde el sistema.

## 9. Solución de problemas

| Problema | Causa probable | Solución |
|---|---|---|
| `node: command not found` | Node.js no instalado o PATH incorrecto | Instalar Node.js 18+ y verificar `node -v` |
| `/printers` devuelve vacío | CUPS no tiene colas o `lpstat` no funciona | Ejecutar `lpstat -p -d` y agregar la impresora en CUPS |
| `lp: command not found` | CUPS client no instalado | `sudo apt install cups-client` |
| No imprime en local | Usuario sin acceso a CUPS | Agregar usuario a `lpadmin` y revisar permisos |
| No imprime por red | IP, puerto o firewall incorrectos | Probar `curl` al puerto `9100` y revisar la red |
| `EADDRINUSE` | Puerto `9100` ocupado | Detener el proceso anterior o cambiar `PORT` |
| No se crea `print-server.log` | Permisos de escritura | Dar permisos al usuario del servicio |
| Impresión con caracteres raros | Encoding incorrecto | Usar `mode: "escpos"` y `codePage: "cp850"` |

## 10. Actualización

```bash
cd /opt/facturafacil-print
sudo npm install --omit=dev
sudo systemctl restart facturafacil-print
sudo systemctl status facturafacil-print
```

## 11. Desinstalación

```bash
sudo systemctl disable --now facturafacil-print
sudo rm /etc/systemd/system/facturafacil-print.service
sudo systemctl daemon-reload
sudo rm -rf /opt/facturafacil-print
```
