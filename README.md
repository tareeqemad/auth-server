# GEDCO SSO — Single Sign-On Identity Provider

نظام SSO مركزي (Identity Provider) لـ **شركة توزيع كهرباء محافظات غزة (GEDCO)** — يخدم 5 أنظمة قائمة بدون المساس بجداولها أو صلاحياتها.

بُني بـ **Laravel 12 + Passport 13 + OIDC** وفق المبدأ: **فصل المصادقة عن التخويل**.

---

## ✨ المميزات

- 🔐 **OAuth 2.0 + OpenID Connect** (Authorization Code + PKCE + RS256 id_tokens)
- 👥 **إدارة مستخدمين مركزية** مع audit logs كامل
- 📱 **SMS 2FA + استعادة كلمة مرور عبر SMS** (Hotsms integration)
- 🎨 **Admin Panel بالعربية** (RTL) — 7 أقسام كاملة CRUD
- 🌐 **Developer Docs** جاهزة لكل نظام عميل (Laravel / CI3 / CI4 / Next.js)
- 📊 **Dashboard للمستخدم** يعرض الأنظمة المرتبطة بحسابه
- 🛡️ **تكامل Spatie Permission** (super_admin, user_manager, client_manager, viewer)
- ⚙️ **إعدادات ديناميكية** قابلة للتعديل من لوحة التحكم (branding, colors, SMS credentials)

---

## 🧩 الأنظمة المستهدفة

| # | النظام | التقنية | DB |
|---|--------|--------|-----|
| 1 | System A | CodeIgniter 3 | MySQL |
| 2 | System B | CodeIgniter 4 | MySQL |
| 3 | System C | CodeIgniter 4 | Oracle |
| 4 | System D | Laravel | MySQL |
| 5 | System E | Next.js + Nest.js | PostgreSQL |

---

## 🏗️ Tech Stack

- **Backend**: Laravel 12, PHP 8.2+, Passport 13
- **Database**: MySQL 5.7+ (أو PostgreSQL)
- **Frontend**: Blade + Tailwind 4 + jQuery + Toastr + SweetAlert2
- **Font**: Tajawal (محلي)
- **SMS**: Hotsms.ps
- **Auth**: OAuth2 + OIDC + PKCE + RS256 JWT

---

## 🚀 Quick Start

### المتطلبات
- PHP 8.2+
- Composer
- MySQL 5.7+ / PostgreSQL 14+
- Node.js 18+
- npm

### التثبيت

```bash
# Clone
git clone https://github.com/tareeqemad/auth-server.git
cd auth-server

# Install dependencies
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Edit .env — set DB credentials + Hotsms credentials

# Database
php artisan migrate:fresh --force

# Passport keys
php artisan passport:keys

# Seed everything
php artisan db:seed --class=SettingsSeeder
php artisan db:seed --class=RolesSeeder
php artisan sso:sync-clients
php artisan db:seed --class=TestUsersSeeder     # optional: test users
php artisan passport:client --personal --name="Personal Access Client"

# Create super admin
php artisan sso:create-admin \
  --email=admin@example.com \
  --name="المدير" \
  --role=super_admin \
  --password=ChangeMe123

# Build frontend
npm run build

# Run
php artisan serve
# → http://localhost:8000
```

### الدخول

- **User**: `http://localhost:8000/login`
- **Admin Panel**: `http://localhost:8000/admin`

---

## 📂 Project Structure

```
auth-server/
├── app/
│   ├── Console/Commands/     # sso:sync-clients, sso:create-admin
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/        # Dashboard, Applications, Users, Audit, Sessions, Settings, Admins
│   │   │   ├── Auth/         # Login, Logout, Reset, SMS Reset, 2FA
│   │   │   ├── OIDC/         # Discovery, JWKS, UserInfo
│   │   │   ├── ProfileController.php
│   │   │   └── TwoFactorSettingsController.php
│   │   └── Middleware/       # RequirePkce, IssueIdToken, EnsureIsAdmin
│   ├── Models/               # User, Application, AuditLog, SsoSession, Setting, SmsOtp, UserSystemLink
│   └── Services/
│       ├── HotsmsService.php
│       └── OIDC/             # JwksService, IdTokenService, ClaimsService
├── config/
│   ├── hotsms.php
│   ├── oidc.php
│   ├── passport.php
│   ├── permission.php
│   └── sso_clients.php       # registry للـ 5 أنظمة
├── database/
│   ├── migrations/
│   └── seeders/              # Settings, Roles, TestUsers
├── resources/
│   └── views/
│       ├── admin/            # لوحة تحكم
│       ├── auth/             # تسجيل دخول + SMS reset + 2FA
│       ├── profile/          # تاب Profile
│       ├── oauth/            # consent page
│       └── dashboard.blade.php
└── routes/
    └── web.php
```

---

## 🔐 الأمان

- **RS256** للـ JWT (asymmetric)
- **PKCE** إجباري (S256 فقط)
- **Client secrets** مُخزّنة hashed
- **SMS 2FA** اختياري للمستخدمين
- **SMS OTP** لاستعادة كلمة المرور
- **Rate limiting** (قيد التطوير في Phase 8)
- **Account lockout** (قيد التطوير في Phase 8)
- **Audit logs** شاملة لكل الأحداث

---

## 🛣️ خريطة الطريق (Roadmap)

### ✅ المُنجز
- Phase 1: DB مركزية
- Phase 2: OIDC Layer
- Phase 3: 5 OAuth Clients + PKCE
- Phase 4: Login/Logout/Reset
- Phase 6: Admin Panel (7 أقسام)
- SMS Integration + 2FA
- Developer Docs

### ⏳ قيد التطوير
- **Phase 8**: Security Hardening (Rate limiting, lockout, password history)
- **Phase 7**: Single Logout (SLO via webhooks)
- **Phase 5**: Migration من الأنظمة الـ 5

راجع [CLAUDE.md](./CLAUDE.md) للتفاصيل الكاملة.

---

## 📚 Developer Docs

لكل نظام عميل، راجع الـ Integration Guide في Admin Panel:
`/admin/applications/{id}/integration`

يحتوي على:
- Client ID + Secret + Endpoints (copy buttons)
- Code snippets جاهزة للنسخ (Laravel, CI3, CI4, Next.js)
- Testing checklist

---

## 📄 License

MIT — لا يوجد ضمان.

---

**Built with ❤️ for GEDCO**
