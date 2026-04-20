# SSO Authentication Server - Project Instructions

## 🎯 Project Overview

بناء **SSO (Single Sign-On) Identity Provider** مركزي يخدم 5 أنظمة قائمة بدون التأثير على:
- جداول المستخدمين الحالية في كل نظام
- منظومة الصلاحيات والأدوار في كل نظام
- البيانات الموجودة

### المبدأ المعماري الأساسي
**فصل المصادقة عن التخويل (Separation of Authentication and Authorization)**
- **IdP المركزي**: يتحقق فقط من هوية المستخدم (من أنت؟)
- **كل نظام**: يحتفظ بصلاحياته وأدواره محلياً (ماذا يمكنك فعله؟)

---

## 🏗️ Tech Stack

### IdP Server (هذا المشروع)
- **Framework**: Laravel 12
- **Language**: PHP 8.2+
- **Database**: **MySQL** (XAMPP) — تم الانتقال من PostgreSQL
- **OAuth/OIDC**: Laravel Passport 13
- **Frontend**: Blade + Tailwind 4 + jQuery + Toastr + SweetAlert2
- **Font**: Tajawal (محلي عبر @fontsource)
- **SMS Gateway**: Hotsms (hotsms.ps) — `E-SER-GEDCO`
- **Environment**: Windows (XAMPP) للتطوير

### Client Systems (الأنظمة المستهدفة للتكامل)
| # | النظام | التقنية | قاعدة البيانات | اللون |
|---|--------|----------|----------------|------|
| 1 | System A | CodeIgniter 3 | MySQL | #2563eb |
| 2 | System B | CodeIgniter 4 | MySQL | #059669 |
| 3 | System C | CodeIgniter 4 | Oracle | #d97706 |
| 4 | System D | Laravel | MySQL | #dc2626 |
| 5 | System E | Next.js + Nest.js | PostgreSQL | #7c3aed |

**ملاحظات مهمة:**
- الأنظمة تعمل منذ 3 سنوات تقريباً
- قد يوجد مستخدمون مشتركون بين الأنظمة (نفس الـ email)
- كل نظام له جدول `users` وجدول صلاحيات خاص به

---

## 🎨 الهوية البصرية (GEDCO)

- **الشركة**: شركة توزيع كهرباء محافظات غزة
- **النظام**: النظام الموحّد
- **الشعار**: "نصمد... لنُضيء"
- **Logo**: `https://gedco.ps/assets/site/images/logos/logo-white.webp`
- **اللون الأساسي**: `#0F2440` (navy)
- **اللون الثانوي**: `#F97316` (orange) → `#FBBF24` (amber gradient)
- **الهاتف**: `+970 566 700 055`
- **البريد**: `support@gedco.ps`

جميع هذه القيم مخزّنة في جدول **`settings`** (قابلة للتعديل من Admin Panel).

---

## ✅ الإنجاز الشامل (جميع المراحل الرئيسية)

### ✅ Phase 1: قاعدة البيانات المركزية
- `users` (UUID v7 + HasUuids + SoftDeletes + HasApiTokens)
- `audit_logs` (15 event types)
- `sso_sessions` (لـ SLO)
- `user_system_links` (ربط user بـ 5 أنظمة)
- Passport migrations مُعدَّلة لـ UUID
- **Later additions**: `settings`, `sms_otps`, `permissions` tables (Spatie), metadata on `oauth_clients`, `sms_2fa_enabled` + `sms_2fa_enabled_at` on users

### ✅ Phase 2: OIDC Layer (بناء يدوي)
- `GET /.well-known/openid-configuration`
- `GET /.well-known/jwks.json` (RSA kid + n + e)
- `GET|POST /oauth/userinfo` (auth:api)
- `id_token` (RS256) تلقائي عبر `IssueIdToken` middleware
- Claims: iss, sub, aud, exp, iat, auth_time, nonce + email/name/phone

### ✅ Phase 3: تسجيل الأنظمة الخمسة
- `config/sso_clients.php` + `sso:sync-clients` artisan command
- 5 OAuth Clients (authorization_code + refresh_token)
- `RequirePkce` middleware — S256 إجباري
- Client secrets hashed

### ✅ Phase 4: Login + Logout + Reset Password
- Login page بتصميم GEDCO (dark + glass morphism + logo)
- AJAX login عبر jQuery
- SSO Sessions (8h TTL) + audit logs
- Forgot/Reset عبر Email (Laravel Password broker)
- **Forgot Password عبر SMS** (3 خطوات: phone → OTP → new password)
- Custom consent page بـ Passport::authorizationView

