
---

# سند معماری و جریان کار — DormSys v3.1 Final Lock

**نسخه: 3.1.0 | تاریخ: 1405/04/01 | وضعیت: Final for Implementation**

---

## ۰. فلسفه‌ی این نسخه

این سند یک لیست تکنولوژی نیست.
این یک سیستم کنترل تصمیم است.

کد زیاد داریم.
تصمیم درست مثل اکسیژن کمیاب است.


---

## ۱. تصمیم بنیادی (ADR-010)

DormSys is NOT a Dormitory System.

DormSys is a Workflow System
that happens to manage accommodation resources.


این جمله همه‌چیز را تعریف می‌کند:

| حوزه | اگر «سیستم خوابگاه» | اگر «سیستم Workflow» |
|------|---------------------|---------------------|
| ERD | حول Room, Bed | حول Request, Stage, Transition |
| ماژول مرکزی | Allocation | **Workflow** (ولی Deferred) |
| تست اولویت | محاسبه اشغال | State transition + Invariant |
| گزارش | لیست اتاق | زمان Stage، گلوگاه Approval |
| توسعه بعدی | فقط خوابگاه | هر فرآیند سازمانی |

**وضعیت در v3.1:**

✅ Workflow Architecture      = Approved
⏸️  Workflow Module            = Deferred تا بعد از دو workflow واقعی
✅ ADR-011                     = Active Constraint


---

## ۲. فلسفه‌ی Human / Machine / AI (بدون تغییر)

Machine → چه چیزی تغییر کرد؟ وابستگی چیست؟ چه چیزی شکست؟
Human   → چرا این تصمیم؟ trade-off چیست? Domain Contract چیست؟
AI      → در این مرزها پیاده‌سازی کن.


سه اصل طلایی:

1. **Context باید Just-in-Time ساخته شود**، نه قبرستان مستندات
2. **AI تصمیم‌گیر نیست؛ مجری است**
3. **ADR محل چرایی است**، نه لیست فایل‌ها

---

## ۳. Domain Contract (اصلاح‌شده v3.1)

Domain Contract دو لایه دارد:

[Behavior Contract]
  ├─ State Machine       (حالت‌های مجاز و گذارها)
  ├─ Business Invariants (قوانینی که همیشه برقرار‌اند)
  └─ Domain Events.v1    (آنچه پس از تغییر معتبر منتشر می‌شود)

[Access Contract]
  └─ Application Service Interface  (نقطه ورود به دامنه)


### چرا تفکیک؟

- **Behavior Contract** تصمیم دامنه است → تغییر نمی‌کند مگر با ADR
- **Access Contract** نحوه دسترسی است → ممکن است برای client‌های متفاوت تغییر کند

### آنچه قرارداد نیست:

❌ Form Request        → Input Validation (adapter ورودی)
❌ Livewire Component  → Presentation State
❌ Controller          → Routing/Orchestration
❌ Eloquent Model      → ORM + Persistence
❌ Migration           → Storage Schema


### Invariant نمونه (نه Validation)

Allocation:
  INV-1: یک تخت در یک بازه زمانی نمی‌تواند به دو نفر تخصیص یابد
  INV-2: تخصیص فقط روی Bed با status=In-Service مجاز است

Lottery:
  INV-3: same RandomSeed => same Result (reproducibility)
  INV-4: یک LotteryRun فقط یک‌بار Execute می‌شود (idempotency)

Request:
  INV-5: گذار وضعیت فقط در مسیر State Machine مجاز است
  INV-6: هیچ Approval بدون actor دارای permission ثبت نمی‌شود


هر کدام در `enforced_by` به Pest test لینک می‌شوند.

---

## ۴. Domain Events با Versioning (جدید در v3.1)

Events قرارداد عمومی‌اند → باید versioned باشند.

```php
// ✅ صحیح
namespace App\Modules\Request\Domain\Events\v1;

class RequestSubmitted
{
    public string $version = '1.0';
    public string $requestId;
    public string $applicantId;
    public Carbon $submittedAt;
}
```

```php
// ✅ وقتی Contract تغییر کرد
namespace App\Modules\Request\Domain\Events\v2;

class RequestSubmitted
{
    public string $version = '2.0';
    public string $requestId;
    public string $applicantId;
    public Carbon $submittedAt;
    public ?string $submittedBy;  // ← فیلد جدید
}
```

