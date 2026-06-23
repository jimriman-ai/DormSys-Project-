# Architecture and Technical Stack Document — DormSys v1.0

### Version: 1.0.0 | Date: 1405/03/31 | Status: Baseline Approved

---

## 1. Decision Summary

**Final Stack:** Laravel 13+ Livewire 3 + PostgreSQL 17

This decision has been made based on the Why → Problem → Need → Solution → Stack chain; not based on technology hype.

DormSys is an **Enterprise Workflow Application** with 10 to 50 users. It is not a SaaS platform, not a real-time system, and not an AI platform. The stack must solve this problem with the least possible complexity.

---

## 2. ADR-001 — Choosing Laravel Instead of FastAPI

| Field | Content |
|---|---|
| Title | Choosing Laravel 13+ Livewire as the Main Stack |
| Status | Accepted |
| Date | 1405/03/31 |
| Decision Maker | Tech Lead + Product Owner |

### Context

The initial project constitution had defined FastAPI as the reference stack. After a complete analysis of the nature of DormSys (an organizational system with forms, workflows, RBAC, audit trail, and reporting), it became clear that FastAPI would require building a toolbox from scratch for this problem, while Laravel provides this toolbox from day one.

### Reasons for Choosing Laravel

**Build Less Software:** Laravel provides Authentication, RBAC, Validation, Queue, Scheduler, Audit, and Storage from the beginning. FastAPI requires manual implementation of all of these.

**Faster MSP:** The time to reach the MSP with Laravel + Livewire is about 2 to 3 months, while with FastAPI + React it is about 4 to 6 months.

**Control Scope Creep:** One ecosystem versus multiple ecosystems. Laravel + Livewire means PHP, Blade, Livewire, and PostgreSQL. FastAPI + React means Python, FastAPI, Pydantic, React, TypeScript, State Management, API Client, and more.

**Nature of the Problem:** DormSys’s main UI consists of forms, tables, approvals, and dashboards — not complex drag & drop, not heavy animations, and not a full SPA. Livewire is built exactly for this.

### Consequences

The project constitution must be updated to reflect this decision. All subsequent documents (ERD, API Spec, Module Structure) will be designed based on Laravel.

---

## 3. Overall Architecture

```text
DormSys — Modular Monolith Architecture
Laravel 12 + Livewire 3 + PostgreSQL 17

┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                        │
├─────────────────────────────────────────────────────────────┤
│  Laravel Blade Templates (Server-side rendering)            │
│  Livewire 3 Components (Reactive UI without writing JS)     │
│  Alpine.js 3 (Limited interactivity — toggle, dropdown, ...)│
│  Tailwind CSS 4 (Utility-first styling)                     │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                    Application Layer                         │
├─────────────────────────────────────────────────────────────┤
│  Laravel 13(PHP 8.4)                                       │
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
│  For: mission documents, attachment files, generated reports│
└─────────────────────────────────────────────────────────────┘
```

---

## 4. Complete Stack with Details and Versions

### 4-1. Core Infrastructure

| Layer | Technology | Version | Description |
|---|---|---|---|
| Language | PHP | 8.4 | Latest stable version with JIT and Property Hooks |
| Framework | Laravel | 13.x | LTS — long-term support |
| Database | PostgreSQL | 17 | ACID, JSON Support, Full-text Search |
| Cache/Queue | Redis | 7.x | Queue, Cache, Locking, Session |
| Web Server | Nginx | 1.26 | Reverse Proxy, Static Files |
| Container | Docker | 27.x | + Docker Compose |

### 4-2. Presentation Layer

| Technology | Version | Purpose | Description |
|---|---|---|---|
| Blade | Laravel 13built-in | Template Engine | Server-side rendering, Layout, Component |
| Livewire | 3.x | Reactive UI | Dynamic forms, tables, modals, real-time validation |
| Alpine.js | 3.x | Micro-interactivity | Dropdown, Toggle, Conditional Show, simple interactions |
| Tailwind CSS | 4.x | Styling | Utility-first, RTL Support, Dark Mode ready |

**General Rule:**

- Blade → page structure, layout, partials
- Livewire → any component that has state or interacts with the server
- Alpine → simple interactivity that does not require the server (open/close, tab switch)
- Tailwind → all styling

### 4-3. Authentication and Authorization

