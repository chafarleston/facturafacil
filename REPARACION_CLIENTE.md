# Reparación en el servidor del cliente — APP_KEY / cachés

**Uso:** copiar `reparar-cliente.bat` y `reparar-cliente.ps1` a la raíz del proyecto en la máquina del cliente y ejecutar el `.bat`.

**Importante:** este paquete **no elimina ni modifica ventas, productos, pedidos ni mesas**. Solo revisa/restaura `APP_KEY`, permisos, enlace de storage y cachés.

---

## 1. Qué significa el error

```text
production.ERROR: No application encryption key has been specified.
Illuminate\Encryption\MissingAppKeyException
```

Laravel no encontró `APP_KEY` en el `.env`. Sin esa clave no puede cifrar/descifrar cookies, sesiones, tokens CSRF, URLs firmadas ni datos cifrados.

Causas típicas:

- `.env` sin `APP_KEY` o con `APP_KEY=` vacío.
- Se ejecutó `config:cache` cuando la clave no existía.
- El `.env` no está en la raíz del proyecto o no es legible.
- `bootstrap/cache` o `storage` sin permisos de escritura.
- Se copió el proyecto sin `.env` o con el `.env` de otra instalación.

El log revisado también mostraba:

```text
rename(...bootstrap/cache/services.php): Acceso denegado (code: 5)
Target class [view] does not exist
```

Eso confirma problemas de caché/permisos en el servidor del cliente.

---

## 2. Antes de ejecutar

### 2.1 Backup

```powershell
# Base de datos
mysqldump -u USUARIO -p BASE > storage\backup\pre-reparacion-YYYYMMDD-HHMM.sql
```

Copiar también:

- `.env`
- `storage\app\certificates\`
- `storage\app\public\`
- `storage\app\backup\`

### 2.2 No generar una `APP_KEY` nueva a ciegas

- Si el cliente tenía datos cifrados o sesiones que se deben conservar, **restaurar la `APP_KEY` anterior** desde un backup.
- Generar una nueva invalida sesiones, cookies, tokens de recuperación y cualquier dato cifrado.
- El script pide elegir entre **restaurar** o **generar**.

---

## 3. Ejecutar el script

### Opción recomendada: doble clic

1. Copiar `reparar-cliente.bat` y `reparar-cliente.ps1` a:
   `C:\laragon\www\facturafacil\` (o la raíz real del proyecto en el cliente).
2. Ejecutar `reparar-cliente.bat`.
3. Si Windows pide permisos, ejecutar como administrador.
4. Si la `APP_KEY` falta, elegir:
   - `1` para restaurar la clave anterior.
   - `2` para generar una nueva (solo con backup y confirmación `SI`).

### Simulación (no modifica nada)

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\reparar-cliente.ps1 -DryRun
```

### Ejecución manual

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\reparar-cliente.ps1
```

---

## 4. Qué hace el script

1. Verifica que exista `artisan` y `.env`.
2. Busca `php.exe` en el PATH o en `C:\laragon\bin\php`.
3. Revisa `APP_KEY`; si falta, permite restaurarla o generar una nueva.
4. Backupa el `.env` antes de modificarlo.
5. Comprueba escritura en:
   - `bootstrap\cache`
   - `storage`
   - `storage\framework`
   - `storage\logs`
   - `storage\app`
   - `storage\backup`
6. Crea `public/storage` si falta.
7. Ejecuta:
   - `php artisan optimize:clear`
   - `php artisan config:cache`
   - `php artisan view:cache`
   - `php artisan route:clear`
   - `php artisan up`
8. Muestra `php artisan about`, `migrate:status` y `schedule:list`.
9. Verifica `config('app.key')` sin mostrar la clave.
10. Advierte si `APP_ENV` no es `production`, `APP_DEBUG` no es `false` o `APP_URL` no es HTTPS.

---

## 5. Qué NO hace

- No borra la base de datos.
- No ejecuta `migrate:fresh`.
- No ejecuta `db:seed`.
- No elimina mesas, pedidos, ventas ni productos.
- No cambia contraseñas.
- No modifica `APP_KEY` sin preguntar.
- No recupera las mesas 55/56.

---

## 6. Si el script falla por permisos

Ejecutar PowerShell como administrador y ajustar permisos:

```powershell
icacls "C:\laragon\www\facturafacil\bootstrap\cache" /grant "$env:USERNAME:(OI)(CI)M" /T
icacls "C:\laragon\www\facturafacil\storage" /grant "$env:USERNAME:(OI)(CI)M" /T
```

Si el servicio corre con otro usuario, reemplazar `$env:USERNAME` por ese usuario.

En Linux:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 7. Verificación manual

```powershell
php artisan tinker --execute="echo config('app.key') ? 'APP_KEY_OK' : 'APP_KEY_FALTA';"
php artisan about
php artisan migrate:status
php artisan schedule:list
php artisan storage:link --force
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
php artisan route:clear
php artisan up
```

Luego probar:

- Login.
- POS.
- Restaurante.
- KDS.
- Web pública y slides.
- Impresión.

Revisar:

```powershell
Get-Content storage\logs\laravel.log -Tail 100
```

No debe repetirse `MissingAppKeyException`.

---

## 8. Configuración recomendada de producción

Editar `.env` del cliente:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio
LOG_LEVEL=warning
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_ENCRYPT=true
```

Después:

```powershell
php artisan optimize:clear
php artisan config:cache
```

**No ejecutar `php artisan key:generate`** si ya existe una `APP_KEY` válida.

---

## 9. Sobre las mesas desaparecidas

El error de `APP_KEY` no explica el borrado. Las mesas **55 y 56** se crearon y borraron después del backup; no están en el backup ni en el binlog.

- Recrearlas desde **Restaurante → Pisos/Mesas**.
- Si tenían pedidos históricos, buscar un backup/snapshot del hosting.
- La protección ya está aplicada:
  - No se puede borrar un piso con mesas.
  - No se puede borrar una mesa con pedidos activos o históricos.
  - Las claves foráneas están en `restrict`.

---

## 10. Seguridad

Después de reparar, revisar `resultadoseguridad.md` y priorizar:

- `APP_DEBUG=false` y HTTPS.
- Dependencias actualizadas (`composer audit`, `npm audit`).
- Cuentas demo deshabilitadas y contraseñas cambiadas.
- Backups/certificados fuera de Git.
- XSS en KDS/restaurante/autopedido.
- Aislamiento por empresa.
- Print server y endpoints públicos protegidos.
