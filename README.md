# Voidworks Studio CMS

CMS modular construido con Laravel, Blade, Tailwind CSS y Vite. Incluye sitio público y administración autenticada para contenido, usuarios, configuración y auditoría.

## Stack del proyecto

- Laravel **12.69.2** (dependencia `laravel/framework: ^12.0`).
- PHP **8.2 o superior** según `composer.json`. El entorno de desarrollo comprobado usa PHP 8.2.12; Laravel 12 admite PHP 8.2–8.5.
- MySQL con PDO MySQL habilitado.
- Blade, Tailwind CSS 4 y Vite 7.
- Node.js **20.19+ o 22.12+** y pnpm. El repositorio incluye `pnpm-lock.yaml`; usa pnpm para respetar las versiones fijadas.

En Windows con XAMPP, inicia MySQL desde el panel de control. Apache no es necesario si ejecutas la aplicación con `php artisan serve`. Asegúrate de que PHP de XAMPP y Composer estén disponibles en PowerShell (`php -v` y `composer --version`).

## Preparación local (Windows / XAMPP)

Ejecuta los comandos desde la raíz del proyecto.

### 1. Instalar dependencias PHP

```powershell
composer install
```

### 2. Crear y completar `.env`

Si todavía no existe `.env`:

```powershell
Copy-Item .env.example .env
```

Crea una base de datos local en MySQL, por ejemplo `voidworks_cms_local`, con charset `utf8mb4`. En producción, la cuenta de la aplicación debe tener permisos limitados a su propia base de datos; evita usar `root`.

Edita únicamente `.env` y configura tus valores locales:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=America/Bogota

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=voidworks_cms_local
DB_USERNAME=tu_usuario_local
DB_PASSWORD=tu_contrasena_local

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=false
```

No pegues credenciales en este README ni en `.env.example`. `.env` está excluido de Git. En un entorno local HTTP, `SESSION_SECURE_COOKIE=false` permite enviar la cookie de sesión; en producción con HTTPS debe ser `true`.

Genera una clave de aplicación:

```powershell
php artisan key:generate
```

### 3. Instalar dependencias frontend

Activa Corepack si pnpm aún no está disponible y luego instala desde el lockfile:

```powershell
corepack enable
pnpm install --frozen-lockfile
```

No combines pnpm y npm para instalar dependencias en el mismo checkout: este proyecto versiona `pnpm-lock.yaml`.

### 4. Crear tablas y enlace de archivos

```powershell
php artisan migrate
php artisan storage:link
```

Las migraciones usan la conexión definida en `.env`. Si MySQL no conecta, comprueba que el servicio esté iniciado, que la base exista y que `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` correspondan a tu instalación local.

### 5. Crear la primera cuenta administrativa

El CMS no ofrece registro público y no crea credenciales predeterminadas. Crea la primera cuenta Super Admin desde Tinker:

```powershell
php artisan tinker
```

En Tinker, ejecuta:

```php
$user = new \App\Models\User();
$user->name = 'Super Admin';
$user->email = 'admin@localhost.test';
$user->password = \Illuminate\Support\Facades\Hash::make(readline('Contraseña inicial: '));
$user->role = 'super_admin';
$user->status = 'active';
$user->save();
```

La contraseña se solicita de forma interactiva; no la escribas en el código ni la guardes en el repositorio. Sal de Tinker con `exit`.

### 6. Iniciar la aplicación y Vite

Abre dos terminales en la raíz del proyecto.

Terminal 1 — servidor Laravel:

```powershell
php artisan serve
```

Terminal 2 — servidor de desarrollo Vite:

```powershell
pnpm dev
```

Abre <http://127.0.0.1:8000>. La administración está en <http://127.0.0.1:8000/admin> y el inicio de sesión en <http://127.0.0.1:8000/login>. Vite está configurado en `127.0.0.1:5173`; mantén la terminal de Vite activa para cargar estilos y JavaScript durante el desarrollo.

Para compilar los assets sin servidor Vite:

```powershell
pnpm build
```

Si Corepack no reconoce `pnpm`, habilítalo una vez desde una terminal con permisos para modificar los shims de Node.js:

```powershell
corepack enable
```

## Pruebas

```powershell
php artisan test
```

La configuración de PHPUnit usa SQLite en memoria, por lo que la suite no requiere alterar la base de datos MySQL local. La extensión PDO SQLite debe estar habilitada para PHP CLI.

## Rutas principales

- Sitio público: `/`.
- Inicio de sesión: `/login`.
- Administración protegida: `/admin`.
- Banners: `/admin/banners`.
- Servicios: `/admin/services` y `/servicios`.
- Publicaciones: `/admin/posts` y `/noticias`.
- Multimedia: `/admin/media`.
- Contacto: `/contacto`.
- Configuración y auditoría: disponibles desde la administración según permisos.

El acceso se controla en el servidor mediante autenticación, estado de cuenta, permisos y Policies. Ocultar enlaces en el menú no sustituye la autorización de las rutas.

## Configuración de correo

El CMS permite configurar SMTP desde el panel administrativo cuando la cuenta tiene permisos. Para recuperación de contraseña y notificaciones locales, configura SMTP en el panel o usa un servicio de captura de correo de desarrollo. El mailer `log` del `.env.example` no entrega correos: escribe los mensajes en los logs.

## Notas de seguridad y operación

- No subas `.env`, contraseñas, `APP_KEY`, claves API ni credenciales SMTP al repositorio.
- Usa `.env.example` solo como plantilla; completa las credenciales locales en `.env`.
- Mantén `APP_DEBUG=false` y HTTPS en producción; configura `SESSION_SECURE_COOKIE=true` bajo HTTPS.
- No uses una cuenta MySQL administrativa como usuario permanente de la aplicación.
- Después de cambiar configuración en un despliegue con caché, ejecuta `php artisan config:clear` y vuelve a generar la caché de configuración como parte del proceso de despliegue.

## Documentación de referencia

- [Laravel 12](https://laravel.com/docs/12.x)
- [Vite](https://vite.dev/guide/)
