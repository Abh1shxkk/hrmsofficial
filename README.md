# HRMS - Human Resource Management System

A full-featured Human Resource Management System built with Laravel 12, Tailwind CSS, and Alpine.js. Manages employees, attendance, leaves, payroll, tasks, holidays, and documents with role-based access control.

---

## Tech Stack

| Layer      | Technology                            |
|------------|---------------------------------------|
| Backend    | PHP 8.2+, Laravel 12.x               |
| Frontend   | Tailwind CSS 4, Alpine.js 3, Vite 7  |
| Database   | MySQL 8.x                            |
| PDF Export | barryvdh/laravel-dompdf              |
| CSV Export | league/csv                           |
| Testing    | Pest 3                               |

---

## Features

- **Role-Based Access Control** -- Four roles (Super Admin, HR Admin, Manager, Employee) with granular per-module permissions
- **Employee Management** -- Full CRUD, document uploads (Aadhar, PAN, offer letters, contracts), department assignment, employment types
- **Attendance Tracking** -- Daily check-in/check-out, present/absent/half-day/WFH statuses, CSV export, reports
- **Leave Management** -- Multiple leave types (CL, SL, EL) with carry-forward support, application workflow with manager/HR approval
- **Payroll Processing** -- Salary structures with HRA/transport/other allowances, deductions (PF, ESI, TDS), salary slip PDF download
- **Task Management** -- Assign tasks with priority and due dates, status tracking (todo/in-progress/completed)
- **Holiday Calendar** -- National, regional, and company-specific holidays
- **Configurable SMTP** -- Admin-managed email settings stored in database
- **Dashboard** -- Role-appropriate overview with charts and key metrics

---

## Project Setup

### Prerequisites

- PHP >= 8.2
- Composer
- Node.js >= 18 & npm
- MySQL 8.x
- XAMPP (or any Apache/MySQL stack)

### Installation

```bash
# 1. Clone the repository
git clone <repository-url>
cd hrms

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Environment setup
cp .env.example .env
php artisan key:generate

# 5. Configure database in .env
#    DB_DATABASE=hrms
#    DB_USERNAME=root
#    DB_PASSWORD=

# 6. Create the database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS hrms"

# 7. Run migrations and seed demo data
php artisan migrate --seed

# 8. Create storage symlink (for document uploads)
php artisan storage:link

# 9. Build frontend assets
npm run build        # Production build
# OR
npm run dev          # Development with hot reload

# 10. Start the server
php artisan serve    # http://localhost:8000
```

**Quick setup** (alternative to steps 2-9):
```bash
composer run setup
```

### Development Server

Run all services concurrently (web server, queue worker, Vite):
```bash
composer run dev
```

---

## Default Login Credentials

| Role        | Email            | Password   |
|-------------|------------------|------------|
| Super Admin | admin@hrms.com   | password   |
| HR Admin    | hr@hrms.com      | password   |
| Manager     | manager@hrms.com | password   |
| Employee    | priya@hrms.com   | password   |
| Employee    | amit@hrms.com    | password   |
| Employee    | neha@hrms.com    | password   |

---

## .env.example

See [`.env.example`](.env.example) for all environment variables. Key sections:

| Variable Group | Purpose                          |
|----------------|----------------------------------|
| `APP_*`        | Application name, URL, debug     |
| `DB_*`         | MySQL connection                 |
| `MAIL_*`       | Email configuration (default: log) |
| `SESSION_*`    | Session driver (database)        |
| `QUEUE_*`      | Queue driver (database)          |
| `CACHE_*`      | Cache driver (database)          |

---

## Database Schema (ERD)

