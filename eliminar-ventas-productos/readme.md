Scripts de limpieza para FacturaFácil.
---

## Forma recomendada (más segura): BATCH
Ejecutar desde la raíz del proyecto el archivo:

    eliminar_ventas_productos.bat

Menú:
  1) Solo ventas
  2) Solo productos
  3) Ventas + productos (ambos)

Antes de eliminar crea un backup automático de la BD en:
    eliminar-ventas-productos\backup\backup_<fecha>.sql
Requiere confirmar escribiendo SI. Útil para reiniciar la base en pruebas/demo.

---

## Forma manual (tinker)

Script 1: Eliminar ventas y cajas
Archivo: eliminar-ventas-productos/clean_ventas.php
Ejecutar:
php artisan tinker --execute="include base_path('eliminar-ventas-productos/clean_ventas.php');"
Elimina: invoices, invoice_items, orders, order_items, cash registers, print_jobs. Libera mesas y resetea series.

Script 2: Eliminar productos
Archivo: eliminar-ventas-productos/clean_productos.php
Ejecutar:
php artisan tinker --execute="include base_path('eliminar-ventas-productos/clean_productos.php');"
Elimina: productos y componentes de productos compuestos. Si hay ventas activas, pide confirmación y sugiere ejecutar el Script 1 primero.

Orden recomendado: Primero (ventas) → luego (productos).

> NOTA: los archivos viven en la RAÍZ del proyecto (carpeta eliminar-ventas-productos/),
> por eso se usan con base_path(), no storage_path().
> En storage/app/tmp/ hay una copia antigua compacta de clean_ventas.php; no usarla.