### قانون مهاجرت Event

v1 listener → باید همیشه کار کند
v2 listener → می‌تواند موازی با v1 اجرا شود
v1 event    → می‌تواند به v2 adapter شود (نه برعکس)


چرا؟ چون Read Model Projectionها به event گوش می‌دهند و شکستن آن‌ها = شکستن گزارش‌ها.

---

## ۵. دو نوع استخراج که نباید قاطی شوند

استخراج از کد (Build-time)         |   استخراج از داده (Runtime)
─────────────────────────────────  |  ─────────────────────────────
Derived Graph                       |   Read Model
                                    |
از repo استخراج می‌شود              |   از Domain Events ساخته می‌شود
dependency / route / test map       |   projection برای گزارش و داشبورد
مصرف‌کننده: AI، CI، Governance       |   مصرف‌کننده: مدیر، گزارش
ابزار: Deptrac, route:list, Pest    |   ابزار: Listener → Projection → Table


### ۵.۱ Derived Graph

Source Code
  ↓
Deptrac + route:list + Pest --coverage
  ↓
dependency_graph.json
route_map.json
test_coverage.json
  ↓
Context Builder


اصل: **هرچه قابل استخراج است، دستی نگهداری نشود.**

### ۵.۲ Read Model — با مالکیت روشن (اصلاح v3.1)

Domain Event.v1
  ↓
Projection (Listener)
  ↓
Read Model Table
  ↓
Dashboard / Report


#### مالکیت Read Model

اگر projection از event یک domain  → همان domain مالک است
اگر projection از event چند domain → Reporting Module مالک است


نمونه:

Modules/Request/
  ReadModels/
    RequestSummaryProjection.php
      ← فقط RequestSubmitted, RequestApproved

Modules/Reporting/
  ReadModels/
    ApprovalDashboardProjection.php
      ← RequestApproved + LotteryDrawn + AllocationCreated


#### قانون قفل‌شده

Management Reports = Read Models ONLY

ممنوع:
  Query مستقیم روی Transaction Tables
  (requests, allocations, lottery_results)


چرا؟
- JOIN بین ماژول‌ها را وسوسه می‌کند
- مرز ماژول را می‌شکند
- با رشد داده کند می‌شود

---

## ۶. Workflow Architecture (Approved) اما Module (Deferred)

### وضعیت در v3.1

✅ Workflow-Centric Architecture = Approved
⏸️  Workflow Module               = Deferred


### چرا Deferred؟

این تناقض را حل می‌کند:

ما گفتیم: "Engine را استخراج کن، اختراع نکن"
ولی بعد: "app/Modules/Workflow/" را تعریف کردیم

AI می‌بیند: Workflow Module
AI می‌سازد: WorkflowEngine

انسان می‌گوید: "ما که گفتیم فعلاً نساز!"


### ترتیب صحیح

Phase 1:
  ✅ ADR-010: Workflow-Centric Architecture
  ✅ ADR-011: Workflow Engine Extraction Discipline
  
Phase 2:
  ✅ Modules/Request     با State Machine
  ✅ Modules/Lottery     با State Machine
  ✅ Modules/Approval    با State Machine
  
Phase 3:
  🔍 بررسی الگوی مشترک
  
Phase 4:
  (فقط اگر الگو پیدا شد)
  ✅ Modules/Workflow    استخراج Engine


### اگر Engine لازم شد، ساختار پیشنهادی

app/Modules/Workflow/
├─ Domain/
│  ├─ Models/
│  │  ├─ WorkflowDefinition
│  │  ├─ WorkflowStage
│  │  └─ WorkflowTransition
│  ├─ Services/
│  │  ├─ WorkflowEngine
│  │  └─ ApprovalEngine
│  └─ Events/v1/
│     ├─ StageEntered
│     └─ TransitionApplied


ولی **فعلاً namespace آن هم ایجاد نشود.**

---

## ۷. Eloquent Model — تعریف عملیاتی (اصلاح v3.1)

نسخه قبلی:

Eloquent = فقط Persistence


این از نظر Clean Architecture ایده‌آل است، ولی در Laravel عملیاتی خیلی سخت‌گیرانه است.

نسخه v3.1:

Eloquent Model شامل:
  ✅ ORM Mapping (table, primary key, timestamps)
  ✅ Relationships (hasMany, belongsTo)
  ✅ Casts (date, json, enum)
  ✅ Scopes (query filters مشترک)
  ✅ Accessors/Mutators (فرمت نمایش)

