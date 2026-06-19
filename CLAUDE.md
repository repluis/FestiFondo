# FestiFondo — Reglas de Arquitectura

## Stack

- **Back-end:** Laravel 13, PHP 8.3, arquitectura DDD por módulos
- **Front-end:** Blade + Tailwind CSS 4, Vite como bundler
- **Base de datos:** SQLite (desarrollo), PostgreSQL (producción)
- **Autoload namespace raíz:** `Src\` → carpeta `src/`

---

## Estructura de módulos (DDD)

Cada módulo vive en `src/{ModuleName}/` con tres capas obligatorias.

```
src/
└── {ModuleName}/
    ├── Domain/
    │   ├── Entities/          ← POPOs, sin dependencia de Laravel
    │   ├── Exceptions/        ← Extienden {ModuleName}Exception (base)
    │   └── Repositories/      ← Solo la interface (contrato)
    ├── Application/
    │   ├── DTOs/              ← Entrada/salida de datos, sin lógica
    │   ├── Services/          ← Orquesta use cases expuestos al controller
    │   └── UseCases/
    │       ├── Create{X}UseCase.php
    │       ├── List{X}UseCase.php
    │       ├── Show{X}UseCase.php
    │       ├── Update{X}UseCase.php
    │       ├── Cancel{X}UseCase.php
    │       └── Dropdowns/     ← Use cases de datos para selects/combos
    └── Infrastructure/
        ├── Http/
        │   ├── Controllers/   ← Extienden Illuminate\Routing\Controller
        │   ├── Policies/      ← Autorización por módulo
        │   ├── Requests/      ← Form Requests de Laravel
        │   └── routes/
        │       ├── web.php    ← Rutas Blade del módulo
        │       └── api.php    ← Rutas API REST del módulo
        ├── Jobs/              ← Queue jobs del módulo
        ├── Providers/         ← ServiceProvider que carga rutas y bindings
        └── Repositories/      ← Implementación Eloquent de la interface
```

---

## Reglas del back-end

### Domain
- Las entidades son clases PHP puras (POPO), sin `use Illuminate\...`.
- `{ModuleName}Exception` es la excepción base; las demás la extienden.
- La interface del repositorio define el contrato; **nunca** toca Eloquent.

### Application
- Los DTOs solo tienen propiedades y un método estático `fromArray()` o `fromRequest()`.
- Cada use case hace **una sola cosa**; recibe DTOs, devuelve entidades o DTOs.
- El Service es la fachada que el controller llama; delega en use cases.

### Infrastructure
- El `EloquentFundXxxRepository` implementa la interface del Domain.
- El controller extiende `Illuminate\Routing\Controller` e inyecta el Service.
- Las rutas web se registran bajo el prefijo `v1/{area}/{module-kebab}`.
- Las rutas **no llevan** middleware `auth` hasta que el sistema de login esté listo.

### ServiceProvider
- Carga las rutas propias del módulo en `boot()`:
  ```php
  public function boot(): void
  {
      $this->loadRoutesFrom(__DIR__.'/../Http/routes/web.php');
      // $this->loadRoutesFrom(__DIR__.'/../Http/routes/api.php');
  }
  ```
- Hace los bindings interface → implementación en `register()`.
- Se registra en `bootstrap/app.php` dentro de `->withProviders([...])`.

### Registrar un módulo nuevo en bootstrap/app.php
```php
->withProviders([
    \Src\FundTransfers\Infrastructure\Providers\FundTransferServiceProvider::class,
    \Src\FundRaising\Infrastructure\Providers\FundRaisingServiceProvider::class,
    // \Src\{ModuleName}\Infrastructure\Providers\{ModuleName}ServiceProvider::class,
])
```

---

## Estructura del front-end (Blade)

```
resources/
├── views/
│   ├── welcome.blade.php
│   └── {ModuleName}/
│       ├── index.blade.php    ← listado principal
│       ├── create.blade.php   ← formulario de creación
│       ├── edit.blade.php     ← formulario de edición
│       └── show.blade.php     ← detalle
├── css/
│   └── app.css                ← Tailwind CSS 4 (@source directives)
└── js/
    └── app.js
