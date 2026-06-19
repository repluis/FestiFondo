# Crear módulo DDD en el back-end

Crea un módulo nuevo en `src/` siguiendo la arquitectura DDD del proyecto FestiFondo.

## Uso

```
/create-module NombreModulo
```

Ejemplo: `/create-module Payments` crea `src/Payments/` con toda la estructura.

---

## Instrucciones

El argumento `$ARGUMENTS` es el nombre del módulo en PascalCase (ej. `Payments`, `FundRaising`).

Ejecuta el siguiente PowerShell para crear la estructura completa:

```powershell
$module = "$ARGUMENTS"
$base   = "c:\archivosLuis\portafolio\php\laravel\FestiFondo\src\$module"
$ns     = "Src\$module"

# 1. Crear directorios
$dirs = @(
    "Domain\Entities",
    "Domain\Exceptions",
    "Domain\Repositories",
    "Application\DTOs",
    "Application\Services",
    "Application\UseCases",
    "Infrastructure\Http\Controllers",
    "Infrastructure\Http\Requests",
    "Infrastructure\Http\routes",
    "Infrastructure\Jobs",
    "Infrastructure\Providers",
    "Infrastructure\Repositories"
)
foreach ($dir in $dirs) { New-Item -ItemType Directory -Force -Path "$base\$dir" | Out-Null }

# 2. Helpers
function Write-Class($path, $namespace, $name) {
    Set-Content -Path $path -Value "<?php`n`nnamespace $namespace;`n`nclass $name`n{`n}`n" -Encoding UTF8
}
function Write-Interface($path, $namespace, $name) {
    Set-Content -Path $path -Value "<?php`n`nnamespace $namespace;`n`ninterface $name`n{`n}`n" -Encoding UTF8
}
function Write-Exception($path, $namespace, $name, $parent) {
    Set-Content -Path $path -Value "<?php`n`nnamespace $namespace;`n`nuse Exception;`n`nclass $name extends $parent`n{`n}`n" -Encoding UTF8
}

# 3. Domain
Write-Class     "$base\Domain\Entities\$module.php"                 "$ns\Domain\Entities"      $module
Write-Class     "$base\Domain\Entities\Create$module.php"           "$ns\Domain\Entities"      "Create$module"
Write-Class     "$base\Domain\Entities\Update$module.php"           "$ns\Domain\Entities"      "Update$module"
Write-Exception "$base\Domain\Exceptions\${module}Exception.php"    "$ns\Domain\Exceptions"    "${module}Exception" "Exception"
Write-Interface "$base\Domain\Repositories\${module}RepositoryInterface.php" "$ns\Domain\Repositories" "${module}RepositoryInterface"

# 4. Application
Write-Class "$base\Application\DTOs\DTOCreate${module}Request.php"  "$ns\Application\DTOs"     "DTOCreate${module}Request"
Write-Class "$base\Application\DTOs\DTOUpdate${module}Request.php"  "$ns\Application\DTOs"     "DTOUpdate${module}Request"
Write-Class "$base\Application\DTOs\DTO${module}Response.php"       "$ns\Application\DTOs"     "DTO${module}Response"
Write-Class "$base\Application\Services\${module}Service.php"       "$ns\Application\Services" "${module}Service"
Write-Class "$base\Application\UseCases\Create${module}UseCase.php" "$ns\Application\UseCases" "Create${module}UseCase"
Write-Class "$base\Application\UseCases\Update${module}UseCase.php" "$ns\Application\UseCases" "Update${module}UseCase"
Write-Class "$base\Application\UseCases\List${module}sUseCase.php"  "$ns\Application\UseCases" "List${module}sUseCase"
Write-Class "$base\Application\UseCases\Show${module}UseCase.php"   "$ns\Application\UseCases" "Show${module}UseCase"

# 5. Infrastructure - Controllers
$ctrlNs = "$ns\Infrastructure\Http\Controllers"
Set-Content -Path "$base\Infrastructure\Http\Controllers\${module}Controller.php" -Value @"
<?php

namespace $ctrlNs;

use Illuminate\Routing\Controller;

class ${module}Controller extends Controller
{
    public function index()
    {
        return view('$module.index');
    }
}
"@ -Encoding UTF8
Write-Class "$base\Infrastructure\Http\Controllers\${module}ApiController.php" $ctrlNs "${module}ApiController"

# 6. Infrastructure - Requests
Write-Class "$base\Infrastructure\Http\Requests\Create${module}Request.php" "$ns\Infrastructure\Http\Requests" "Create${module}Request"
Write-Class "$base\Infrastructure\Http\Requests\Update${module}Request.php" "$ns\Infrastructure\Http\Requests" "Update${module}Request"

# 7. Infrastructure - Routes
$kebab = ($module -creplace '([A-Z])', '-$1').TrimStart('-').ToLower()
Set-Content -Path "$base\Infrastructure\Http\routes\web.php" -Value @"
<?php

