Listos. Dos scripts generados:
---
Script 1: Eliminar ventas y cajas
Archivo: storage/app/tmp/clean_ventas.php
Ejecutar:
php artisan tinker --execute="include storage_path('app/tmp/clean_ventas.php');"
Elimina: invoices, invoice_items, orders, order_items, cash registers, print_jobs. Libera mesas y resetea series.
---
Script 2: Eliminar productos
Archivo: storage/app/tmp/clean_productos.php
Ejecutar:
php artisan tinker --execute="include storage_path('app/tmp/clean_productos.php');"
Elimina: productos y componentes de productos compuestos. Si hay ventas activas, pide confirmación y sugiere ejecutar el Script 1 primero.
---
Orden recomendado: Primero Script 1 (ventas) → luego Script 2 (productos).