# دليل نشر SSO IdP — GEDCO

هذا الدليل يشرح نشر الـ IdP على Linux (production) وعلى Windows (للتطوير والتجربة الداخلية).

---

## 📋 المتطلبات

| العنصر | الإصدار المُوصى به |
|--------|---------------------|
| PHP | 8.2+ (مع extensions: pdo_mysql, openssl, mbstring, tokenizer, xml, ctype, json, bcmath, fileinfo, curl, gd) |
| MySQL | 8.0+ (أو MariaDB 10.6+) |
| Node.js | 20+ (للبناء فقط — لا يحتاج في الإنتاج) |
| Composer | 2.6+ |
| Web server | Nginx 1.22+ أو Apache 2.4+ |
| SSL | Let's Encrypt أو شهادة مؤسسية |

---

## 🐧 النشر على Linux (Production)

### 1) نقل الكود

```bash
cd /var/www
sudo git clone https://github.com/gedco/auth-server.git
sudo chown -R www-data:www-data auth-server
cd auth-server
```

### 2) تثبيت الاعتماديات

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
# بعد البناء يمكن إزالة node_modules لتوفير المساحة
rm -rf node_modules
```

### 3) إعداد `.env`

```bash
cp .env.example .env
php artisan key:generate --force
```

عدّل القيم التالية:

```ini
APP_NAME="GEDCO SSO"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sso.gedco.ps
APP_TIMEZONE=Asia/Gaza

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=auth_server
DB_USERNAME=sso_user
DB_PASSWORD=strong-password-here

# Session security (HTTPS-only)
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_HTTP_ONLY=true

# HTTPS
FORCE_HTTPS=true
# إذا كنت خلف load balancer/reverse proxy:
TRUSTED_PROXIES="*"

# Queue للـ back-channel logout (ضروري)
QUEUE_CONNECTION=database

# Mail (لإرسال reset email)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gedco.ps
MAIL_PORT=587
MAIL_USERNAME=no-reply@gedco.ps
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@gedco.ps

# Hotsms credentials (تُخزّن في settings الآن لكن يمكن override)
HOTSMS_USERNAME=E-SER-GEDCO
HOTSMS_PASSWORD=...
HOTSMS_SENDER=E-SER-GEDCO

# Monitoring
TELESCOPE_ENABLED=true
LOG_CHANNEL=daily
LOG_LEVEL=warning
```

### 4) الـ Database + Passport keys

```bash
php artisan migrate --force
php artisan db:seed --class=SettingsSeeder --force
php artisan db:seed --class=RolesSeeder --force

# ⚠️ في الإنتاج: أنشئ الـ passport keys مرة واحدة فقط
php artisan passport:keys
# تأكد أن المفاتيح ليست في git:
echo "storage/oauth-*.key" >> .gitignore

php artisan sso:sync-clients
```

> **تحذير:** لا تُعد تشغيل `passport:keys --force` في الإنتاج — سيُبطل كل access tokens + refresh tokens الحالية.

### 5) الصلاحيات

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo chmod 600 storage/oauth-private.key
sudo chmod 644 storage/oauth-public.key
```

### 6) التخزين المؤقت (cache) — اختياري لكنه مُوصى به للإنتاج

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 7) أنشئ super admin

```bash
php artisan sso:create-admin --email=admin.panel@gedco.ps --name="مدير النظام" --role=super_admin
```

---

## 🌐 Nginx Configuration

```nginx
# /etc/nginx/sites-available/sso.gedco.ps
server {
    listen 80;
    server_name sso.gedco.ps;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name sso.gedco.ps;
    root /var/www/auth-server/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/sso.gedco.ps/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/sso.gedco.ps/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-Frame-Options SAMEORIGIN always;
    add_header Referrer-Policy strict-origin-when-cross-origin always;

    client_max_body_size 10M;
    gzip on;
    gzip_types text/plain text/css application/json application/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param HTTPS on;
        fastcgi_read_timeout 60;
    }

    location ~ /\.(?!well-known) { deny all; }
    location ~ /storage/oauth-.*\.key$ { deny all; }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/sso.gedco.ps /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d sso.gedco.ps
```

---

## ⚙️ Queue Worker Daemon

### Option A: Supervisor (Linux — الأفضل)

```bash
sudo apt install supervisor
sudo nano /etc/supervisor/conf.d/auth-server-queue.conf
```

```ini
[program:auth-server-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/auth-server/artisan queue:work --tries=3 --max-time=3600 --sleep=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/auth-server-queue.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start auth-server-queue:*
sudo supervisorctl status
```

### Option B: systemd

```bash
sudo nano /etc/systemd/system/auth-server-queue.service
```

```ini
[Unit]
Description=Auth Server Queue Worker
After=network.target mysql.service

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
RestartSec=5
WorkingDirectory=/var/www/auth-server
ExecStart=/usr/bin/php artisan queue:work --tries=3 --max-time=3600 --sleep=3

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable auth-server-queue
sudo systemctl start auth-server-queue
sudo systemctl status auth-server-queue
```

### Scheduler (cron)

```bash
sudo crontab -u www-data -e
```

