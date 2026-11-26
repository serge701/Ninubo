# Despliegue de NINUBO (sitio + CRM) en HostGator desde GitHub

- Repositorio: `https://github.com/serge701/Ninubo` (rama `main`, **público**). SSH: `git@github.com:serge701/Ninubo.git`
  (la llave SSH del servidor ya está registrada en la cuenta de GitHub).
- Hosting: HostGator compartido, cPanel, SSH con *jailshell*, servidor web **LiteSpeed**, PHP 8.3 (CLI), **MySQL 5.7**.
- Carpeta del sitio (document root de ninubo.com): `/home2/lizzmald/ninubo.com` (`~/ninubo.com`). Respaldos: `~/respaldos_ninubo/`.
- Base de datos del sitio + CRM: **`lizzmald_ninuboweb`** (usuario `lizzmald_store`). ⚠️ No usar `lizzmald_ninubo`:
  es de otra aplicación (asistencia) y tiene su propia tabla `users`.
- El código no tiene secretos: cada servidor tiene su `includes/config.local.php`, que no está en Git.
- **Desplegado por primera vez el 2026-11-25** (commit `337c18f`). Para actualizar, ir directo a
  [Actualizaciones posteriores](#actualizaciones-posteriores).

## Reglas para pegar comandos en el jailshell

- **Al conectarte**, escribe a mano (no pegues) `bind 'set enable-bracketed-paste off'`: la terminal envuelve lo
  pegado con `^[[200~ … ~` y el jailshell no lo entiende.
- Pegar cada bloque completo; van envueltos en `( set -e … )` para que un error no cierre la sesión SSH.
- `curl` desde el propio servidor puede recibir **409** del filtro anti-bots de HostGator: comprobar esa página en el navegador.
- **No usar `!`** en ningún comando ni texto entre comillas dobles (bash lo toma como *history expansion*).
- `mysqldump` puede mostrar "Access denied; you need the PROCESS privilege … tablespaces": es inofensivo; el
  respaldo es válido si su última línea dice `-- Dump completed`.
- `composer` no existe en el jailshell; **no hace falta**: Dompdf ya va en `admin/vendor/`.

## Primera vez

### 0. Ubicar el sitio y ver qué hay (solo lectura)

```bash
( set -e
SITE=$(ls -d ~/ninubo.com ~/public_html/ninubo.com 2>/dev/null | head -1); echo "SITE=$SITE"
cd "$SITE"; ls -la | head -50
git status 2>&1 | head -2
php -v | head -1; mysql --version
grep -n "define('NINUBO" includes/config.php | sed -E "s/(NINUBO_(SECRET|ADMIN_PASSWORD_HASH)'[^,]*,).*/\1 <oculto>);/"
grep -nE '\$(host|name|user|pass) *=' includes/db.php | sed -E 's/(\$pass *=).*/\1 <oculto>;/'
grep -n "SetEnv" .htaccess || echo "sin SetEnv en .htaccess"
)
```

### 1. Respaldo de archivos y base de datos

```bash
( set -e
SITE=~/ninubo.com; mkdir -p ~/respaldos_ninubo; cd ~/respaldos_ninubo
STAMP=$(date +%F_%H%M)
tar -czf "sitio_${STAMP}.tar.gz" -C "$(dirname "$SITE")" "$(basename "$SITE")"
cp "$SITE/includes/config.php" "config.php.pre-git"
cp "$SITE/includes/db.php" "db.php.pre-git"
mysqldump -u USUARIO_DB -p NOMBRE_DB > "db_${STAMP}.sql" || true
tail -1 "db_${STAMP}.sql"; ls -lh
)
```

### 2. Conectar la carpeta con GitHub

```bash
( set -e
cd ~/ninubo.com
git init -q
git remote add origin git@github.com:serge701/Ninubo.git
git fetch -q origin
git checkout -f -B main origin/main      # reemplaza los archivos del repo; lo que no está en Git se conserva
git log --oneline -1
git status --short --untracked-files=all | head -40   # archivos viejos que ya no son del proyecto
)
```

Archivos del panel anterior que quedan sueltos y **hay que borrar** (ya no existen en el proyecto):

```bash
( set -e
cd ~/ninubo.com
rm -f admin/login.php admin/logout.php admin/brief-ver.php includes/admin-auth.php
git status --short --untracked-files=all | head -40
)
```

### 3. Configuración del servidor (sin escribir secretos a mano)

Lee la clave, el hash del panel anterior y la contraseña de MySQL de los archivos respaldados en el paso 1:

```bash
( set -e
cd ~/ninubo.com
php admin/scripts/make-config-local.php --config="$HOME/respaldos_ninubo/config.php.pre-git" --db="$HOME/respaldos_ninubo/db.php.pre-git" --base=
php -l includes/config.local.php
mkdir -p admin/storage/uploads admin/storage/contracts admin/storage/cache admin/storage/logs
chmod -R 775 admin/storage
)
```

Si la base de datos usaba variables de entorno del panel (`SetEnv` en `.htaccess`), editar
`includes/config.local.php` con `nano` y poner los valores `NINUBO_DB_*` correctos.

### 4. Verificar

```bash
( set -e
for p in / /contacto.php /admin/login /robots.txt; do printf "%-22s %s\n" "$p" "$(curl -s -o /dev/null -w '%{http_code}' "https://ninubo.com$p")"; done
for p in /.git/config /includes/config.local.php /admin/app/routes.php /admin/vendor/autoload.php /DEPLOY.md /sql/brief.sql; do printf "%-32s %s (debe ser 403/404)\n" "$p" "$(curl -s -o /dev/null -w '%{http_code}' "https://ninubo.com$p")"; done
)
```

### 5. Crear el administrador del CRM

Abrir `https://ninubo.com/admin/` → **Set up Ninubo CRM** → contraseña del panel anterior + datos del nuevo
usuario administrador. Se crean todas las tablas del CRM.

Después, en el CRM: **Business settings** (nombre legal, dirección, teléfono, logo, permiso de sales tax,
firmante), **Catalog** (precios y costo de producción de cada paquete — el costo no está en el repositorio),
**Users** (vendedores).

## Actualizaciones posteriores

Un solo bloque: respalda la base y los archivos subidos, trae el código y aplica migraciones pendientes.
Lee las credenciales de `includes/config.local.php`, así que no pide ni muestra contraseñas.

```bash
( set -e
cd ~/ninubo.com
STAMP=$(date +%F_%H%M)
H=$(php -r 'require "includes/config.local.php"; echo NINUBO_DB_HOST;')
U=$(php -r 'require "includes/config.local.php"; echo NINUBO_DB_USER;')
N=$(php -r 'require "includes/config.local.php"; echo NINUBO_DB_NAME;')
export MYSQL_PWD=$(php -r 'require "includes/config.local.php"; echo NINUBO_DB_PASS;')
mysqldump -h "$H" -u "$U" "$N" > ~/respaldos_ninubo/db_${STAMP}.sql || true
tail -1 ~/respaldos_ninubo/db_${STAMP}.sql
tar -czf ~/respaldos_ninubo/storage_${STAMP}.tar.gz admin/storage
git pull -q --ff-only origin main
git log --oneline -1
php admin/scripts/migrate.php
git status --short --untracked-files=all
)
```

Debe terminar con `-- Dump completed`, el commit nuevo y `Up to date.` (o la lista de migraciones aplicadas).
También se pueden aplicar desde el CRM: al abrir `https://ninubo.com/admin/` aparece **"Database update required"**.

## Notas

- **Correo**: los avisos usan `mail()` de PHP; si no llegan, configurar SMTP.
- **Nunca** subir a Git: `includes/config.local.php`, `admin/storage/`, respaldos `.sql`.
- Los archivos subidos (logo, contratos firmados) viven en `admin/storage/`: inclúyelos en los respaldos.
- Errores de PHP del CRM: `admin/storage/logs/php-error.log`.
