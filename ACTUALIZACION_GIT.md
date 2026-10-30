# Guía de actualización vía Git — FacturaFácil

**Fecha:** 2026-10-29  
**Entorno:** Laravel 13 + MySQL/MariaDB + Node.js (Print Server)  
**Alcance:** pasos seguros para aplicar cambios traídos con `git pull` en desarrollo, Laragon y producción.

> Regla principal: **backup primero, migraciones después, seeders solo si es necesario y nunca `git clean -fdx` sin revisar qué borra**.

---

## 0. Antes de actualizar

### 0.1 Backup
En Windows/Laragon (ajusta usuario, base y ruta):

```powershell
mysqldump -u USUARIO -p BASE > storage\backup\pre-update-YYYYMMDD-HHMM.sql
```

También respalda:

- `.env`
- `storage\app\certificates\`
- `storage\app\public\`
- `storage\app\backup\`
- `print-server-node\` (configuración local)
- `public\build\` (si se compila frontend)

En Linux:

```bash
mysqldump -u USUARIO -p BASE > storage/backup/pre-update-$(date +%Y%m%d-%H%M).sql
tar -czf storage/backup/storage-pre-update-$(date +%Y%m%d-%H%M).tar.gz storage/app .env
```

### 0.2 Revisar el repositorio

```powershell
git status
git log --oneline -5
```

Si hay cambios locales:

```powershell
git stash push -m "cambios locales antes de actualizar"
```

No ejecutes:

```powershell
git clean -fdx
git reset --hard
```

sin backup: pueden borrar `.env`, `storage`, certificados, backups y `node_modules`.

### 0.3 Mantenimiento

```powershell
php artisan down --retry=60
```

Opcional, con acceso secreto:

```powershell
php artisan down --retry=60 --secret="clave-temporal"
```

---

## 1. Traer los cambios

```powershell
git fetch --all --prune
git pull --ff-only
```

Si el proyecto usa rama/tag de producción:

```powershell
git checkout main
git pull --ff-only
```

Verifica:

```powershell
git status
git log --oneline -5
```

---

## 2. Dependencias PHP

Usa `install`, no `update`, en producción:

```powershell
composer install --no-dev --optimize-autoloader
```

- Si `composer.lock` cambió, `composer install` instala exactamente lo bloqueado.
- No ejecutes `composer update` en producción salvo que sea una actualización planificada y probada.
- Revisa vulnerabilidades:

```powershell
composer audit
```

---

## 3. Frontend / Vite

```powershell
npm ci
npm run build
```

- `npm ci` respeta `package-lock.json`.
- `npm run build` actualiza `public/build`.
- Si usas Cloudflare u otra CDN, limpia la caché de assets después.

---

## 4. Print Server (Node.js)

Solo si cambió `print-server-node` o sus dependencias:

```powershell
cd print-server-node
npm ci
npm audit
```

Luego reinicia el proceso:

```powershell
# Cierra la instancia anterior y vuelve a iniciar
start-hidden.vbs
```

Verifica:

```powershell
Invoke-RestMethod http://127.0.0.1:9100/status
```

Si el puerto 9100 está ocupado, revisa el proceso Node y el firewall local.

---

## 5. Variables de entorno

Revisa `.env` contra `.env.example` y agrega claves nuevas.

Para producción:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio
LOG_LEVEL=warning
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_ENCRYPT=true
```

### Importante

- **No ejecutes `php artisan key:generate`** si ya existe `APP_KEY`; romperías sesiones y datos cifrados.
- Si cambió `APP_URL`, vuelve a generar caché de configuración.
- Si el sitio está detrás de Cloudflare/proxy, mantén la configuración de proxies confiables.
- No subas `.env` a Git.

---

## 6. Base de datos

### 6.1 Revisar migraciones

```powershell
php artisan migrate:status
```

Puedes simular primero:

```powershell
php artisan migrate --pretend
```

### 6.2 Aplicar

```powershell
php artisan migrate --force
```

Migraciones recientes relevantes:

- `2026_10_28_000001_create_restaurant_kitchen_batches_table`
- `2026_10_28_000002_fix_delivered_kitchen_batch_items`
- `2026_10_29_000001_restrict_restaurant_table_deletions`

Estas incluyen:

- Lotes KDS por estación.
- Saneamiento de ítems históricos en lotes entregados.
- Protección contra borrado en cascada de mesas/pedidos.

### 6.3 Seeders: precaución

**No ejecutes `php artisan db:seed` a ciegas.**

- `PermissionsSeeder` puede resetear permisos de roles.
- `AdminUserSeeder` puede recrear/cambiar cuentas administrativas.
- `TestUsersSeeder`/`SuperAdminSeeder` pueden crear cuentas demo con contraseñas conocidas.

Si el update exige un seeder específico:

```powershell
php artisan db:seed --class=NombreDelSeeder --force
```

---

## 7. Storage y permisos

```powershell
php artisan storage:link
```

Si el enlace existe pero está roto:

```powershell
php artisan storage:link --force
```

En Linux:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Verifica especialmente:

- `storage/app/certificates` (certificados SUNAT, privados).
- `storage/app/public/website` (imágenes y slides).
- `storage/app/public/products` y `categories`.
- `storage/app/backup`.

---

## 8. Cachés

```powershell
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
php artisan event:cache
```

`route:cache` puede fallar si hay rutas con closures. Si ocurre:

```powershell
php artisan route:clear
```

No dejes la aplicación con una caché de rutas a medias.

Alternativa completa:

```powershell
php artisan optimize
```

