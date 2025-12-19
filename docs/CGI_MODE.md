# SwordPHP - Modo CGI Tradicional

Esta documentación explica cómo instalar y ejecutar SwordPHP en modo CGI tradicional, compatible con cualquier hosting que soporte PHP.

## Requisitos Mínimos

| Requisito       | Versión Mínima                           |
| --------------- | ---------------------------------------- |
| PHP             | 8.1+                                     |
| PostgreSQL      | 12+                                      |
| Extensiones PHP | pdo, pdo_pgsql, json, mbstring, fileinfo |

## Ventajas del Modo CGI

- ✅ Compatible con **shared hosting** (cPanel, Plesk)
- ✅ No requiere procesos en segundo plano
- ✅ Funciona con Apache, Nginx, LiteSpeed
- ✅ Cambios reflejados inmediatamente
- ✅ Sin configuración especial de servidor

## Instalación en Shared Hosting

### 1. Subir Archivos

Sube todo el contenido del proyecto a tu hosting. La estructura debería quedar:

```
public_html/
├── public/           ← Este debe ser el document root
│   ├── index.php
│   ├── .htaccess
│   └── admin/
├── app/
├── config/
├── vendor/
├── .htaccess
└── ...
```

### 2. Configurar Document Root

**Opción A - Hosting permite cambiar Document Root:**
Apunta el dominio a la carpeta `public/`

**Opción B - No se puede cambiar Document Root:**
El `.htaccess` raíz redirigirá automáticamente a `public/`

### 3. Configurar Base de Datos

Edita el archivo `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=tu_base_de_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

### 4. Permisos de Carpetas

```bash
chmod -R 755 .
chmod -R 777 runtime/
chmod -R 777 public/uploads/
```

### 5. Instalar Base de Datos

Accede a `/system/install` via POST o usa la interfaz de admin.

---

## Instalación en VPS/Servidor

### Apache

1. Copia el archivo `.htaccess` incluido
2. Asegúrate de que `mod_rewrite` esté habilitado:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```
3. Configura el VirtualHost:
   ```apache
   <VirtualHost *:80>
       ServerName tudominio.com
       DocumentRoot /var/www/swordphp/public
       
       <Directory /var/www/swordphp/public>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

### Nginx

1. Copia `nginx.conf.example` a `/etc/nginx/sites-available/swordphp`
2. Ajusta las rutas y dominio
3. Crea symlink y reinicia:
   ```bash
   sudo ln -s /etc/nginx/sites-available/swordphp /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl restart nginx
   ```

---

## Diferencias con Modo Webman

| Aspecto           | Modo CGI    | Modo Webman                |
| ----------------- | ----------- | -------------------------- |
| Rendimiento       | Estándar    | Alto (proceso persistente) |
| Compatibilidad    | Universal   | Requiere acceso CLI        |
| Cambios de código | Inmediatos  | Requiere reinicio          |
| Memoria           | Por request | Compartida                 |
| Ideal para        | CMS público | API headless               |

## Solución de Problemas

### Error 500 al acceder

1. Verifica permisos de `runtime/`
2. Revisa `runtime/logs/` para errores
3. Asegúrate de que PHP tiene las extensiones necesarias

### Página en blanco

1. Habilita errores en `.env`: `APP_DEBUG=true`
2. Revisa el log de PHP del servidor

### Sesiones no funcionan

1. Verifica que `runtime/sessions/` existe y es escribible
2. Comprueba configuración de PHP: `session.save_path`

### Rutas no funcionan

1. Verifica que `mod_rewrite` está habilitado (Apache)
2. Comprueba que `.htaccess` está siendo leído
3. En Nginx, asegúrate de que `try_files` apunta a `index.php`

---

## Estructura de Archivos CGI

```
app/support/
├── cgi_bootstrap.php     # Inicialización para CGI
├── cgi_helpers.php       # Funciones helper CGI
├── CgiRequest.php        # Wrapper de Request
├── CgiResponse.php       # Wrapper de Response
├── CgiRouter.php         # Router compatible
├── CgiSession.php        # Manejo de sesiones
└── Environment.php       # Detección de modo

public/
├── index.php             # Punto de entrada CGI
├── .htaccess             # Reglas Apache
└── ...
```

---

## Comandos Útiles

Estos comandos requieren acceso SSH/CLI:

```bash
# Verificar configuración PHP
php -m | grep -E "pdo|pgsql|json|mbstring"

# Probar conexión a BD
php -r "new PDO('pgsql:host=localhost;dbname=sword', 'user', 'pass');"

# Limpiar caché (si aplica)
rm -rf runtime/views/*
```

---

## Soporte

Para problemas específicos del modo CGI, abre un issue en GitHub con:
- Versión de PHP (`php -v`)
- Servidor web (Apache/Nginx)
- Tipo de hosting (shared/VPS/cloud)
- Logs relevantes
