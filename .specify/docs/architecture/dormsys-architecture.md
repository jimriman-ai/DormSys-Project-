# سند معماری و استک فنی — DormSys v1.0

### نسخه: 1.0.0 | تاریخ: 1405/03/31 | وضعیت: Baseline Approved

---

## ۱. خلاصه تصمیم

**استک نهایی:** Laravel 13 + Livewire 3 + PostgreSQL 17

این تصمیم بر اساس زنجیره Why → Problem → Need → Solution → Stack گرفته شده است؛ نه بر اساس هیجان فناوری.

DormSys یک **Enterprise Workflow Application سازمانی** است با ۱۰ تا ۵۰ کاربر، نه یک پلتفرم SaaS، نه سیستم Real-time، نه AI Platform. استک باید این مسئله را با کمترین پیچیدگی ممکن حل کند.

---

## ۲. ADR-001 — انتخاب Laravel به جای FastAPI

| فیلد         | محتوا                                            |
| ------------ | ------------------------------------------------ |
| عنوان        | انتخاب Laravel 13 + Livewire به عنوان Stack اصلی |
| وضعیت        | Accepted                                         |
| تاریخ        | 1405/03/31                                       |
| تصمیم‌گیرنده | Tech Lead + Product Owner                        |

### زمینه

Constitution اولیه پروژه FastAPI را به عنوان Reference Stack تعریف کرده بود. پس از تحلیل کامل ماهیت DormSys (سیستم سازمانی با Form، Workflow، RBAC، Audit Trail، Report)، مشخص شد که FastAPI برای این مسئله نیاز به ساختن Toolbox از صفر دارد، در حالی که Laravel این Toolbox را از روز اول فراهم می‌کند.

### دلایل انتخاب Laravel

**Build Less Software:** Laravel از ابتدا دارای Authentication، RBAC، Validation، Queue، Scheduler، Audit، Storage است. FastAPI نیاز به پیاده‌سازی دستی همه اینها دارد.

**MSP سریع‌تر:** زمان رسیدن به MSP با Laravel+Livewire حدود ۲ تا ۳ ماه، با FastAPI+React حدود ۴ تا ۶ ماه است.

**Control Scope Creep:** یک اکوسیستم در مقابل چند اکوسیستم. Laravel+Livewire یعنی PHP، Blade، Livewire، PostgreSQL. FastAPI+React یعنی Python، FastAPI، Pydantic، React، TypeScript، State Management، API Client، و...

**ماهیت مسئله:** UI اصلی DormSys شامل Form، Table، Approval، Dashboard است — نه Drag & Drop پیچیده، نه Animation سنگین، نه SPA کامل. Livewire دقیقاً برای همین ساخته شده.

### پیامدها

Constitution پروژه باید به‌روزرسانی شود تا این تصمیم را منعکس کند. تمام اسناد بعدی (ERD، API Spec، Module Structure) بر اساس Laravel طراحی می‌شوند.

---

## ۳. معماری کلی

```
DormSys — Modular Monolith Architecture
Laravel 13 + Livewire 3 + PostgreSQL 17

┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                        │
├─────────────────────────────────────────────────────────────┤
│  Laravel Blade Templates (Server-side rendering)            │
│  Livewire 3 Components (Reactive UI بدون JS نوشتن)         │
│  Alpine.js 3 (Interactivity محدود — toggle, dropdown, ...)  │
│  Tailwind CSS 4 (Utility-first styling)                     │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                    Application Layer                         │
├─────────────────────────────────────────────────────────────┤
│  Laravel  13 (PHP 8.4)                                       │
│  ├─ Route → Controller → Service → Repository               │
│  ├─ Form Requests (Validation)                              │
│  ├─ Policies + Gates (Authorization)                        │
│  ├─ Jobs + Queue (Background Processing)                    │
│  ├─ Events + Listeners (Domain Events)                      │
│  └─ Notifications (In-App)                                  │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                      Domain Layer                            │
├─────────────────────────────────────────────────────────────┤
│  Modules (Bounded Contexts):                                │
│  ├─ Identity (User, Role, Permission)                       │
│  ├─ Employee (Employee, Department, Dependent)              │
│  ├─ Dormitory (Dormitory, Room, Bed)                        │
│  ├─ Request (Request, RequestApproval)                      │
│  ├─ Workflow (State Machine, Approval Engine)               │
│  ├─ Allocation (Allocation, AllocationItem)                 │
│  ├─ Lottery (LotteryProgram, Registration, Result)          │
│  ├─ CheckIn (CheckIn, CheckOut)                             │
│  ├─ Notification (NotificationLog)                          │
│  ├─ Audit (AuditLog)                                        │
│  └─ Report (Read-only Projections)                          │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                      Data Layer                              │
├─────────────────────────────────────────────────────────────┤
│  PostgreSQL 17 (Primary Database)                           │
│  Redis 7 (Queue + Cache + Session + Locking)                │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                     Storage Layer                            │
├─────────────────────────────────────────────────────────────┤
│  MinIO (S3 Compatible Object Storage)                       │
│  برای: اسناد مأموریت، فایل‌های ضمیمه، گزارش‌های تولیدشده  │
└─────────────────────────────────────────────────────────────┘
```