| Package | Version | Purpose |
|---|---|---|
| Laravel Fortify | 1.x | Authentication Engine (Login, Logout, Password Reset) |
| Laravel Sanctum | 4.x | Token Authentication (for future API) |
| spatie/laravel-permission | 6.x | RBAC — Role and Permission |
| Laravel Policies | Built-in | Authorization Logic for each Resource |
| Laravel Gates | Built-in | Authorization for non-resource operations |

**RBAC Structure:**

```text
Roles:
├─ employee              (regular employee)
├─ department_manager    (department head)
├─ hr_manager            (head of human resources)
├─ dormitory_manager     (overall dormitory manager)
├─ dormitory_unit_staff  (dormitory unit staff)
├─ lottery_operator      (lottery operator)
├─ operator              (receptionist — Check-In/Out)
└─ admin                 (system administrator)

Permissions (examples):
├─ requests.create
├─ requests.approve.stage1
├─ requests.approve.stage2
├─ allocations.create
├─ lottery.execute
├─ audit.view
└─ ...
```

### 4-4. Workflow and State Machine

| Package | Version | Purpose |
|---|---|---|
| spatie/laravel-model-states | 2.x | Explicit State Machine in the Domain Layer |

**Implemented State Machines:**

```text
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

**Rule:** No logical transition is written in a Controller or SQL. Everything is defined in the State Class.

### 4-5. Audit Log

| Package | Version | Purpose |
|---|---|---|
| spatie/laravel-activitylog | 4.x | Automatic logging of all changes |

**What Gets Logged:**

- All Request and Lottery state transitions
- All Allocations (direct + lottery)
- All Approvals/Rejections with reason
- Lottery execution + RandomSeed + Snapshot
- Marking a room/bed as out-of-service
- Allocation and allocation cancellation
- Changes to user roles and permissions

**AuditLog Structure:**

```php
AuditLog:
├─ entity_type   (Request, Allocation, Room, ...)
├─ entity_id
├─ event         (created, updated, state_changed, ...)
├─ actor_id      (UserID or system)
├─ old_values    (JSON)
├─ new_values    (JSON)
├─ metadata      (rejection reason, additional descriptions)
└─ created_at    (UTC)
```

### 4-6. Background Processing

| Technology | Purpose |
|---|---|
| Laravel Jobs | Executing lottery, sending notifications, generating reports |
| Laravel Queue (Redis) | Queueing jobs |
| Laravel Horizon | Queue monitoring (optional from v1.0) |
| Laravel Scheduler | Automatic lottery locking, Check-In reminders |

**Main Jobs:**

```text
Jobs:
├─ ExecuteLotteryDrawJob      (execute lottery algorithm)
├─ AutoLockLotteryJob         (automatic lock after deadline)
├─ SendNotificationJob        (send in-app notification)
├─ GenerateReportJob          (generate Excel/PDF)
├─ LateCheckOutWarningJob     (late check-out warning)
└─ PromoteReserveWinnerJob    (promote reserve winner)
```

### 4-7. Notification

| Method | MVP Status | Description |
|---|---|---|
| In-App (Database) | ✅ Mandatory | Through the notifications table |
| Email | ❌ Later phase | Laravel Mail is ready |
| SMS | ❌ Later phase | Separate package |

### 4-8. Reporting

| Package | Version | Purpose |
|---|---|---|
| maatwebsite/excel | 3.x | Export to Excel (xlsx) |
| barryvdh/laravel-dompdf | 3.x | Export to PDF |
| Livewire + Charts | - | Online report display in the browser |

**MVP Reports:**

- Occupancy Rate
- Request status
- Approval time at each stage
- Employee usage history
- Lottery results
- Department reports

### 4-9. Storage

| Technology | Version | Purpose | Status |
|---|---|---|---|
| MinIO | Latest | S3-Compatible Object Storage | Optional in v1 |
| Laravel Storage | Built-in | Abstraction Layer | ✅ |

**Storage Usage:**

- Uploading mission documents
- Storing generated reports
- Request attachment files

### 4-10. Code Quality

| Tool | Version | Purpose |
|---|---|---|
| Pest PHP | 3.x | Unit + Integration + Feature Testing |
| PHPStan | 2.x | Static Analysis — Level 8 |
| Laravel Pint | 1.x | Code Style (PSR-12 + Laravel conventions) |

**Coverage Target:** More than 80% for the Domain and Application layers

**Testing Priority:**

```text
1. Lottery Algorithm (execution, weighting, PRNG, reproducibility)
2. Allocation Overlap Detection
3. State Machine Transitions
4. RBAC and Permission
5. Approval Workflow
6. Audit Log
```

### 4-11. Operations

| Technology | Version | Purpose |
|---|---|---|
| Docker | 27.x | Containerization |
| Docker Compose | 2.x | Development and Production environment |
| Nginx | 1.26 | Web Server + Reverse Proxy |
| GitHub Actions | - | CI/CD Pipeline |
| Sentry | - | Error Tracking |
| Laravel Pulse | 1.x | Application Monitoring (from Production) |

**CI/CD Pipeline:**

```text
Push → GitHub Actions:
├─ PHPStan (Static Analysis)
├─ Laravel Pint (Code Style Check)
├─ Pest (Run Tests)
├─ Build Docker Image
└─ Deploy (if main branch)
```

---

## 5. Modular Monolith Folder Structure

```text
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