### ✅ Phase 6: Admin Panel كامل (مقدّم عن الترتيب الأصلي)
**7 أقسام** على `/admin/*`:
1. **Dashboard** — 6 stat cards + recent logins
2. **Applications** (Clients CRUD) — قائمة + إنشاء + تعديل + حذف + rotate secret + toggle revoke + **Integration Docs page**
3. **Users** — قائمة + CRUD + toggle active + reset password
4. **Audit Logs** — قائمة + فلاتر (بحث/نوع/تاريخ)
5. **Sessions** — نشطة + force revoke
6. **Settings** — 4 أقسام (branding, contact, security, sms) + SMS balance + test
7. **Admins & Roles** — قائمة + تغيير دور inline
- Spatie Permission مُكيّف لـ UUID (4 أدوار: super_admin, user_manager, client_manager, viewer)
- أمر `sso:create-admin` لإنشاء مدراء

### ✅ User-facing
- **Dashboard** (`/dashboard`) — header داكن + systems grid بألوان + profile mini cards
- **Profile** (`/profile/*`) — 5 tabs:
  1. بياناتي (تعديل الاسم/الهاتف/البريد)
  2. كلمة المرور (تغيير مع تأكيد current password)
  3. **الأمان** (تفعيل/إيقاف SMS 2FA)
  4. جلساتي (عرض + إنهاء فردي/كل الجلسات)
  5. سجل نشاطي (audit log الشخصي)

### ✅ SMS Integration (Hotsms)
- `App\Services\HotsmsService` — send + balance + phone normalization
- **Forgot Password via SMS** (3-step flow)
- **SMS 2FA للـ login** (intercepts after password)
- Admin UI في settings: اسم المستخدم + كلمة المرور + اسم المرسل + الرصيد الحي + "Test SMS" button
- بيانات Hotsms مخزّنة في `settings` (قابلة للتعديل بدون `.env`)

### ✅ Developer Docs (for integration)
- صفحة `/admin/applications/{id}/integration` لكل نظام
- Client ID + Secret + جميع الـ endpoints (copy buttons)
- 4 code snippets جاهزة: Laravel, CodeIgniter 3, CodeIgniter 4, Next.js
- Testing checklist (8 بنود)
- Flow diagram (Authorization Code + PKCE)

---

## ⏳ المتبقّي (حسب الأولوية)

### 🔴 Phase 8: Security Hardening (المقترح البدء به)
- [ ] Rate limiting على `/login`, `/forgot-password/sms/send` (Laravel `throttle` middleware)
- [ ] Account lockout بعد 5 محاولات فاشلة (`locked_until` column في users)
- [ ] Password history (جدول جديد `password_histories` — منع آخر 5)
- [ ] CSRF protection (موجود افتراضياً في Laravel)
- [x] PKCE إجباري (✅ تم في Phase 3)
- [x] Audit logging شامل (✅ تم)
- [ ] HTTPS enforcement في الإنتاج

### 🟡 Phase 7: Single Logout (SLO)
- [ ] جدول `sso_session_clients` (pivot) — تتبع أي client نشط في كل session
- [ ] عند login لنظام عميل → إضافة row
- [ ] عند logout في IdP → webhook/HTTP POST لكل client للـ logout endpoint الخاص به
- [ ] كل نظام عميل يحتاج endpoint `/sso/back-channel-logout` (يُنظّف session المحلية)

### 🟡 Phase 5: ترحيل المستخدمين من 5 قواعد بيانات
- [ ] `App\Services\MigrationService` مع adapters:
  - MySqlAdapter (System A, B, D)
  - OracleAdapter (System C) — يحتاج oci8 extension
  - PostgreSqlAdapter (System E)
- [ ] Admin UI لتكوين credentials لكل system source
- [ ] قراءة users → دمج بالـ email → تعبئة user_system_links
- [ ] معالجة password hashes:
  - bcrypt/argon2 → نقل مباشر (Laravel يتعرف عليها)
  - MD5/SHA1 → إجبار reset (حقل `must_change_password` يحتاج إضافة)
- [ ] تقرير: كم نُقل، كم تكرر، كم يحتاج reset

### 🟢 اختياري (nice-to-have)
- [ ] عرض تفصيلي للمستخدم في admin (profile + audit + linked systems في صفحة واحدة)
- [ ] Export CSV للـ audit logs + users
- [ ] Charts على admin dashboard
- [ ] Queue workers للـ SMS/emails (حالياً sync)
- [ ] Feature/Integration tests
- [ ] Production deployment guide
- [ ] Monitoring (Telescope موجود لكن مش مُفعّل للإنتاج)

---

## 🔐 بيانات اختبار (للتطوير فقط)

### Super Admin (لوحة التحكم)
```
البريد: admin.panel@gedco.ps
كلمة المرور: AdminPass2026
الرابط: /admin
```

### Users (كلمة المرور للكل: `password123`)
| البريد | الاسم | الحالة | أنظمة |
|--------|------|-------|------|
| admin@example.com | مدير النظام | ✓ نشط | 5 |
| ahmed@example.com | أحمد محمد | ✓ نشط | 2 |
| sara@example.com | سارة علي | ✓ نشط | 2 |
| fatma@example.com | فاطمة حسن | ✓ نشط | 0 |
| khaled@example.com | خالد المعطّل | ❌ معطّل | 1 |
| ibrahim@example.com | إبراهيم العطية | ✓ نشط | 1 |
| test@example.com | Test User | ✓ نشط | 0 |