---

## ۴. استک کامل با جزئیات و نسخه

### ۴-۱. زیرساخت اصلی (Core Infrastructure)

|لایه|تکنولوژی|نسخه|توضیح|
|---|---|---|---|
|زبان|PHP|8.4|آخرین نسخه Stable با JIT و Property Hooks|
|فریم‌ورک|Laravel| 13.x|LTS — پشتیبانی بلندمدت|
|دیتابیس|PostgreSQL|17|ACID، JSON Support، Full-text Search|
|Cache/Queue|Redis|7.x|Queue، Cache، Locking، Session|
|وب‌سرور|Nginx|1.26|Reverse Proxy، Static Files|
|کانتینر|Docker|27.x|+ Docker Compose|

### ۴-۲. لایه ارائه (Presentation)

|تکنولوژی|نسخه|هدف|توضیح|
|---|---|---|---|
|Blade|Laravel  13 built-in|Template Engine|Server-side rendering، Layout، Component|
|Livewire|3.x|Reactive UI|فرم‌های پویا، جدول‌ها، Modal، Real-time Validation|
|Alpine.js|3.x|Micro-interactivity|Dropdown، Toggle، Conditional Show، ساده|
|Tailwind CSS|4.x|Styling|Utility-first، RTL Support، Dark Mode آماده|

**قاعده کلی:**

- Blade → ساختار صفحه، Layout، Partial
- Livewire → هر جزئی که State دارد یا با Server تعامل دارد
- Alpine → Interactivity ساده که نیاز به Server ندارد (open/close، tab switch)
- Tailwind → تمام استایل‌گذاری

### ۴-۳. Authentication و Authorization

|بسته|نسخه|هدف|
|---|---|---|
|Laravel Fortify|1.x|Authentication Engine (Login، Logout، Password Reset)|
|Laravel Sanctum|4.x|Token Authentication (برای API در آینده)|
|spatie/laravel-permission|6.x|RBAC — Role و Permission|
|Laravel Policies|Built-in|Authorization Logic برای هر Resource|
|Laravel Gates|Built-in|Authorization برای عملیات غیر-Resource|

**ساختار RBAC:**

```
Roles:
├─ employee              (کارمند عادی)
├─ department_manager    (رئیس واحد)
├─ hr_manager           (رئیس نیروی انسانی)
├─ dormitory_manager    (مسئول کل مامورسراها)
├─ dormitory_unit_staff (مسئول واحد مامورسرا)
├─ lottery_operator     (اپراتور قرعه‌کشی)
├─ operator             (مهماندار — Check-In/Out)
└─ admin                (مدیر سیستم)

Permissions (نمونه):
├─ requests.create
├─ requests.approve.stage1
├─ requests.approve.stage2
├─ allocations.create
├─ lottery.execute
├─ audit.view
└─ ...
```

### ۴-۴. Workflow و State Machine

|بسته|نسخه|هدف|
|---|---|---|
|spatie/laravel-model-states|2.x|State Machine صریح در Domain Layer|

**State Machineهای پیاده‌سازی‌شده:**

