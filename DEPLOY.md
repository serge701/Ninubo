# Despliegue de NINUBO (sitio + CRM) desde GitHub

Repositorio: `https://github.com/serge701/Ninubo` (rama `main`, **público**).
El código no contiene secretos: cada servidor tiene su propio `includes/config.local.php`, que no se sube a Git.

## Requisitos del hosting

- PHP **8.1 o superior**, con las extensiones `pdo_mysql`, `mbstring`, `dom`, `gd`, `fileinfo`, `openssl` (`php -v`, `php -m`).
- MySQL / MariaDB.
- Apache con `mod_rewrite` y `.htaccess` habilitados (el CRM en `/admin` los necesita).
- Git en la terminal del hosting. **No hace falta Composer**: la librería de PDF (Dompdf) ya va en `admin/vendor/`.
- HTTPS activo (las cookies de sesión del CRM se marcan como `Secure` cuando hay HTTPS).

## Primera vez (conectar el hosting con GitHub)

> Todo lo de abajo se hace desde la terminal del hosting, dentro de la carpeta pública del dominio
> (normalmente `~/public_html`). Los comandos usan esa ruta; ajústala si es otra.

### 1. Respaldo de lo que hay hoy en producción

```bash
cd ~
tar -czf ninubo-site-backup-$(date +%F).tar.gz public_html
mysqldump -u USUARIO_DB -p NOMBRE_DB > ninubo-db-backup-$(date +%F).sql
```

### 2. Rescatar los valores actuales de producción

Antes de reemplazar archivos, anota de los archivos actuales del servidor:

- `includes/config.php` → el valor de `NINUBO_SECRET` y el de `NINUBO_ADMIN_PASSWORD_HASH`
  (son los valores reales que se cambiaron al publicar el sitio).
- `includes/db.php` (o las variables de entorno del panel) → host, nombre, usuario y contraseña de MySQL.

```bash
grep -n "NINUBO_SECRET\|NINUBO_ADMIN_PASSWORD_HASH" ~/public_html/includes/config.php
grep -n "NINUBO_DB\|getenv" ~/public_html/includes/db.php
```

### 3. Traer el código de GitHub

```bash
cd ~/public_html
git init
git remote add origin https://github.com/serge701/Ninubo.git
git fetch origin
git checkout -f -B main origin/main     # reemplaza los archivos del repo; los que no están en Git se conservan
git status
```

### 4. Crear la configuración del servidor

```bash
cp includes/config.local.example.php includes/config.local.php
nano includes/config.local.php
```

Llenar con:

- `NINUBO_BASE` → `''` (el sitio está en la raíz del dominio).
- `NINUBO_SECRET` → el valor rescatado en el paso 2 (si no hay, generar uno:
  `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`).
- `NINUBO_ADMIN_PASSWORD_HASH` → el hash rescatado en el paso 2. Con esa contraseña se crea el primer
  administrador del CRM en `/admin/setup`.
- `NINUBO_DB_*` → credenciales de MySQL de producción.

```bash
chmod 640 includes/config.local.php
php -l includes/config.local.php
```

### 5. Carpetas que el CRM escribe

```bash
mkdir -p admin/storage/uploads admin/storage/contracts admin/storage/cache admin/storage/logs
chmod -R 775 admin/storage
```

### 6. Verificar acceso web y crear el administrador

1. Abrir `https://ninubo.com/` → el sitio debe verse igual que antes.
2. Comprobar que lo interno **no** se puede abrir (debe dar 403 o 404):
   `https://ninubo.com/includes/config.local.php`, `https://ninubo.com/admin/app/routes.php`,
   `https://ninubo.com/admin/vendor/autoload.php`, `https://ninubo.com/DEPLOY.md`.
3. Abrir `https://ninubo.com/admin/` → pantalla **Set up Ninubo CRM**: escribir la contraseña del panel anterior,
   crear el usuario administrador. Esto crea todas las tablas del CRM.
4. En el CRM: **Business settings** (nombre legal, dirección, teléfono, logo, permiso de sales tax, firmante),
   **Catalog** (revisar precios y capturar el costo de producción de cada paquete — no está en el repositorio),
   **Users** (vendedores).

## Actualizaciones posteriores

```bash
cd ~/public_html
git pull origin main
```

Después abrir `https://ninubo.com/admin/`. Si la actualización trae cambios de base de datos, el CRM muestra
**"Database update required"** con el botón **Apply update now** (respaldar la base antes).

## Notas

- **Correo**: los avisos (formulario de contacto, brief completado, contrato firmado) usan `mail()` de PHP.
  Si no llegan, configurar SMTP en el hosting.
- **Nunca** subir a Git: `includes/config.local.php`, `admin/storage/`, respaldos `.sql`.
- Los archivos subidos (logo, contratos firmados escaneados) viven en `admin/storage/` y no están en Git:
  inclúyelos en los respaldos del hosting.