### Hotsms API
```
User: E-SER-GEDCO
Password: 6770585
Sender: E-SER-GEDCO
URL: http://hotsms.ps
```

### Client Secrets الـ 5 (ظهرت مرة واحدة — للتدوير: `php artisan sso:sync-clients --rotate-secrets`)
- System A: `019d9f4a-d64d-73ca-bfa7-a9bad9d5c651`
- System B: `019d9f4a-d7e7-723d-adfd-bdf3e6533f2f`
- System C: `019d9f4a-d966-711a-9b58-56d52bd14ba5`
- System D: `019d9f4a-daec-7153-943e-9cef9d506d3e`
- System E: `019d9f4a-dc82-738c-ade9-b207142258b7`

---

## 🔒 Security Principles (مهم جداً)

1. **لا ترسل الصلاحيات من الـ IdP**: الـ IdP يرسل فقط هوية المستخدم (sub, email, name). كل نظام يقرأ صلاحياته من قاعدته المحلية.
2. **RS256 دائماً**: RSA asymmetric keys للـ JWT.
3. **Access tokens قصيرة العمر**: 15-60 دقيقة.
4. **Refresh tokens**: قابلة للإبطال + rotation.
5. **Authorization codes**: مرة واحدة، 60 ثانية.
6. **PKCE**: إجباري لكل الـ clients (S256).
7. **Client secrets**: hashed في DB.
8. **HTTPS فقط** في الإنتاج.

---

## 🛠️ Useful Commands

```bash
# Development
php artisan serve
npm run dev        # Vite watch
npm run build      # Production build

# Routes + Migrations
php artisan route:list --path=admin
php artisan route:list --path=oauth
php artisan route:list --path=profile
php artisan migrate:fresh --force

# Seeders
php artisan db:seed --class=SettingsSeeder
php artisan db:seed --class=RolesSeeder
php artisan db:seed --class=TestUsersSeeder
php artisan db:seed             # default (creates test@example.com)

# SSO-specific
php artisan sso:sync-clients                       # create/update 5 clients
php artisan sso:sync-clients --rotate-secrets      # rotate secrets
php artisan sso:create-admin --email=X --name=Y --role=super_admin

# Passport
php artisan passport:client --personal --name="Personal"

# MySQL (XAMPP)
C:/xampp/mysql/bin/mysql.exe -uroot auth_server

# Cache
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

---

## 💡 ملاحظات مهمة لـ Claude Code

### أسلوب العمل
1. **اشرح قبل التنفيذ** — انتظر موافقة
2. **خطوة بخطوة** — لا تنفذ عدة مراحل دفعة
3. **اللغة**: عربي للشرح، إنجليزي للكود
4. **اسأل عند الشك**

### الأخطاء الشائعة للتجنب
- ❌ `php artisan passport:keys --force` (سيُبطل كل الـ tokens)
- ❌ تعديل ملفات `vendor/`
- ❌ تخزين secrets في الكود/Git
- ❌ HS256 للـ JWT
- ❌ إرسال صلاحيات من الـ IdP
- ❌ استخدام `ilike` (PostgreSQL specific — غيّر لـ `like`)

### ملاحظات خاصة
- بعض migrations لا تُطبّق الأعمدة صح عند `migrate:fresh` — قد يحتاج تعديل يدوي عبر tinker
- الألوان في DB = hex (ليست Tailwind classes)
- `mysql.exe` في `C:/xampp/mysql/bin/` مش في PATH

---

## 🌍 Environment Details

- **OS**: Windows
- **PHP**: 8.2.12 (XAMPP)
- **MySQL**: XAMPP (root بدون كلمة مرور)
- **Location**: `C:\Users\PC\Desktop\auth-server`
- **Database**: `auth_server`

---

## 📝 Next Immediate Tasks (بالترتيب)

### 1. Phase 8: Security Hardening (Rate limiting + Account lockout + Password history)
### 2. Phase 7: Single Logout (SLO + back-channel webhooks)
### 3. Phase 5: Migration from 5 databases (MigrationService + adapters)

---

## 📊 الإحصائيات الحالية

- **~60 routes** (admin + auth + profile + oauth + oidc)
- **~25 controllers**
- **~35 Blade views**
- **13+ DB tables** (MySQL)
- **24,170 SMS** رصيد متبقّي (Hotsms)
- **Developer Docs** بـ 4 code snippets (Laravel, CI3, CI4, Next.js)
- **Test data**: 7 مستخدمين + 5 أنظمة + 1 super_admin

---

**آخر تحديث**: 2026-04-18 — Phases 1-4 + 6 (Admin Panel) + Extras (Profile + SMS + 2FA + Dev Docs) مكتملة. المتبقّي: Phase 5, 7, 8.