```
Request States:
Draft → Submitted → PendingDepartmentManager
     → PendingHR → PendingDormitoryManager
     → PendingDormitoryUnit → Approved
     → WaitingForAllocation → Allocated
     → CheckedIn → CheckedOut
     → Rejected | Cancelled | AllocationFailed

LotteryProgram States:
Draft → WaitingApproval → Approved
     → RegistrationOpen → RegistrationClosed
     → Locked → Drawn → Completed
     → Cancelled
```

**قاعده:** هیچ Transition منطقی در Controller یا SQL نوشته نمی‌شود. همه در State Class تعریف می‌شوند.

### ۴-۵. Audit Log

|بسته|نسخه|هدف|
|---|---|---|
|spatie/laravel-activitylog|4.x|ثبت خودکار تمام تغییرات|

**چه چیزی Log می‌شود:**

- تمام State Transition‌های Request و Lottery
- تمام Allocation (مستقیم + قرعه‌کشی)
- تمام Approval/Rejection با دلیل
- اجرای قرعه‌کشی + RandomSeed + Snapshot
- Out-of-Service کردن اتاق/تخت
- تخصیص و لغو تخصیص
- تغییر نقش و دسترسی کاربران

**ساختار AuditLog:**

```php
AuditLog:
├─ entity_type   (Request, Allocation, Room, ...)
├─ entity_id
├─ event         (created, updated, state_changed, ...)
├─ actor_id      (UserID یا system)
├─ old_values    (JSON)
├─ new_values    (JSON)
├─ metadata      (دلیل رد، توضیحات اضافه)
└─ created_at    (UTC)
```

### ۴-۶. پردازش پس‌زمینه (Background Processing)

|تکنولوژی|هدف|
|---|---|
|Laravel Jobs|اجرای قرعه‌کشی، ارسال Notification، تولید Report|
|Laravel Queue (Redis)|صف‌بندی Job‌ها|
|Laravel Horizon|مانیتورینگ Queue (از v1.0، Optional)|
|Laravel Scheduler|Lock خودکار قرعه‌کشی، Reminder Check-In|

**Job‌های اصلی:**

```
Jobs:
├─ ExecuteLotteryDrawJob      (اجرای الگوریتم قرعه‌کشی)
├─ AutoLockLotteryJob         (Lock خودکار پس از مهلت)
├─ SendNotificationJob        (ارسال In-App Notification)
├─ GenerateReportJob          (تولید Excel/PDF)
├─ LateCheckOutWarningJob     (هشدار خروج دیرهنگام)
└─ PromoteReserveWinnerJob    (ارتقای ذخیره)
```

### ۴-۷. Notification

|روش|وضعیت MVP|توضیح|
|---|---|---|
|In-App (Database)|✅ اجباری|از طریق notifications جدول|
|Email|❌ فاز بعد|Laravel Mail آماده است|
|SMS|❌ فاز بعد|Package جداگانه|

### ۴-۸. گزارش‌گیری (Reporting)

|بسته|نسخه|هدف|
|---|---|---|
|maatwebsite/excel|3.x|Export به Excel (xlsx)|
|barryvdh/laravel-dompdf|3.x|Export به PDF|
|Livewire + Charts|-|نمایش آنلاین گزارش‌ها در Browser|

**گزارش‌های MVP:**

- نرخ اشغال (Occupancy Rate)
- وضعیت درخواست‌ها
- زمان تأیید در هر Stage
- سابقه استفاده کارکنان
- نتایج قرعه‌کشی
- گزارش واحدها

### ۴-۹. Storage

|تکنولوژی|نسخه|هدف|وضعیت|
|---|---|---|---|
|MinIO|Latest|S3-Compatible Object Storage|اختیاری در v1|
|Laravel Storage|Built-in|Abstraction Layer|✅|

**کاربرد Storage:**

- آپلود سند مأموریت
- ذخیره گزارش‌های تولیدشده
- فایل‌های ضمیمه درخواست

### ۴-۱۰. کیفیت کد (Code Quality)

|ابزار|نسخه|هدف|
|---|---|---|
|Pest PHP|3.x|Unit + Integration + Feature Testing|
|PHPStan|2.x|Static Analysis — Level 8|
|Laravel Pint|1.x|Code Style (PSR-12 + Laravel conventions)|