```cron
* * * * * cd /var/www/auth-server && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🪟 النشر على Windows (XAMPP — للتطوير)

### 1) المتطلبات
- XAMPP 8.2+ (PHP + Apache + MySQL)
- Git for Windows
- Node.js 20+
- Composer

### 2) الإعداد

```powershell
# في PowerShell / CMD (Administrator)
cd C:\Users\PC\Desktop
git clone https://github.com/gedco/auth-server.git
cd auth-server
composer install
npm install
npm run build

copy .env.example .env
php artisan key:generate
```

### 3) تكوين Apache virtual host
في `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
```apache
<VirtualHost *:8005>
    ServerName sso.local
    DocumentRoot "C:/Users/PC/Desktop/auth-server/public"
    <Directory "C:/Users/PC/Desktop/auth-server/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
Listen 8005
```

في `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1   sso.local
```

### 4) Queue worker كخدمة Windows

**الطريقة الأسهل: NSSM (Non-Sucking Service Manager)**

```powershell
# Download NSSM from https://nssm.cc/download
# ضعه في C:\nssm\nssm.exe

C:\nssm\nssm.exe install AuthServerQueue "C:\xampp\php\php.exe"
# في الحوار الذي يظهر:
# Path: C:\xampp\php\php.exe
# Startup directory: C:\Users\PC\Desktop\auth-server
# Arguments: artisan queue:work --tries=3 --max-time=3600 --sleep=3

# أو عبر CLI:
C:\nssm\nssm.exe install AuthServerQueue ^
    "C:\xampp\php\php.exe" ^
    "C:\Users\PC\Desktop\auth-server\artisan queue:work --tries=3 --max-time=3600 --sleep=3"
C:\nssm\nssm.exe set AuthServerQueue AppDirectory "C:\Users\PC\Desktop\auth-server"
C:\nssm\nssm.exe set AuthServerQueue AppStdout "C:\Users\PC\Desktop\auth-server\storage\logs\queue-worker.log"
C:\nssm\nssm.exe set AuthServerQueue AppStderr "C:\Users\PC\Desktop\auth-server\storage\logs\queue-worker-err.log"
C:\nssm\nssm.exe set AuthServerQueue Start SERVICE_AUTO_START

net start AuthServerQueue
```

**بديل: Task Scheduler**
- افتح Task Scheduler
- Create Task
- Trigger: At startup
- Action: Start a program
  - Program: `C:\xampp\php\php.exe`
  - Arguments: `artisan queue:work --tries=3 --max-time=3600`
  - Start in: `C:\Users\PC\Desktop\auth-server`
- Settings: "Run whether user is logged on or not"

---

## ✅ Checklist ما قبل النشر

### الأمان
- [ ] `APP_ENV=production` و `APP_DEBUG=false`
- [ ] `FORCE_HTTPS=true` في `.env`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `TRUSTED_PROXIES` مضبوط إذا خلف load balancer
- [ ] SSL certificate صالح + HSTS header
- [ ] Passport keys في `storage/` مع permissions 600
- [ ] لا يوجد `.env` في git (استخدم `.env.example`)
- [ ] Database password قوي + user محدود الصلاحيات
- [ ] Firewall يسمح فقط بـ 80/443 للعامة
- [ ] Rate limiters فعّالة (Redis cache موصى به في production)

### الوظائف
- [ ] Super admin موجود + 2FA مُفعّل عليه
- [ ] SMS Gateway credentials في settings صحيحة
- [ ] Email SMTP يعمل (جرّب forgot-password)
- [ ] 5 OAuth clients متصلون بأنظمتهم
- [ ] Back-Channel Logout URLs مُسجَّلة لكل client
- [ ] Queue worker يعمل (جرّب logout + راقب logs)

### الأداء
- [ ] `config:cache`, `route:cache`, `view:cache`, `event:cache`
- [ ] OPcache مُفعّل في PHP (production.ini)
- [ ] Redis/Memcached للـ cache (اختياري لكن موصى به)
- [ ] Log rotation مُكوَّن (`storage/logs/`)
- [ ] Monitoring (Telescope auth-protected، أو Sentry/Bugsnag)

---

## 🔄 التحديثات (deploy pipeline)

```bash
#!/usr/bin/env bash
# deploy.sh — تشغيله من CI/CD أو يدوياً
set -e

cd /var/www/auth-server

php artisan down
git pull --ff-only
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
sudo supervisorctl restart auth-server-queue:*
php artisan up
```

---

## 🆘 استكشاف المشاكل

| المشكلة | الحل |
|---------|------|
| `Mixed content` على HTTPS | تأكد أن `FORCE_HTTPS=true` + `TRUSTED_PROXIES="*"` |
| back-channel logout لا يُرسل | تحقق `php artisan queue:work` يعمل + راجع `storage/logs/laravel.log` |
| `419 Page Expired` | Session cookie حجمه كبير — استخدم `SESSION_DRIVER=database` |
| `The redirect URI is invalid` | تحقق redirect_uri في applications مطابق 100٪ (https/trailing slash) |
| SMS لا يُرسل | تحقق من `/admin/settings` — Hotsms credentials + الرصيد |
| JWKS لا يُحمَّل من client | تأكد `openssl` extension مُفعّل + مفاتيح الـ Passport صحيحة |

---

## 📞 للمساعدة

- Logs: `storage/logs/laravel.log`
- Failed jobs: `php artisan queue:failed` ثم `php artisan queue:retry all`
- Audit: `/admin/audit-logs`
- User lookup: `/admin/users/{id}` (صفحة تفصيلية)