```

- El nombre de la carpeta en `views/` debe coincidir **exactamente** con el nombre del módulo en `src/`.
- Las vistas se referencian como `'{ModuleName}.index'`, `'{ModuleName}.create'`, etc.

---

## Conexión back → front

```
HTTP Request
    └─→ routes/web.php del módulo          (prefix v1/...)
            └─→ {ModuleName}Controller      (Infrastructure/Http/Controllers)
                    └─→ {ModuleName}Service (Application/Services)
                            └─→ UseCase     (Application/UseCases)
                                    └─→ Repository Interface  (Domain)
                                                └─→ Eloquent Repository (Infrastructure)
                    └─→ return view('{ModuleName}.index', $data)
                                    └─→ resources/views/{ModuleName}/index.blade.php
```

### Convenciones de rutas web
| Método | URI                                      | Nombre                     | Acción del controller |
|--------|------------------------------------------|----------------------------|-----------------------|
| GET    | v1/{area}/{module}                       | {module}.index             | index()               |
| GET    | v1/{area}/{module}/create                | {module}.create            | create()              |
| POST   | v1/{area}/{module}                       | {module}.store             | store()               |
| GET    | v1/{area}/{module}/{id}                  | {module}.show              | show()                |
| GET    | v1/{area}/{module}/{id}/edit             | {module}.edit              | edit()                |
| PUT    | v1/{area}/{module}/{id}                  | {module}.update            | update()              |
| DELETE | v1/{area}/{module}/{id}                  | {module}.destroy           | destroy()             |

---

## Convenciones de nombres

| Elemento              | Patrón                                      | Ejemplo                            |
|-----------------------|---------------------------------------------|------------------------------------|
| Namespace módulo      | `Src\{ModuleName}\...`                      | `Src\FundTransfers\Domain\...`     |
| Entidad principal     | `{ModuleName}.php`                          | `FundTransfer.php`                 |
| Entidades de acción   | `{Action}{Entity}.php`                      | `CreateFundTransfer.php`           |
| Excepción base        | `{ModuleName}Exception.php`                 | `FundTransferException.php`        |
| Interface repositorio | `{ModuleName}RepositoryInterface.php`       | `FundTransferRepositoryInterface`  |
| Impl. repositorio     | `Eloquent{ModuleName}Repository.php`        | `EloquentFundTransferRepository`   |
| DTO request           | `DTO{Action}{Entity}Request.php`            | `DTOCreateFundTransferRequest.php` |
| DTO response          | `DTO{X}Response.php`                        | `DTOCashBankBalanceResponse.php`   |
| Use case              | `{Action}{Entity}UseCase.php`               | `CreateFundTransferUseCase.php`    |
| Service               | `{ModuleName}Service.php`                   | `FundTransferService.php`          |
| Controller web        | `{ModuleName}Controller.php`                | `FundTransferController.php`       |
| Controller API        | `{ModuleName}ApiController.php`             | `FundTransferApiController.php`    |
| Policy                | `{ModuleName}Policy.php`                    | `FundTransferPolicy.php`           |
| Form Request          | `{Action}{Entity}Request.php`               | `CreateFundTransferRequest.php`    |
| Job                   | `Process{ModuleName}Job.php`                | `ProcessFundTransferJob.php`       |
| ServiceProvider       | `{ModuleName}ServiceProvider.php`           | `FundTransferServiceProvider.php`  |
| Prefijo de ruta       | `v1/{area}/{module-kebab}`                  | `v1/financial/fund-transfers`      |
| Carpeta de vistas     | `resources/views/{ModuleName}/`             | `resources/views/FundTransfers/`   |

---

## Convenciones de base de datos (PostgreSQL)

### Columnas obligatorias en toda tabla nueva

Cada tabla que se cree **debe** incluir las columnas `oid` (auto-incrementable por identidad) y `uuid` (generado automáticamente con `pgcrypto`). Siempre se crean dos migraciones separadas: una para `oid` y otra para `uuid`.

#### Migración 1 — `oid` como IDENTITY

```php
// yyyy_mm_dd_xxxxxx_add_oid_identity_to_{table}_table.php
public function up(): void
{
    if (!Schema::hasColumn('{table}', 'oid')) {
        Schema::table('{table}', function (Blueprint $table) {
            $table->bigInteger('oid')->nullable()->after('id');
        });
    }
    // PostgreSQL requiere NOT NULL antes de agregar IDENTITY
    DB::statement('ALTER TABLE public.{table} ALTER COLUMN oid SET NOT NULL;');
    DB::statement('ALTER TABLE public.{table} ALTER COLUMN oid ADD GENERATED ALWAYS AS IDENTITY;');
    DB::statement("SELECT setval('{table}_oid_seq', (SELECT COALESCE(MAX(oid), 1) FROM public.{table}));");
}