ممنوع:
  ❌ Business Workflow Decision
  ❌ Business Invariant Enforcement
  ❌ State Transition Logic


نمونه:

```php
// ✅ مجاز
class Request extends Model
{
    protected $casts = [
        'submitted_at' => 'datetime',
        'metadata' => 'array',
    ];
    
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
```

```php
// ❌ ممنوع
class Request extends Model
{
    public function approve()  // ← تصمیم workflow
    {
        if ($this->status === 'pending') {
            $this->status = 'approved';
            $this->save();
        }
    }
}
```

تصمیم workflow باید در Service یا State Class باشد.

---

## ۸. Context Builder JIT — مشخص شده (اصلاح v3.1)

Context Builder چه چیزی تولید می‌کند؟

برای Task: "Implement Lottery Draw Logic"

Context Builder generates:

📄 /constitution.md
   ← فلسفه پروژه، معماری کلی

📄 ADR مرتبط
   ← فقط ADRهایی که به این Task ربط دارند
   مثلاً ADR-010 (Workflow-Centric)

📄 Domain Contract
   ├─ Lottery Service Interface
   ├─ Lottery State Machine
   └─ Lottery Events.v1

📄 Related Classes
   ├─ app/Modules/Lottery/Domain/Services/LotteryService.php
   ├─ app/Modules/Lottery/Domain/States/LotteryState.php
   └─ app/Modules/Lottery/Domain/Events/v1/LotteryDrawn.php

📄 Tests
   └─ tests/Feature/Lottery/LotteryDrawTest.php

📄 Dependency Graph (محلی)
   LotteryService
     → RequestService (می‌خواند)
     → AllocationService (می‌نویسد)

📄 Enforced Rules
   ← از ADR استخراج شده
   - INV-3: same seed => same result
   - Test: LotteryReproducibilityTest


### چه چیزی در Context نیست؟

❌ کل repository
❌ تمام ADRها
❌ تمام تست‌ها
❌ کد بی‌ربط به این Task


چرا؟ چون حتی AI هم با ۵۰۰ هزار خط context قبل از صبحانه گیج می‌شود.

---

## ۹. Deptrac — قانون صحیح (بدون تغییر ولی تأکید)

❌ WRONG (نسخه قدیمی):
   هر JOIN بین دو ماژول = شکست

✅ CORRECT (v3.1):
   - Cross-module WRITE = ممنوع
   - Cross-module READ  = فقط via Service
   - Deptrac روی namespace dependency کار می‌کند


نمونه:

```php
// ❌ ممنوع
namespace App\Modules\Allocation;

use App\Modules\Request\Domain\Models\Request;

Request::where('status', 'approved')->update(['processed' => true]);
```

```php
// ✅ مجاز
namespace App\Modules\Allocation;

use App\Modules\Request\Application\Services\RequestService;

$this->requestService->getApprovedRequests();
```

Deptrac config:

```yaml
layers:
  - name: Request
    collectors: [{ type: directory, value: app/Modules/Request/.* }]
  - name: Allocation
    collectors: [{ type: directory, value: app/Modules/Allocation/.* }]
  - name: Identity
    collectors: [{ type: directory, value: app/Modules/Identity/.* }]

ruleset:
  Request:
    - Identity          # Request → Identity مجاز
  Allocation:
    - Request           # Allocation → Request مجاز (read via Service)
    - Identity
  Identity:
    []                  # Identity به هیچ‌کس وابسته نیست
```

---

## ۱۰. استراتژی تست

Layer 1: Pest Unit (اجباری، سریع)
  ✅ Lottery reproducibility (same seed => same result)
  ✅ Allocation overlap detection
  ✅ State Machine transitions
  ✅ Business Invariants

Layer 2: Pest Feature (اجباری)
  ✅ Approval workflow end-to-end
  ✅ RBAC / Policy enforcement
  ✅ AuditLog append
  ✅ Livewire::test() برای component logic

Layer 3: Laravel Dusk (فقط Critical Flows)
  ✅ Login
  ✅ Approval Flow
  ✅ Lottery Execution
  ❌ نه کل سیستم


اصل:

> تست کند که اجرا نمی‌شود، بدتر از نبود تست است.
> چون توهم امنیت می‌سازد.