**هدف Coverage:** بیش از ۸۰٪ برای Domain و Application Layer

**اولویت تست:**

```
1. Lottery Algorithm (اجرا، وزن‌دهی، PRNG، تطابق‌پذیری)
2. Allocation Overlap Detection
3. State Machine Transitions
4. RBAC و Permission
5. Approval Workflow
6. Audit Log
```

### ۴-۱۱. عملیات (Operations)

|تکنولوژی|نسخه|هدف|
|---|---|---|
|Docker|27.x|Containerization|
|Docker Compose|2.x|محیط توسعه و Production|
|Nginx|1.26|Web Server + Reverse Proxy|
|GitHub Actions|-|CI/CD Pipeline|
|Sentry|-|Error Tracking|
|Laravel Pulse|1.x|Application Monitoring (از Production)|

**CI/CD Pipeline:**

```
Push → GitHub Actions:
├─ PHPStan (Static Analysis)
├─ Laravel Pint (Code Style Check)
├─ Pest (Run Tests)
├─ Build Docker Image
└─ Deploy (if main branch)
```

---

## ۵. ساختار پوشه‌بندی Modular Monolith

```
dormsys/
├─ app/
│   ├─ Modules/
│   │   ├─ Identity/
│   │   │   ├─ Domain/
│   │   │   │   ├─ Models/          (User, Role)
│   │   │   │   └─ States/
│   │   │   ├─ Application/
│   │   │   │   ├─ Services/        (AuthService, PermissionService)
│   │   │   │   └─ DTOs/
│   │   │   ├─ Infrastructure/
│   │   │   │   └─ Repositories/
│   │   │   └─ Presentation/
│   │   │       ├─ Http/Controllers/
│   │   │       ├─ Livewire/
│   │   │       └─ Requests/
│   │   │
│   │   ├─ Employee/
│   │   │   ├─ Domain/
│   │   │   │   └─ Models/          (Employee, Department, Dependent)
│   │   │   ├─ Application/
│   │   │   │   └─ Services/        (EmployeeService)
│   │   │   ├─ Infrastructure/
│   │   │   └─ Presentation/
│   │   │
│   │   ├─ Dormitory/
│   │   │   ├─ Domain/
│   │   │   │   └─ Models/          (Dormitory, Room, Bed)
│   │   │   ├─ Application/
│   │   │   │   └─ Services/        (InventoryService, AvailabilityService)
│   │   │   ├─ Infrastructure/
│   │   │   └─ Presentation/
│   │   │
│   │   ├─ Request/
│   │   │   ├─ Domain/
│   │   │   │   ├─ Models/          (Request, RequestApproval, RequestMember)
│   │   │   │   └─ States/          (RequestState, DraftState, ...)
│   │   │   ├─ Application/
│   │   │   │   └─ Services/        (RequestService)
│   │   │   ├─ Infrastructure/
│   │   │   └─ Presentation/
│   │   │       └─ Livewire/        (CreateRequestForm, RequestList, ...)
│   │   │
│   │   ├─ Workflow/
│   │   │   ├─ Domain/
│   │   │   │   └─ Models/          (RequestApproval)
│   │   │   ├─ Application/
│   │   │   │   └─ Services/        (ApprovalService, WorkflowEngine)
│   │   │   ├─ Infrastructure/
│   │   │   └─ Presentation/
│   │   │
│   │   ├─ Allocation/
│   │   │   ├─ Domain/
│   │   │   │   └─ Models/          (Allocation, AllocationItem)
│   │   │   ├─ Application/
│   │   │   │   └─ Services/        (AllocationService, OverlapDetector)
│   │   │   ├─ Infrastructure/
│   │   │   └─ Presentation/
│   │   │
│   │   ├─ Lottery/
│   │   │   ├─ Domain/
│   │   │   │   ├─ Models/          (LotteryProgram, LotteryRegistration, LotteryResult)
│   │   │   │   ├─ States/          (LotteryProgramState, ...)
│   │   │   │   └─ Services/        (ScoringEngine, DrawEngine)
│   │   │   ├─ Application/
│   │   │   │   └─ Services/        (LotteryService)
│   │   │   ├─ Infrastructure/
│   │   │   └─ Presentation/
│   │   │
│   │   ├─ CheckIn/
│   │   ├─ Notification/
│   │   ├─ Audit/
│   │   └─ Report/
│   │
│   ├─ Shared/
│   │   ├─ Domain/
│   │   │   ├─ Events/              (Domain Events)
│   │   │   └─ ValueObjects/
│   │   ├─ Infrastructure/
│   │   │   └─ Persistence/
│   │   └─ Support/
│   │       └─ Helpers/
│   │
│   └─ Http/
│       └─ Middleware/
│
├─ config/
├─ database/
│   ├─ migrations/
│   └─ seeders/
├─ resources/
│   ├─ views/
│   │   ├─ layouts/
│   │   ├─ components/
│   │   └─ modules/
│   ├─ js/                          (Alpine.js snippets)
│   └─ css/                         (Tailwind)
├─ routes/
│   ├─ web.php
│   └─ modules/                     (Route per module)
├─ tests/
│   ├─ Unit/
│   ├─ Integration/
│   └─ Feature/
└─ docker/
    ├─ nginx/
    └─ php/
```