Si falla por rutas con closures, usa `config:cache` + `view:cache` + `event:cache` y deja las rutas sin cachear.

---

## 9. Scheduler, colas e impresión

### Scheduler

```powershell
php artisan schedule:list
```

Debe seguir activo:

- Windows: `scheduler.vbs` o tarea `FacturaFacilScheduler`.
- Linux: cron cada minuto con `php artisan schedule:run`.

No uses `schedule:work` en producción; es para desarrollo.

Tareas esperadas:

- `print:process-queue` cada minuto.
- `sunat:send-daily-summary` 09:00 y 12:00.
- `sunat:check-summaries` 09:05, 09:20, 12:05 y 12:20.
- `sunat:verify-unresolved` 12:06.
- `sunat:download-padron` domingos 02:00.

### Cola de impresión

```powershell
php artisan print:process-queue
```

Revisa la cola en `/printers/queue`.

### Queue worker (si aplica)

```powershell
php artisan queue:restart
```

---

## 10. Activar el sistema

```powershell
php artisan up
```

---

## 11. Verificación posterior

### Comandos

```powershell
php artisan about
php artisan migrate:status
php artisan schedule:list
php artisan route:list
php artisan test
composer audit
```

### Pruebas manuales

- `/up` responde correctamente.
- Login de admin y cajero.
- POS: buscar producto, agregar, cobrar sin emitir SUNAT real.
- Restaurante:
  - Abre directamente en el primer salón.
  - Los otros salones están ocultos hasta hacer clic.
  - El modal de producto abre con los auxiliares ya cargados.
- KDS:
  - Un card por lote, lote nuevo arriba.
  - Sonido por lote nuevo.
  - Historial/Métricas.
- Web pública:
  - Slider visible en escritorio y móvil.
  - Los slides cargan desde `storage`.
- Impresión:
  - Print server activo.
  - Cola sin trabajos atascados.
- SUNAT:
  - No enviar comprobantes reales durante la prueba.
- Logs:
  - `storage/logs/laravel.log` sin errores nuevos.
- `.env`:
  - `APP_DEBUG=false` en producción.

---

## 12. Seguridad después del update

Revisa `resultadoseguridad.md` y prioriza:

1. Actualizar dependencias PHP/Node.
2. `APP_DEBUG=false` y HTTPS.
3. Sacar backups/certificados de Git; rotar credenciales.
4. Deshabilitar cuentas demo y cambiar contraseñas.
5. Corregir XSS en KDS/restaurante/autopedido.
6. Aislar por empresa los endpoints con IDOR.
7. Proteger print server, `/register`, `/decolecta`, `/marcar` y `/autopedido/success`.
8. Revisar permisos de fotos de asistencia.

---

## 13. Rollback

Si algo falla:

```powershell
php artisan down --retry=60
git checkout <tag-o-commit-anterior>
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate:rollback --step=1
php artisan optimize:clear
php artisan up
```

Si el rollback de migraciones no es suficiente, restaura la base y `storage` desde el backup.

No hagas rollback de una migración que eliminó/transformó datos sin haber restaurado antes el backup.

---

## 14. Problemas comunes

### `route:cache` falla por Closure
```powershell
php artisan route:clear
php artisan config:cache
php artisan view:cache
```

### 419 Page Expired / sesión
- Revisa `APP_URL`, `SESSION_SECURE_COOKIE` y `SESSION_DOMAIN`.
- En HTTP local puede requerir `SESSION_SECURE_COOKIE=false`.
- Limpia configuración:

```powershell
php artisan optimize:clear
php artisan config:cache
```

### Imágenes o slides no cargan
```powershell
php artisan storage:link --force
```
Verifica permisos de `storage/app/public`.

### Print server inactivo
- Revisa que Node esté corriendo.
- Ejecuta `start-hidden.vbs`.
- Verifica `http://127.0.0.1:9100/status`.
- Revisa firewall y puerto 9100.

### Scheduler no ejecuta
- Windows: tarea `FacturaFacilScheduler` o `scheduler.vbs`.
- Linux: cron cada minuto.
- Comprueba con `php artisan schedule:list`.

### Migración falla
- No continúes sin backup.
- Revisa `php artisan migrate:status`.
- Revisa el error exacto en `storage/logs/laravel.log`.
- Si es por FK/columna existente, corrige la migración y prueba en local antes.

### No ejecutes seeders globales
Evita `php artisan db:seed` sin revisar. Puede resetear permisos o recrear cuentas demo.

---

## 15. Checklist rápido

- [ ] Backup de base, `.env`, storage y certificados.
- [ ] `git status` limpio o cambios guardados.
- [ ] `git pull --ff-only`.
- [ ] `composer install --no-dev --optimize-autoloader`.
- [ ] `npm ci && npm run build`.
- [ ] `npm ci` en `print-server-node` si cambió.
- [ ] Revisar `.env` y `APP_DEBUG=false`.
- [ ] `php artisan migrate --force`.
- [ ] Revisar seeders; no ejecutar globales sin necesidad.
- [ ] `php artisan storage:link`.
- [ ] `php artisan optimize:clear`.
- [ ] `config:cache`, `view:cache`, `event:cache`.
- [ ] Reiniciar print server.
- [ ] Verificar scheduler y `print:process-queue`.
- [ ] `php artisan up`.
- [ ] Smoke test: login, POS, restaurante, KDS, web, impresión.
- [ ] Revisar logs.
- [ ] `composer audit` y `npm audit`.
- [ ] Revisar `resultadoseguridad.md`.