## 6. Design Rules

### Rule 1 — Module Boundary

Each module owns its own tables and is the only writer to them. Communication between modules happens only through Application Services — not through direct JOINs between tables of different modules.

### Rule 2 — State Machine

No transition logic is written in a Controller, Livewire Component, or SQL. All transitions are defined in State Classes (spatie/laravel-model-states).

### Rule 3 — Audit

All sensitive operations (Allocation, State Transition, Lottery Execution, Permission Change) are recorded in AuditLog. Audit Log is append-only and cannot be deleted.

### Rule 4 — No Hardcoding

All changeable parameters (lottery coefficients, time deadlines, group size limits) are stored in the `settings` table — not in code.

### Rule 5 — Livewire vs Alpine

If it needs the server → Livewire. If it is only UI → Alpine. If it needs neither the server nor complex UI → plain Blade.

### Rule 6 — Test First for Lottery

The lottery algorithm (ScoringEngine, DrawEngine, PRNG) must have Unit Tests before anything else. The lottery result with the same RandomSeed must always be reproducible.

### Rule 7 — Idempotency

Lottery execution, Allocation, and Approval must be idempotent or must explicitly prevent repeated execution.

---

## 7. Docker Compose (Development Environment)

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

## 8. Key Environment Variables (.env)

```env
APP_NAME=DormSys
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080
APP_LOCALE=fa
APP_TIMEZONE=Asia/Tehran

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

## 9. Complete Packages (sample composer.json)

```json
{
  "require": {
    "php": "^8.4",
    "laravel/framework": "^12.0",
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

## 10. Development Phasing (Based on the Final Stack)

### Phase 1 — Infrastructure and Base Data (Weeks 1–3)

Docker setup, PostgreSQL, Redis, modular structure, base migrations, Auth with Fortify, RBAC with Spatie, Audit Log

### Phase 2 — Lottery (Weeks 4–8)

Complete Lottery module: program definition, registration, lock, algorithm execution, winners and reserves, complete Unit Tests for ScoringEngine and DrawEngine

### Phase 3 — Individual Request + Direct Allocation (Weeks 9–12)

Request module (Personal), four-stage Workflow Engine, Allocation module with Overlap Detection, Livewire UI

### Phase 4 — Group Maintenance Request (Weeks 13–15)

Mission Request, distributing members across multiple rooms, AllocationItem for Mission

### Phase 5 — Check-In/Out + Reports (Weeks 16–18)

CheckIn/Out Operator Interface, management reports, Excel/PDF export

---

## 11. Final Summary

| Section | Choice |
|---|---|
| Language | PHP 8.4 |
| Framework | Laravel 13|
| UI Layer | Blade + Livewire 3 |
| Micro-interactivity | Alpine.js 3 |
| Styling | Tailwind CSS 4 |
| Database | PostgreSQL 17 |
| Cache/Queue | Redis 7 |
| RBAC | spatie/laravel-permission |
| State Machine | spatie/laravel-model-states |
| Audit | spatie/laravel-activitylog |
| Queue Monitor | Laravel Horizon |
| App Monitor | Laravel Pulse |
| Error Tracking | Sentry |
| Storage | MinIO (S3) |
| Export | Laravel Excel + DomPDF |
| Test | Pest PHP 3 |
| Static Analysis | PHPStan Level 8 |
| Code Style | Laravel Pint |
| Container | Docker + Nginx |
| CI/CD | GitHub Actions |