---

## ۶. قوانین طراحی (Design Rules)

### قانون ۱ — Module Boundary

هر ماژول مالک جداول خودش است و تنها Writer آنهاست. ارتباط بین ماژول‌ها فقط از طریق Application Service انجام می‌شود — نه JOIN مستقیم بین جداول ماژول‌های مختلف.

### قانون ۲ — State Machine

هیچ منطق Transition در Controller، Livewire Component، یا SQL نوشته نمی‌شود. همه Transition‌ها در State Class تعریف می‌شوند (spatie/laravel-model-states).

### قانون ۳ — Audit

تمام عملیات حساس (Allocation، State Transition، Lottery Execution، Permission Change) در AuditLog ثبت می‌شوند. Audit Log فقط Append-Only است و حذف‌شدنی نیست.

### قانون ۴ — No Hardcoding

تمام پارامترهای قابل تغییر (ضرایب قرعه‌کشی، مهلت‌های زمانی، سقف گروه) در جدول `settings` ذخیره می‌شوند — نه در کد.

### قانون ۵ — Livewire vs Alpine

اگر نیاز به Server دارد → Livewire. اگر فقط UI است → Alpine. اگر نه Server نه UI پیچیده → Blade خالی.

### قانون ۶ — Test First برای Lottery

الگوریتم قرعه‌کشی (ScoringEngine، DrawEngine، PRNG) باید قبل از هر چیز دیگری Unit Test داشته باشد. نتیجه قرعه‌کشی با RandomSeed یکسان باید همیشه Reproducible باشد.

### قانون ۷ — Idempotency

اجرای قرعه‌کشی، Allocation، و Approval باید Idempotent باشند یا به صراحت از اجرای تکراری جلوگیری کنند.

---

## ۷. Docker Compose (محیط توسعه)

```yaml
# docker-compose.yml
services:

  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    image: dormsys-app
    container_name: dormsys_app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - .:/var/www
    networks:
      - dormsys
    depends_on:
      - postgres
      - redis

  nginx:
    image: nginx:1.26-alpine
    container_name: dormsys_nginx
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - .:/var/www
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    networks:
      - dormsys
    depends_on:
      - app

  postgres:
    image: postgres:17-alpine
    container_name: dormsys_postgres
    restart: unless-stopped
    environment:
      POSTGRES_DB: dormsys
      POSTGRES_USER: dormsys_user
      POSTGRES_PASSWORD: secret
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"
    networks:
      - dormsys

  redis:
    image: redis:7-alpine
    container_name: dormsys_redis
    restart: unless-stopped
    command: redis-server --requirepass secret
    volumes:
      - redis_data:/data
    ports:
      - "6379:6379"
    networks:
      - dormsys

  horizon:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: dormsys_horizon
    restart: unless-stopped
    working_dir: /var/www
    command: php artisan horizon
    volumes:
      - .:/var/www
    networks:
      - dormsys
    depends_on:
      - postgres
      - redis

  minio:
    image: minio/minio:latest
    container_name: dormsys_minio
    restart: unless-stopped
    command: server /data --console-address ":9001"
    environment:
      MINIO_ROOT_USER: minioadmin
      MINIO_ROOT_PASSWORD: minioadmin
    volumes:
      - minio_data:/data
    ports:
      - "9000:9000"
      - "9001:9001"
    networks:
      - dormsys

networks:
  dormsys:
    driver: bridge

volumes:
  postgres_data:
  redis_data:
  minio_data:
```