use Illuminate\Support\Facades\Route;
use $ctrlNs\${module}Controller;

Route::prefix('v1/financial/$kebab')->middleware(['web'])->group(function () {
    Route::get('/',          [${module}Controller::class, 'index'])->name('$kebab.index');
    Route::get('/create',    [${module}Controller::class, 'create'])->name('$kebab.create');
    Route::post('/',         [${module}Controller::class, 'store'])->name('$kebab.store');
    Route::get('/{id}',      [${module}Controller::class, 'show'])->name('$kebab.show');
    Route::get('/{id}/edit', [${module}Controller::class, 'edit'])->name('$kebab.edit');
    Route::put('/{id}',      [${module}Controller::class, 'update'])->name('$kebab.update');
    Route::delete('/{id}',   [${module}Controller::class, 'destroy'])->name('$kebab.destroy');
});
"@ -Encoding UTF8
Set-Content -Path "$base\Infrastructure\Http\routes\api.php" -Value "<?php`n" -Encoding UTF8

# 8. Infrastructure - Job, Provider, Repository
Write-Class "$base\Infrastructure\Jobs\Process${module}Job.php" "$ns\Infrastructure\Jobs" "Process${module}Job"

Set-Content -Path "$base\Infrastructure\Providers\${module}ServiceProvider.php" -Value @"
<?php

namespace $ns\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class ${module}ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        `$this->loadRoutesFrom(__DIR__.'/../Http/routes/web.php');
    }
}
"@ -Encoding UTF8

Write-Class "$base\Infrastructure\Repositories\Eloquent${module}Repository.php" "$ns\Infrastructure\Repositories" "Eloquent${module}Repository"

Write-Output "Módulo $module creado: $((Get-ChildItem -Recurse -File $base).Count) archivos"
```

Después de ejecutar el script:

1. **Registra el ServiceProvider** en `bootstrap/app.php` dentro de `->withProviders([...])`:
   ```php
   \Src\{ModuleName}\Infrastructure\Providers\{ModuleName}ServiceProvider::class,
   ```

2. **Crea la vista** en `resources/views/{ModuleName}/index.blade.php`.

3. **Regenera el autoload**:
   ```bash
   composer dump-autoload
   ```

---

## Reglas de arquitectura por capa

Estas reglas se aplican siempre al escribir o revisar código en cualquier módulo.

### Repository (`Infrastructure/Repositories/`)
- **Solo** código SQL / Eloquent. Ningún otro tipo de lógica.
- Métodos atómicos: cada método hace una sola operación sobre la base de datos.
- Son llamados exclusivamente desde use cases, nunca desde el Service ni el Controller.
- No lanza excepciones de dominio; deja que los errores de DB suban naturalmente.

### Use Case (`Application/UseCases/`)
- Contiene `DB::transaction()` cuando la operación modifica datos.
- Lanza **excepciones de dominio personalizadas** (las del módulo, no excepciones genéricas de Laravel).
- Registra `Log::info()` al inicio y en cada paso relevante, con contexto útil (`['uuid' => ..., 'oid' => ...]`).
- Recibe DTOs, devuelve entidades o DTOs. No accede a `Request` ni a `Auth` directamente.
- Cada use case hace **una sola cosa**.
- Nunca instancia otros use cases con `new`; los recibe por inyección de constructor.

### Service (`Application/Services/`)
- Es la **única fachada** que el Controller llama.
- Delega todo en use cases; no contiene lógica de negocio propia.
- No inyecta repositorios directamente; solo inyecta use cases.

### Controller (`Infrastructure/Http/Controllers/`)
- Registra `Log::info()` al recibir el request (con payload/uuid) y al terminar con éxito.
- El `try/catch` vive **aquí**: captura excepciones de dominio y las convierte en respuestas HTTP.
- En los catch de excepciones de negocio esperadas agrega `Log::warning()` con datos que ayuden a identificar el problema.
- En el catch de `\Throwable` usa `Log::error()` con `error` y `trace`.
- **Cero SQL**: no usa `DB::`, `Schema::`, ni Eloquent directamente.
- Usa `$this->authUserOid()` (heredado de `BaseController`) en lugar de `auth()->user()->oid` o `Auth::id()`.
- No inyecta repositorios ni use cases directamente; solo inyecta el Service del módulo.

### Imports prohibidos en el Controller
| Import | Motivo |
|--------|--------|
| `use App\Models\User;` | Solo necesario en `BaseController` para `authUserOid()` |
| `use Illuminate\Support\Facades\Auth;` | Ídem |
| `use Illuminate\Support\Facades\DB;` | SQL no va en el Controller |
| `use Illuminate\Support\Facades\Schema;` | Ídem |
| `use ...RepositoryInterface;` | El Controller no habla con repositorios directamente |
