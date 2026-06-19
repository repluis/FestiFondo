# FestiFondo

A web application for managing collaborative savings campaigns ("tandas" / "vaquitas"), members, fund raisings, and financial transactions — built with Laravel 13 following a strict Domain-Driven Design (DDD) architecture.

---

## Tech Stack

| Layer      | Technology              | Version  |
|------------|-------------------------|----------|
| Backend    | PHP                     | ^8.3     |
| Backend    | Laravel Framework       | ^13.8    |
| Frontend   | Tailwind CSS            | ^4.0     |
| Frontend   | Vite                    | ^5.0     |
| Database   | SQLite (dev)            | —        |
| Database   | PostgreSQL (prod)       | —        |
| Testing    | PHPUnit                 | ^12.5    |
| Code style | Laravel Pint            | ^1.27    |

---

## What the Project Does

FestiFondo lets a group of people (members) join savings campaigns. Each campaign defines a fund-raising plan with periodic fees, penalty rules, and a target amount. The platform tracks:

- **Campaigns** — creation, configuration, member enrollment, and lifecycle management.
- **Members** — registration, balance tracking, and per-campaign status.
- **Fund Raisings** — recurring fee schedules, due dates, and rate configuration.
- **Transactions** — every monetary movement (payments, adjustments, refunds) with a full audit snapshot.
- **Fund Transfers** — internal transfers between campaign funds.
- **Reports** — transaction history, member balances, and campaign summaries.
- **Auth** — user registration, login, and session management.

---

## Architecture — Domain-Driven Design (DDD)

The codebase is organized by **business domain**, not by technical layer. Each module lives under `src/{ModuleName}/` and is split into three mandatory layers:

```
src/
└── {ModuleName}/
    ├── Domain/
    │   ├── Entities/        ← Pure PHP objects, zero Laravel dependency
    │   ├── Exceptions/      ← {ModuleName}Exception base + specific exceptions
    │   └── Repositories/    ← Interface (contract only)
    ├── Application/
    │   ├── DTOs/            ← Input/output data holders, no logic
    │   ├── Services/        ← Facade the controller calls
    │   └── UseCases/        ← One use case = one action
    └── Infrastructure/
        ├── Http/
        │   ├── Controllers/ ← Extends Illuminate\Routing\Controller
        │   ├── Policies/    ← Module-level authorization
        │   ├── Requests/    ← Laravel Form Requests
        │   └── routes/      ← web.php and api.php per module
        ├── Jobs/            ← Queue jobs
        ├── Providers/       ← ServiceProvider (routes + bindings)
        └── Repositories/    ← Eloquent implementation of the interface
```

### Request flow

```
HTTP Request
  └─→ Module routes (prefix v1/{area}/{module})
        └─→ {ModuleName}Controller
              └─→ {ModuleName}Service
                    └─→ UseCase
                          └─→ Repository Interface (Domain)
                                └─→ Eloquent Repository (Infrastructure)
              └─→ return view('{ModuleName}.index', $data)
```

---

## Modules

| Module          | Responsibility                                        |
|-----------------|-------------------------------------------------------|
| `Auth`          | User authentication and session management            |
| `Members`       | Member CRUD, balance tracking                         |
| `Campaigns`     | Campaign lifecycle, enrollment, configuration         |
| `FundRaising`   | Recurring fee schedules, due dates, rate rules        |
| `FundTransfers` | Internal fund transfers between campaigns             |
| `Transactions`  | All monetary movements with audit snapshots           |
| `Reports`       | Transaction history, member balances, summaries       |
| `Shared`        | Cross-cutting infrastructure utilities                |

---

## Project Structure

```
FestiFondo/
├── src/                        ← DDD modules (Src\ namespace)
│   ├── Auth/
│   ├── Campaigns/
│   ├── FundRaising/
│   ├── FundTransfers/
│   ├── Members/
│   ├── Reports/
│   ├── Shared/
│   └── Transactions/
├── resources/
│   ├── views/
│   │   ├── components/
│   │   │   ├── form/           ← input, select, textarea, checkbox
│   │   │   ├── layout/         ← header, sidebar, nav, footer
│   │   │   └── ui/             ← button, card, modal, badge, table…
│   │   ├── Auth/
│   │   ├── Campaigns/
│   │   ├── FundRaising/
│   │   ├── FundTransfers/
│   │   ├── Members/
│   │   └── Reports/
│   ├── css/app.css             ← Tailwind CSS 4
│   └── js/app.js
├── database/
│   └── migrations/            ← 77+ migrations (oid, uuid, status per table)
├── bootstrap/app.php          ← ServiceProvider registration
├── routes/
│   ├── web.php                ← Root routes
│   └── console.php
├── composer.json
├── package.json
└── vite.config.js
```

---

## Database Conventions

Every table includes three mandatory extra columns added via separate migrations:

| Column   | Type    | Notes                                               |
|----------|---------|-----------------------------------------------------|
| `oid`    | bigint  | PostgreSQL IDENTITY (auto-increment)                |
| `uuid`   | uuid    | Generated by `gen_random_uuid()` (pgcrypto)         |
| `status` | boolean | Default `true` (active)                             |

Column order in every table: `id → oid → uuid → status → domain fields`.

---

## Naming Conventions

| Element               | Pattern                                | Example                              |
|-----------------------|----------------------------------------|--------------------------------------|
| Module namespace      | `Src\{ModuleName}\...`                 | `Src\Campaigns\Domain\...`           |
| Repository interface  | `{ModuleName}RepositoryInterface`      | `CampaignRepositoryInterface`        |
| Eloquent repository   | `Eloquent{ModuleName}Repository`       | `EloquentCampaignRepository`         |
| DTO request           | `DTO{Action}{Entity}Request`           | `DTOCreateCampaignRequest`           |
| Use case              | `{Action}{Entity}UseCase`              | `CreateCampaignUseCase`              |
| Service               | `{ModuleName}Service`                  | `CampaignService`                    |
| Controller (web)      | `{ModuleName}Controller`               | `CampaignController`                 |
| ServiceProvider       | `{ModuleName}ServiceProvider`          | `CampaignServiceProvider`            |
| Route prefix          | `v1/{area}/{module-kebab}`             | `v1/financial/fund-transfers`        |
| Views folder          | `resources/views/{ModuleName}/`        | `resources/views/Campaigns/`         |

---

## Getting Started

```bash
# Install dependencies and set up the project
composer run setup

# Start development servers (Laravel + Vite concurrently)
composer run dev

# Run tests
composer run test
```

The `setup` script handles: `composer install`, `php artisan key:generate`, `php artisan migrate`, `npm install`, and `npm run build`.

---

## Routes

Module routes are loaded by each ServiceProvider and follow this convention:

| Method | URI                              | Action     |
|--------|----------------------------------|------------|
| GET    | v1/{area}/{module}               | index()    |
| GET    | v1/{area}/{module}/create        | create()   |
| POST   | v1/{area}/{module}               | store()    |
| GET    | v1/{area}/{module}/{id}          | show()     |
| GET    | v1/{area}/{module}/{id}/edit     | edit()     |
| PUT    | v1/{area}/{module}/{id}          | update()   |
| DELETE | v1/{area}/{module}/{id}          | destroy()  |