public function down(): void
{
    DB::statement('ALTER TABLE public.{table} ALTER COLUMN oid DROP IDENTITY IF EXISTS;');
    if (Schema::hasColumn('{table}', 'oid')) {
        Schema::table('{table}', function (Blueprint $table) {
            $table->dropColumn('oid');
        });
    }
}
```

#### Migración 2 — `uuid` con pgcrypto

```php
// yyyy_mm_dd_xxxxxx_add_uuid_to_{table}_table.php
public function up(): void
{
    if (!Schema::hasColumn('{table}', 'uuid')) {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');
        Schema::table('{table}', function (Blueprint $table) {
            $table->uuid('uuid')
                  ->default(DB::raw('gen_random_uuid()'))
                  ->unique()
                  ->after('oid');
        });
        DB::statement('UPDATE {table} SET uuid = gen_random_uuid() WHERE uuid IS NULL;');
    }
}

public function down(): void
{
    if (Schema::hasColumn('{table}', 'uuid')) {
        Schema::table('{table}', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
}
```

#### Migración 3 — `status` booleano

```php
// yyyy_mm_dd_xxxxxx_add_status_to_{table}_table.php
public function up(): void
{
    if (!Schema::hasColumn('{table}', 'status')) {
        Schema::table('{table}', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('uuid');
        });
    }
}

public function down(): void
{
    if (Schema::hasColumn('{table}', 'status')) {
        Schema::table('{table}', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
```

> **Reglas:**
> - Nunca colapsar estas migraciones en una sola; deben ser archivos independientes para facilitar el rollback granular.
> - `status` siempre es `boolean` con `default(true)` — activo por defecto.
> - Orden de columnas en la tabla: `id` → `oid` → `uuid` → `status` → resto de campos.

---

## Checklist para agregar un módulo nuevo

1. Crear carpeta `src/{ModuleName}/` con las tres capas (Domain, Application, Infrastructure).
2. Definir entidades en `Domain/Entities/`.
3. Definir excepciones en `Domain/Exceptions/` (base + específicas).
4. Definir interface en `Domain/Repositories/`.
5. Crear DTOs en `Application/DTOs/`.
6. Crear use cases en `Application/UseCases/` (y subcarpeta `Dropdowns/` si aplica).
7. Crear service en `Application/Services/`.
8. Implementar `Eloquent{X}Repository` en `Infrastructure/Repositories/`.
9. Crear controllers en `Infrastructure/Http/Controllers/`.
10. Crear form requests en `Infrastructure/Http/Requests/`.
11. Crear policy en `Infrastructure/Http/Policies/`.
12. Definir rutas en `Infrastructure/Http/routes/web.php` (y `api.php` si aplica).
13. Crear `{ModuleName}ServiceProvider` que cargue las rutas y registre los bindings.
14. Registrar el ServiceProvider en `bootstrap/app.php` dentro de `->withProviders([...])`.
15. Crear carpeta y vistas en `resources/views/{ModuleName}/`.
16. Crear migración `add_oid_identity_to_{table}_table.php` para la tabla principal del módulo.
17. Crear migración `add_uuid_to_{table}_table.php` para la tabla principal del módulo.
18. Crear migración `add_status_to_{table}_table.php` con `boolean status default true` para la tabla principal del módulo.