```
┌──────────────────────┐       ┌──────────────────────┐
│        users         │       │     departments      │
├──────────────────────┤       ├──────────────────────┤
│ id (PK)              │       │ id (PK)              │
│ name                 │       │ name (unique)        │
│ email (unique)       │       │ description          │
│ password             │       │ manager_id (FK) ─────┼──┐
│ role (enum)          │       │ timestamps           │  │
│ is_active            │       └──────────────────────┘  │
│ email_verified_at    │                                  │
│ deleted_at           │                                  │
│ timestamps           │                                  │
└──────────┬───────────┘                                  │
           │ 1:1                                          │
           ▼                                              │
┌──────────────────────┐                                  │
│      employees       │◄─────────────────────────────────┘
├──────────────────────┤
│ id (PK)              │
│ user_id (FK)         │──► users.id
│ department_id (FK)   │──► departments.id
│ employee_code (uniq) │
│ designation          │
│ date_of_birth        │
│ date_of_joining      │
│ phone, address       │
│ photo                │
│ aadhar_number        │
│ pan_number           │
│ employment_type      │    (full_time|part_time|contract)
│ status               │    (active|inactive|terminated)
│ deleted_at           │
│ timestamps           │
└──┬───┬───┬───┬───┬───┘
   │   │   │   │   │
   │   │   │   │   │    ┌──────────────────────┐
   │   │   │   │   └───►│ employee_documents   │
   │   │   │   │        ├──────────────────────┤
   │   │   │   │        │ id (PK)              │
   │   │   │   │        │ employee_id (FK)     │
   │   │   │   │        │ type, title          │
   │   │   │   │        │ path, original_name  │
   │   │   │   │        │ mime_type, size       │
   │   │   │   │        │ timestamps           │
   │   │   │   │        └──────────────────────┘
   │   │   │   │
   │   │   │   │         ┌──────────────────────┐
   │   │   │   └────────►│       tasks          │
   │   │   │             ├──────────────────────┤
   │   │   │             │ id (PK)              │
   │   │   │             │ assigned_to (FK)     │──► employees.id
   │   │   │             │ assigned_by (FK)     │──► users.id
   │   │   │             │ title, description   │
   │   │   │             │ status               │   (todo|in_progress|completed)
   │   │   │             │ priority             │   (low|medium|high)
   │   │   │             │ due_date             │
   │   │   │             │ timestamps           │
   │   │   │             └──────────────────────┘
   │   │   │
   │   │   │  ┌──────────────────────────────────────────────────────┐
   │   │   │  │              PAYROLL SYSTEM                          │
   │   │   │  │                                                      │
   │   │   │  │  ┌──────────────────────┐  ┌──────────────────────┐  │
   │   │   └──┼─►│  salary_structures   │  │    salary_slips      │  │
   │   │      │  ├──────────────────────┤  ├──────────────────────┤  │
   │   │      │  │ id (PK)              │  │ id (PK)              │  │
   │   │      │  │ employee_id (FK)     │  │ employee_id (FK)     │  │
   │   │      │  │ basic                │  │ month, year          │  │
   │   │      │  │ hra                  │  │ basic, hra           │  │
   │   │      │  │ transport_allowance  │  │ transport_allowance  │  │
   │   │      │  │ other_allowances     │  │ other_allowances     │  │
   │   │      │  │ effective_from       │  │ gross_salary         │  │
   │   │      │  │ is_active            │  │ pf_employee/employer │  │
   │   │      │  │ timestamps           │  │ esi_employee/employer│  │
   │   │      │  └──────────────────────┘  │ tds                  │  │
   │   │      │                            │ total_deductions     │  │
   │   └──────┼───────────────────────────►│ net_salary           │  │
   │          │                            │ working/present_days │  │
   │          │                            │ status               │  │
   │          │                            │ generated_by (FK)    │  │
   │          │                            │ timestamps           │  │
   │          │                            └──────────────────────┘  │
   │          └──────────────────────────────────────────────────────┘
   │
   │  ┌──────────────────────────────────────────────────────────────┐
   │  │              LEAVE SYSTEM                                    │
   │  │                                                              │
   │  │  ┌──────────────────┐                                        │
   │  │  │   leave_types    │                                        │
   │  │  ├──────────────────┤                                        │
   │  │  │ id (PK)          │                                        │
   │  │  │ name, code       │                                        │
   │  │  │ max_days_per_year│                                        │
   │  │  │ is_carry_forward │                                        │
   │  │  │ timestamps       │                                        │
   │  │  └────┬─────────────┘                                        │
   │  │       │                                                      │
   │  │       ▼                                                      │
   │  │  ┌──────────────────────┐  ┌──────────────────────┐          │
   │  │  │   leave_balances     │  │  leave_applications   │         │
   │  │  ├──────────────────────┤  ├──────────────────────┤          │
   │  │  │ id (PK)              │  │ id (PK)              │          │
   ├──┼─►│ employee_id (FK)     │  │ employee_id (FK)     │◄─── ────┤
   │  │  │ leave_type_id (FK)   │  │ leave_type_id (FK)   │          │
   │  │  │ year                 │  │ from_date, to_date   │          │
   │  │  │ allocated            │  │ total_days           │          │
   │  │  │ carried_forward      │  │ reason               │          │
   │  │  │ used, pending        │  │ status               │          │
   │  │  │ balance (computed)   │  │ approved_by (FK)     │──► users │
   │  │  │ timestamps           │  │ approved_at          │          │
   │  │  └──────────────────────┘  │ rejection_reason     │          │
   │  │                            │ timestamps           │          │
   │  │                            └──────────────────────┘          │
   │  └──────────────────────────────────────────────────────────────┘
   │
   │         ┌──────────────────────┐
   └────────►│    attendances       │
             ├──────────────────────┤
             │ id (PK)              │
             │ employee_id (FK)     │──► employees.id
             │ date                 │
             │ status               │   (present|absent|half_day|wfh)
             │ check_in, check_out  │
             │ remarks              │
             │ marked_by (FK)       │──► users.id
             │ timestamps           │
             └──────────────────────┘


┌──────────────────────┐       ┌──────────────────────┐
│      holidays        │       │   role_permissions   │
├──────────────────────┤       ├──────────────────────┤
│ id (PK)              │       │ id (PK)              │
│ name                 │       │ role                 │
│ date (unique)        │       │ module               │
│ type                 │       │ can_view             │
│ timestamps           │       │ can_edit             │
└──────────────────────┘       │ can_delete           │
                               │ can_manage           │
┌──────────────────────┐       │ timestamps           │
│    smtp_settings     │       └──────────────────────┘
├──────────────────────┤
│ id (PK)              │
│ is_enabled           │
│ mailer, host, port   │
│ username, password   │   (encrypted)
│ encryption           │
│ from_address/name    │
│ timestamps           │
└──────────────────────┘
```