---

## ۸. متغیرهای محیطی کلیدی (.env)

```env
APP_NAME=DormSys
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080
APP_LOCALE=fa
APP_TIMEZONE=UTC

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=dormsys
DB_USERNAME=dormsys_user
DB_PASSWORD=secret

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=secret
REDIS_PORT=6379

FILESYSTEM_DISK=minio
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=dormsys
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true

SENTRY_LARAVEL_DSN=
```

---

## ۹. Packages کامل (composer.json نمونه)

```json
{
  "require": {
    "php": "^8.4",
    "laravel/framework": "^ 13.0",
    "laravel/fortify": "^1.0",
    "laravel/sanctum": "^4.0",
    "laravel/horizon": "^5.0",
    "laravel/pulse": "^1.0",
    "livewire/livewire": "^3.0",
    "spatie/laravel-permission": "^6.0",
    "spatie/laravel-model-states": "^2.0",
    "spatie/laravel-activitylog": "^4.0",
    "maatwebsite/excel": "^3.1",
    "barryvdh/laravel-dompdf": "^3.0",
    "sentry/sentry-laravel": "^4.0"
  },
  "require-dev": {
    "pestphp/pest": "^3.0",
    "pestphp/pest-plugin-laravel": "^3.0",
    "phpstan/phpstan": "^2.0",
    "nunomaduro/larastan": "^3.0",
    "laravel/pint": "^1.0"
  }
}
```

---

## ۱۰. فازبندی توسعه (بر اساس استک نهایی)

### فاز ۱ — زیرساخت و داده‌های پایه (هفته ۱-۳)

راه‌اندازی Docker، PostgreSQL، Redis، ساختار ماژولار، Migration پایه، Auth با Fortify، RBAC با Spatie، Audit Log

### فاز ۲ — قرعه‌کشی (هفته ۴-۸)

ماژول Lottery کامل: تعریف برنامه، ثبت‌نام، Lock، اجرای الگوریتم، برندگان و ذخیره‌ها، Unit Test کامل برای ScoringEngine و DrawEngine

### فاز ۳ — درخواست فردی + تخصیص مستقیم (هفته ۹-۱۲)

ماژول Request (Personal)، Workflow Engine چهارمرحله‌ای، ماژول Allocation با Overlap Detection، Livewire UI

### فاز ۴ — درخواست گروهی تعمیراتی (هفته ۱۳-۱۵)

Mission Request، توزیع اعضا در چند اتاق، AllocationItem برای Mission

### فاز ۵ — Check-In/Out + گزارش‌ها (هفته ۱۶-۱۸)

CheckIn/Out Operator Interface، گزارش‌های مدیریتی، Export Excel/PDF

---

## ۱۱. خلاصه نهایی

|بخش|انتخاب|
|---|---|
|زبان|PHP 8.4|
|فریم‌ورک|Laravel  13|
|UI Layer|Blade + Livewire 3|
|Micro-interactivity|Alpine.js 3|
|Styling|Tailwind CSS 4|
|دیتابیس|PostgreSQL 17|
|Cache/Queue|Redis 7|
|RBAC|spatie/laravel-permission|
|State Machine|spatie/laravel-model-states|
|Audit|spatie/laravel-activitylog|
|Queue Monitor|Laravel Horizon|
|App Monitor|Laravel Pulse|
|Error Tracking|Sentry|
|Storage|MinIO (S3)|
|Export|Laravel Excel + DomPDF|
|Test|Pest PHP 3|
|Static Analysis|PHPStan Level 8|
|Code Style|Laravel Pint|
|Container|Docker + Nginx|
|CI/CD|GitHub Actions|

---

_سند استک فنی DormSys v1.0 — نهایی و آماده برای شروع توسعه_ _گام بعدی: طراحی Database Schema (ERD) بر اساس این استک_