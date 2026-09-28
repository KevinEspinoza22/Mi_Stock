# Mi Stock - Integración inventario, compras, ventas y correos

## 1. Requisitos
- XAMPP con Apache, PHP 8.1+ y MySQL/MariaDB.
- Base `BD_Inventario` importada desde el SQL original.
- Ejecutar después `sql/BD_Inventario_EXTRA.sql`.

## 2. Configuración de conexión
Por defecto se usa:
- Host: `127.0.0.1`
- BD: `BD_Inventario`
- Usuario: `root`
- Password: vacío

Se pueden cambiar con las variables `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.

## 3. Login de prueba del SQL original
- `admin@logistock.cl` / `Admin123`
- `operador@logistock.cl` / `Operador123`

## 4. Flujo implementado
- Login por correo y rol.
- Dashboard con productos, stock bajo, ventas del día/mes y valor del inventario.
- Productos con stock mínimo y precios.
- Ventas con carrito, documento y descuento de stock a través de `Movimientos` + `DetalleMovimiento` + trigger existente.
- Compras con proveedor, documento y archivo PDF/JPG/PNG; aumentan stock usando el mismo mecanismo de movimientos.
- Alertas de stock por correo después de operaciones que dejan productos bajo el mínimo.
- Reporte diario mediante `tasks/reporte_diario.php`.

## 5. Correo automático en XAMPP
La función `mail()` necesita un SMTP local configurado. En Windows XAMPP normalmente se configura `sendmail.ini` y el `php.ini` de Apache.

El archivo `tasks/reporte_diario.php` debe programarse en el Programador de tareas de Windows, por ejemplo a las 18:00 cada día, ejecutando:

`php C:\xampp\htdocs\Mi_Stock\tasks\reporte_diario.php`

Ajustar la ruta real del proyecto.