Coverage Target: >80% برای Domain و Application (Layer 1 + 2).

---

## ۱۱. Workflow نهایی — جدول مرحله‌به‌مرحله

|   # | نوع     | Artifact            | خروجی                    | Governance                 |
| --: | ------- | ------------------- | ------------------------ | -------------------------- |
|   1 | `[H]`   | Notion              | نیازمندی خام             | منبع حقیقت نیست            |
|   2 | `[H]`   | Spec Kit            | scope + acceptance       | قابل تبدیل به ADR/Contract |
|   3 | `[H]`   | ADR                 | rationale + trade-off    | فقط judgment               |
|   4 | `[H/M]` | enforced_by         | لینک به test/policy/rule | هر judgment لنگر دارد      |
|   5 | `[H]`   | **Domain Contract** | Behavior + Access        | قلب معماری                 |
|   6 | `[M]`   | Deptrac + PHPStan   | کشف نقض                  | namespace dependency       |
|   7 | `[H]`   | Eloquent + States   | Models + State Classes   | ORM + mapping              |
|   8 | `[M/H]` | Migration           | scripts                  | review انسانی اجباری       |
|   9 | `[H/M]` | Linear              | atomic tasks             | knowledge base نیست        |
|  10 | `[M]`   | Derived Graph       | code map                 | استخراج، نه دستی           |
|  11 | `[M]`   | Context Builder     | JIT context              | ذخیره نمی‌شود              |
|  12 | `[H/M]` | Git Branch          | branch per task          | به task وصل                |
|  13 | `[M/H]` | Cursor + Cline      | code + tests             | تصمیم جدید = ADR           |
|  14 | `[M]`   | Pint                | code style               | gate                       |
|  15 | `[M]`   | PHPStan L8          | type/logic errors        | Service + State            |
|  16 | `[M]`   | Pest Unit+Feature   | tests، >80%              | enforced_by                |
|  17 | `[M]`   | Dusk                | critical flows           | نه همه                     |
|  18 | `[M]`   | Semgrep + Gitleaks  | security + secret        | PR                         |
|  19 | `[M]`   | GitHub Actions      | pipeline                 | نگهبان contract            |
|  20 | `[M]`   | Docker              | image                    | parity                     |
|  21 | `[M]`   | Staging             | validation               | کشف mismatch               |
|  22 | `[M]`   | Sentry + Pulse      | errors + metrics         | ورودی incident             |
|  23 | `[M]`   | Production          | محصول فعال               | feedback                   |
|  24 | `[H]`   | Incident Review     | root cause               | فعال‌کننده ADR             |
|  25 | `[H]`   | ADR Update          | حافظه جدید               | ضد پوسیدگی                 |

---

## ۱۲. جدول منبع حقیقت (v3.1)

| حوزه | منبع حقیقت | نباید باشد |
|------|-----------|------------|
| چرایی | ADR | Linear، Slack، Chat |
| **قرارداد رفتار** | Service + State + Invariants + Events.v1 | Controller، Form |
| **قرارداد دسترسی** | Application Service Interface | — |
| گذار وضعیت | State Class (spatie) | Controller، SQL |
| هماهنگی چند مرحله | Workflow (Deferred) | منطق پراکنده |
| مرز ماژول | Deptrac namespace | اعتماد به انضباط |
| تکامل DB | Migrations | تغییر دستی |
| Validation | Form Request | منطق پراکنده |
| دسترسی | Policy + spatie/permission | if در Blade |
| **گزارش (domain)** | Read Model در همان ماژول | Transaction Table |
| **گزارش (cross-domain)** | Read Model در Reporting | JOIN چند ماژول |
| نقشه کد | Derived Graph | نگهداری دستی |
| کد واقعی | Git | مستندات |
| کیفیت | CI Gates | اعتماد به AI |
| عملیات حساس | AuditLog Append-Only | log پراکنده |
| خطای runtime | Sentry / Pulse | حدس |
| Context AI | Context Builder JIT | DB context دائمی |
| دانش incident | ADR Update | postmortem دفن‌شده |

---

## ۱۳. ADRهای قفل‌شده در v3.1

ADR-010  Workflow-Centric Architecture
         "DormSys is a Workflow System, not a Dormitory System"
         Status: Approved