### Table Relationships Summary

| Relationship                        | Type        | Cascade     |
|-------------------------------------|-------------|-------------|
| users -> employees                  | One-to-One  | Delete      |
| departments -> employees            | One-to-Many | Delete      |
| departments.manager_id -> employees | Many-to-One | Set Null    |
| employees -> attendances            | One-to-Many | Delete      |
| employees -> leave_balances         | One-to-Many | Delete      |
| employees -> leave_applications     | One-to-Many | Delete      |
| employees -> salary_structures      | One-to-Many | Delete      |
| employees -> salary_slips           | One-to-Many | Delete      |
| employees -> tasks                  | One-to-Many | Delete      |
| employees -> employee_documents     | One-to-Many | Delete      |
| leave_types -> leave_balances       | One-to-Many | Delete      |
| leave_types -> leave_applications   | One-to-Many | Restrict    |
| users -> leave_applications         | One-to-Many | Set Null    |
| users -> salary_slips               | One-to-Many | Set Null    |
| users -> tasks                      | One-to-Many | Set Null    |
| users -> attendances                | One-to-Many | Set Null    |

---

## Role Permissions Matrix

| Module       | Super Admin | HR Admin          | Manager       | Employee   |
|--------------|-------------|-------------------|---------------|------------|
| Users        | Full        | --                | --            | --         |
| Employees    | Full        | View/Edit/Delete  | View          | View       |
| Departments  | Full        | View/Edit/Delete  | --            | --         |
| Attendance   | Full        | View/Edit/Manage  | View          | View       |
| Leaves       | Full        | View/Edit/Manage  | View/Edit     | View/Edit  |
| Payroll      | Full        | View/Edit/Manage  | View          | View       |
| Tasks        | Full        | View/Edit/Manage  | Full          | View/Edit  |
| Holidays     | Full        | View/Edit/Manage  | View          | View       |
| Permissions  | Full        | --                | --            | --         |
| SMTP Settings| Full        | --                | --            | --         |

---

## Project Structure

```
hrms/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Employee, Holiday, SmtpSetting, User controllers
│   │   │   ├── Auth/           # Login, password reset
│   │   │   ├── Employee/       # Leave applications (self-service)
│   │   │   └── HR/             # Attendance, Leave approval, Payroll
│   │   ├── Middleware/         # RoleMiddleware, CheckPermission
│   │   └── Requests/          # Form request validation
│   ├── Models/                # Eloquent models
│   └── Services/              # SmtpConfigurator service
├── database/
│   ├── migrations/            # All table schemas
│   ├── seeders/               # Demo data seeders
│   └── factories/             # Model factories
├── resources/views/
│   ├── layouts/               # App layout with sidebar
│   ├── components/            # Reusable Blade components
│   ├── employees/             # Employee CRUD views
│   ├── attendance/            # Attendance views
│   ├── leaves/                # Leave application views
│   ├── payroll/               # Payroll & salary slip views
│   ├── tasks/                 # Task management views
│   ├── holidays/              # Holiday calendar views
│   ├── users/                 # User management views
│   └── settings/              # SMTP settings views
├── routes/
│   └── web.php                # All application routes
├── public/                    # Public assets
├── storage/                   # Uploaded documents, logs
└── tests/                     # Pest test suite
```

---

## Assumptions

1. **Single-tenant system** -- Designed for a single organization; no multi-tenancy support.
2. **Indian payroll context** -- Deductions include PF (Provident Fund), ESI (Employee State Insurance), and TDS (Tax Deducted at Source); document types include Aadhar and PAN.
3. **Working days** -- No built-in weekend/shift configuration; attendance is marked manually per day.
4. **Leave balances** -- Allocated annually per leave type; carry-forward is supported for Earned Leave only (configurable per type).
5. **Salary calculation** -- Based on salary structure + attendance (present days vs working days); no overtime or bonus calculations.
6. **Single currency** -- All monetary values are in INR (Indian Rupees) with no multi-currency support.
7. **Session-based auth** -- No API/token authentication; designed for browser-based access only.
8. **Local file storage** -- Employee documents are stored on the local filesystem (configurable to S3 via `.env`).
9. **MySQL required** -- While Laravel supports multiple databases, migrations use MySQL-specific features; MySQL 8.x is required.
10. **Email is optional** -- Mail defaults to `log` driver; SMTP can be configured via the admin panel without redeploying.

---

## Running Tests

```bash
php artisan test
# or
./vendor/bin/pest
