# Deployment (Ubuntu VPS example)

Cookie auth needs the SPA and API on the **same registrable domain**:

- `app.example.com` serves the static React build
- `api.example.com` serves Laravel (PHP-FPM)

## 1. Server packages
```bash
sudo apt update
sudo apt install nginx mysql-server php-fpm php-mysql php-mbstring php-xml php-curl php-zip unzip git composer
```
Node is only needed where you build the frontend (your machine or CI).

## 2. Database
```sql
CREATE DATABASE admin_starter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'admin_starter'@'localhost' IDENTIFIED BY '<long random password>';
GRANT ALL PRIVILEGES ON admin_starter.* TO 'admin_starter'@'localhost';
```

## 3. API
```bash
cd /var/www && git clone <repo-url> admin-starter && cd admin-starter/api
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate
# edit .env (table below)
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
php artisan admin:create you@example.com "Your Name"     # prompts for a password
php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache
```

| `.env` key | Production value |
|---|---|
| `APP_ENV` / `APP_DEBUG` | `production` / `false` (debug on = leaked secrets in error pages) |
| `APP_URL` | `https://api.example.com` |
| `FRONTEND_URL` | `https://app.example.com` (also the CORS allowed origin) |
| `SANCTUM_STATEFUL_DOMAINS` | `app.example.com` |
| `SESSION_DOMAIN` | `.example.com` (shared by both subdomains) |
| `SESSION_SECURE_COOKIE` | `true` (HTTPS only) |
| `DB_*` | the database and least-privilege user from step 2 |
| `LOG_LEVEL` | `warning` |
| `AUDIT_RETENTION_DAYS` | `365` |
| `MAIL_*` | a real mailer, or remove the forgot-password routes |

## 4. Web
Vite bakes env values in at build time:
```bash
cd web && npm ci && VITE_API_URL=https://api.example.com npm run build
rsync -a --delete dist/ user@server:/var/www/admin-starter/web/dist/
```

## 5. Nginx
`/etc/nginx/sites-available/api`:
```nginx
server {
    listen 80;
    server_name api.example.com;
    root /var/www/admin-starter/api/public;
    index index.php;

    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;   # match your PHP version
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```
`/etc/nginx/sites-available/app`:
```nginx
server {
    listen 80;
    server_name app.example.com;
    root /var/www/admin-starter/web/dist;
    index index.html;

    location / { try_files $uri /index.html; }   # lets React Router handle /users, /roles, ...
}
```
```bash
sudo ln -s /etc/nginx/sites-available/{api,app} /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d api.example.com -d app.example.com    # HTTPS (needed for secure cookies)
```

## 6. Scheduler (audit-log pruning)
`crontab -e` as the web user:
```
* * * * * cd /var/www/admin-starter/api && php artisan schedule:run >> /dev/null 2>&1
```

## 7. Updating
```bash
cd /var/www/admin-starter && git pull
cd api && composer install --no-dev --optimize-autoloader
php artisan down
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force   # picks up new permissions from config/rbac.php
php artisan optimize
php artisan up
# then rebuild and rsync web/ as in step 4
```

## 8. Checklist
- [ ] `APP_DEBUG=false`, HTTPS on both domains
- [ ] Demo seeding disabled (the seeder only creates demo users when `APP_ENV=local`)
- [ ] Database backups scheduled (for example a nightly `mysqldump`)
- [ ] CI is green on the commit being deployed