ADR-011  Workflow Engine Extraction Discipline
         "Engine استخراج می‌شود، اختراع نمی‌شود"
         "Workflow Module = Deferred تا بعد از 2 workflow واقعی"
         Status: Active Constraint

ADR-012  Reports via Read Model Only
         "Management Reports = Read Models"
         "Domain-specific → در همان Module"
         "Cross-domain → در Reporting Module"
         Status: Approved

ADR-013  Module Boundary via Namespace Dependency
         "Cross-module write = ممنوع"
         "Cross-module read = فقط via Service"
         "Deptrac روی namespace، نه SQL"
         Status: Approved, enforced_by: Deptrac CI

ADR-014  Test Pyramid Discipline
         "Pest mandatory، Dusk فقط critical"
         Status: Approved
         
ADR-015  Domain Event Versioning
         "Events are public contracts → must be versioned"
         Status: Approved (جدید در v3.1)


---

## ۱۴. ده اصل اجرایی (v3.1 Final)

| # | اصل |
|--:|-----|
| 1 | **AI مجری است، نه منبع حقیقت** |
| 2 | **Domain Contract = Behavior (State + Invariants + Events.v1) + Access (Service Interface)** |
| 3 | **DormSys یک Workflow System است** |
| 4 | **Derived Graph (کد) ≠ Read Model (داده)** |
| 5 | **Read Model ownership: domain-specific → همان Module، cross-domain → Reporting** |
| 6 | **مرز ماژول = namespace dependency، نه SQL JOIN** |
| 7 | **Workflow Architecture = Approved، Workflow Module = Deferred** |
| 8 | **هر judgment مهم `enforced_by` دارد** |
| 9 | **Pest اجباری، Dusk فقط critical** |
| 10 | **Context همیشه JIT؛ Incident همیشه به ADR برمی‌گردد** |

---

## ۱۵. نمای معماری نهایی

```text
Notion [H] → Spec Kit [H] → ADR [H] ──┬─ enforced_by [H/M]
                                       │
                                       ↓
                        Domain Contract [H]
                          ├─ Behavior (State + Invariants + Events.v1)
                          └─ Access (Service Interface)
                                       ↓
                        Deptrac + PHPStan [M]
                                       ↓
                        Eloquent + States [H]
                                       ↓
                        Migrations [M/H]
                                       ↓
          ┌────────────────────────────┴────────────────────────────┐
          │                                                          │
   Derived Graph [M]                                      Domain Events.v1
   (از repo، build-time)                                          ↓
   → Context Builder                              ┌────────────────┴────────────┐
                                                  │                             │
                                           Domain Read Model          Reporting Read Model
                                           (در همان Module)           (cross-domain)
                                                  │                             │
                                                  └─────────────┬───────────────┘
                                                                ↓
                                                          Dashboard / Reports
                                       ↓
          Linear → Context Builder JIT → Git Branch → Cursor + Cline
                                       ↓
          Blade + Livewire 3 + Alpine + Tailwind
                                       ↓
          Pint / PHPStan / Pest / Dusk(critical) / Semgrep / Gitleaks
                                       ↓
          GitHub Actions → Docker → Staging → Sentry/Pulse → Production
                                       ↓
          Incident Review [H] → ADR Update [H]
```

---

## ۱۶. ترتیب پیشنهادی اجرا (مهم)

Phase 1: Constitution
  ✅ ADR-010 تا ADR-015
  ✅ این سند (v3.1)

Phase 2: Technical Foundation (Spec01)
  ✅ Laravel setup
  ✅ Module skeleton (بدون Workflow)
  ✅ CI: Pint + PHPStan + Pest + Deptrac
  ✅ Docker + Compose

Phase 3: اولین Workflow واقعی (Spec02)
  ✅ Request Module
     ├─ State Machine
     ├─ Invariants
     ├─ Events.v1
     ├─ Tests
     └─ Read Model (domain-specific)

Phase 4: دومین Workflow واقعی (Spec03)
  ✅ Lottery Module
     (ساختار مشابه)

Phase 5: تصمیم استخراج
  🔍 آیا الگوی مشترک وجود دارد؟
  
  اگر بله:
    → Workflow Module (با ADR جدید)
  
  اگر نه:
    → ادامه با State Machine per Module


---

## ۱۷. جمع‌بندی نهایی

### تغییرات v3.1 نسبت به v3.0

| موضوع | v3.0 | v3.1 |
|-------|------|------|
| Workflow Module | تعریف شده | **

| موضوع | v3.0 | v3.1 |
|-------|------|------|
| Workflow Module | تعریف شده | **Deferred** |
| Domain Contract | Service + State + Form Request | **Behavior + Access** (بدون Form Request) |
| Event Versioning | نداشت | **Events.v1** اجباری |
| Read Model Ownership | مبهم | **Domain-specific vs Cross-domain** |
| Context Builder | مبهم | **JIT با محتوای مشخص** |
| Eloquent Model | Persistence-only (خیلی سخت) | **عملیاتی + ممنوعیت‌ها** |


---

### چرا این نسخه Final است؟

۱. پیام به AI یکدست شد
   ✅ Workflow Architecture approved
   ⏸️  Workflow Module deferred
   → تناقض برطرف شد

۲. مالکیت روشن شد
   ✅ Read Model: domain-specific در همان Module، cross-domain در Reporting
   ✅ ADR مالک چرایی
   ✅ Contract مالک رفتار
   ✅ CI مالک enforcement

۳. Context دیگر قبرستان نیست
   ✅ JIT با محتوای مشخص
   ✅ فقط آنچه لازم است
   ✅ ذخیره نمی‌شود

۴. Event شکستنی نیست
   ✅ Versioning اجباری
   ✅ مهاجرت کنترل‌شده
   ✅ Projection محافظت‌شده

۵. Eloquent واقع‌گرایانه شد
   ✅ ORM + Relationships + Casts → مجاز
   ❌ Business Decision + State Transition → ممنوع


---

### این معماری برای چه کسی است؟

✅ تیم ۲–۴ نفره
✅ Laravel + Livewire
✅ ۱۰–۵۰ کاربر همزمان
✅ Workflow-heavy domain
✅ نیاز به governance بدون microservice
✅ پروژه که باید ۳–۵ سال بچرخد


---

### این معماری برای چه کسی نیست؟

❌ تیم ۲۰+ نفره که میخواهند microservices
❌ پروژه API-only بدون frontend
❌ پروژه CRUD ساده
❌ تیمی که Clean Architecture کلاسیک میخواهد
❌ پروژه prototype که ۶ ماه بیشتر عمر نمیکند


---

## ۱۸. Checklist تحویل به تیم

قبل از شروع Spec02، این موارد باید آماده باشد:

□ این سند (v3.1) در /architecture/ قفل شده
□ ADR-010 تا ADR-015 در /docs/ADR/ نوشته شده
□ constitution.md بازنویسی شده با ADR-010
□ Deptrac config نوشته شده
□ CI pipeline با گیت‌های اجباری راه‌اندازی شده
    □ Pint
    □ PHPStan L8
    □ Pest >80%
    □ Deptrac
    □ Gitleaks
□ Module skeleton ایجاد شده (بدون Workflow)
    □ app/Modules/Request
    □ app/Modules/Lottery
    □ app/Modules/Allocation
    □ app/Modules/Identity
    □ app/Modules/Reporting
□ Docker Compose برای local dev
□ Context Builder script اولیه (حتی ساده)
□ Linear workspace با template task


---

## ۱۹. قانون طلایی برای تیم

اگر تصمیم گرفتی که به معماری مربوط است:

  ۱. ADR بنویس
  ۲. enforced_by اضافه کن
  ۳. به تیم اطلاع بده
  ۴. Context را بروزرسانی نکن (JIT است)

اگر Incident اتفاق افتاد:

  ۱. Postmortem بنویس
  ۲. Root Cause پیدا کن
  ۳. اگر به معماری مربوط شد → ADR Update
  ۴. اگر فقط bug بود → ADR لازم نیست

اگر AI چیزی گفت که با ADR تناقض دارد:

  → AI اشتباه میکند
  → ADR source of truth است
  → Context را بازسازی کن


---

## ۲۰. آخرین جمله

این معماری از "لیست تکنولوژی‌ها" 
به "سیستم کنترل تصمیم‌ها" تبدیل شد.

و این تفاوت است که پروژه‌های سازمانی را نجات می‌دهد.


---

**پایان سند v3.1 Final Lock**

---

## تأییدیه

این نسخه برای implementation قفل شده است.

تغییرات بعدی فقط از طریق ADR و با دلیل.

نسخه: 3.1.0
تاریخ: 1405/04/01
وضعیت: ✅ Final for Implementation
