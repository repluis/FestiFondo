# FestiFondo

Aplicación web para administrar campañas de recaudación entre un grupo de personas: cuotas mensuales, mora diaria por atraso, pagos, reportes y un juego de Amigo Secreto. Está hecha con Laravel 13 y organizada por módulos con arquitectura DDD.

---

## Índice

1. [Stack](#stack)
2. [Puesta en marcha local](#puesta-en-marcha-local)
3. [Acceso y login](#acceso-y-login)
4. [Rutas](#rutas)
5. [Reglas de negocio: cuotas, mora y pagos](#reglas-de-negocio-cuotas-mora-y-pagos)
6. [Inicio y Dashboard](#inicio-y-dashboard)
7. [Amigo Secreto](#amigo-secreto)
8. [Arquitectura y módulos](#arquitectura-y-módulos)
9. [Base de datos](#base-de-datos)
10. [Tests](#tests)
11. [Despliegue (Render / Railway)](#despliegue-render--railway)
12. [Problemas frecuentes](#problemas-frecuentes)

---

## Stack

| Capa        | Tecnología                         |
|-------------|------------------------------------|
| Back-end    | PHP 8.3, Laravel 13                |
| Front-end   | Blade, Tailwind CSS 4, Vite        |
| Base de datos | PostgreSQL (local y producción) |
| Tests       | PHPUnit 12                         |
| Despliegue  | Docker (Render o Railway)          |

---

## Puesta en marcha local

Requisitos: PHP 8.3, Composer, Node 20 y PostgreSQL.

```bash
composer install
npm install
cp .env.example .env          # luego configura DB_* para tu Postgres local
php artisan key:generate
php artisan migrate
npm run build                 # o "npm run dev" mientras desarrollas
php artisan serve             # http://localhost:8000
```

Configuración de base de datos en `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=FestiFondo_db
DB_USERNAME=postgres
DB_PASSWORD=secret
```

### Datos iniciales

`GET /run-seeds?token=<SEED_TOKEN>` crea el usuario administrador, los 12 miembros, la campaña `recaudacion_navidad_2026` y su historial de pagos. Requiere la variable `SEED_TOKEN` y solo corre si la tabla `members` está vacía.

Usuario creado por el seed: `test@example.com` / `password`. **Cámbialo en producción.**

---

## Acceso y login

- Solo existen **usuarios administradores** (tabla `users`). Los miembros de las campañas no inician sesión.
- **El registro está deshabilitado**: las rutas `/register` están comentadas en `src/Auth/Infrastructure/Http/routes/web.php`. Los enlaces de registro se ocultan solos mientras la ruta no exista.
- Login en `/login`. Al iniciar sesión se vuelve a la página que se intentaba abrir, o al Inicio.
- Sin sesión, cualquier ruta protegida redirige a `/login` (configurado en `bootstrap/app.php` con `redirectGuestsTo`).
- El menú superior y el lateral ocultan las secciones privadas (Financiero, Administrativo, Reportes) a los invitados.

### Qué ve cada quien

| Página | Invitado | Usuario con sesión |
|--------|----------|--------------------|
| `/` Inicio | Portada de bienvenida **sin datos** | Resumen con datos reales |
| `/dashboard` | Redirige a `/login` | Gráficas y tablas completas |
| Módulos `v1/financial/*` y `v1/reports/*` | Redirige a `/login` | Acceso completo |
| `/v1/games/secret-santa` | Aviso: "¿Ya te llegó tu enlace?" | Panel del sorteo |
| `/v1/games/secret-santa/r/{token}` | Ve su amigo secreto (una vez) | Igual |
| `/transparencia/{uuid}` | Nombres y total pagado sin mora | Igual |

---

## Rutas

Cada módulo registra sus rutas en `src/{Módulo}/Infrastructure/Http/routes/web.php` desde su ServiceProvider. Para ver la lista completa: `php artisan route:list --except-vendor`.

### Públicas

| Método | URI | Nombre | Descripción |
|--------|-----|--------|-------------|
| GET | `/` | `home` | Portada (invitado) o resumen (usuario) |
| GET / POST | `/login` | `auth.login`, `auth.login.store` | Inicio de sesión |
| POST | `/logout` | `auth.logout` | Cerrar sesión |
| GET | `/transparencia/{uuid}` | `transparency` | Transparencia financiera de una campaña: nombres y total pagado sin mora |
| GET | `/up` | — | Health check (usado por Render/Railway) |
| GET | `/run-seeds?token=` | — | Carga de datos iniciales (protegida por `SEED_TOKEN`) |
| GET | `/v1/games/secret-santa` | `games.secret-santa` | Página del Amigo Secreto |
| GET / POST | `/v1/games/secret-santa/r/{token}` | `games.secret-santa.reveal-page`, `.reveal` | Enlace personal del participante (POST limitado a 30/min) |

### Requieren sesión

| Área | Prefijo | Qué incluye |
|------|---------|-------------|
| Dashboard | `/dashboard` | Gráficas, rankings y estado de cuenta |
| Campañas | `/v1/financial/campaigns` | CRUD, inscribir/quitar miembros, transacciones por miembro |
| Recaudación | `/v1/financial/fund-raising` | Vista general; `POST /process-charges/preview` (vista previa), `POST /process-charges` (guardar), `POST /executions/{id}/revert` (restablecer) |
| Transferencias | `/v1/financial/fund-transfers` | CRUD de transferencias |
| Miembros | `/v1/financial/members` | CRUD y activación |
| Transacciones | `/v1/financial/transactions` | Registro, `POST /apply-payment`, anulación |
| Reportes | `/v1/reports` | Índice y reporte de transacciones con filtros |
| Amigo Secreto (admin) | `/v1/games/secret-santa/...` | Integrantes, exclusiones, sortear, historial, restaurar enlaces |

---

## Reglas de negocio: cuotas, mora y pagos

Cada campaña (`fund_raisings`) define:

| Campo | Significado |
|-------|-------------|
| `target_amount` | Meta de recaudación |
| `monthly_fee_amount` | Cuota mensual por miembro |
| `due_day` | Día del mes en que vence la cuota (1–28) |
| `daily_penalty_rate` | Mora que se suma por cada día de atraso |

### Proceso de cobros (`POST /v1/financial/fund-raising/process-charges`)

`ProcessChargesAndPenaltiesUseCase` hace dos cosas por cada miembro activo de cada campaña activa:

1. **Cuota del mes**: la crea solo cuando ya llegó el día de vencimiento, y nunca la duplica.
2. **Mora**: si el miembro tiene cuotas vencidas sin pagar, crea **una mora por cada día** desde el día siguiente al vencimiento de la cuota más antigua (o desde el día siguiente a la última mora registrada) hasta hoy. Así no se pierden días aunque el proceso no se ejecute a diario.

### Vista previa, guardado y restablecer

En el detalle de una campaña, **⟳ Actualizar Cobros y Moras** funciona en dos pasos:

1. **Vista previa** (`POST .../process-charges/preview`): ejecuta el proceso real dentro de una transacción y hace **ROLLBACK** al final. Muestra cuántas cuotas y moras se crearían, por miembro y con montos, sin guardar nada.
2. **💾 Guardar** (`POST .../process-charges`): ejecuta el proceso en **una sola transacción**. Si algo falla a mitad de camino, se revierte todo y no queda nada a medias.

**↺ Restablecer última actualización** (`POST .../executions/{id}/revert`) deja la base como estaba antes. Funciona porque el proceso solo *agrega* filas (nunca modifica las existentes) y cada cuota/mora queda marcada con `generated_by_process_oid`. Borra exactamente esas filas y marca la ejecución como `reverted`. Por seguridad solo se permite:
- sobre la **última** ejecución completada de esa campaña;
- si **ninguna** de sus cuotas o moras recibió un pago (si las hay, primero hay que anular esos pagos).

### Cómo se aplica un pago (`ApplyPaymentUseCase`)

1. El dinero cubre **primero la mora** pendiente, de la más antigua a la más nueva.
2. Lo que sobra cubre **las cuotas**, de la más antigua a la más nueva.
3. Cada transacción guarda una "foto" del antes y el después: mora y cuotas pendientes, cuánto se aplicó a cada una, y el total pagado acumulado del miembro (`previous_total_paid` / `new_total_paid`).
4. Se suma el monto al `collected_amount` de la campaña.

Anular una transacción (`DELETE /v1/financial/transactions/{uuid}`) la marca con `status = false`; las transacciones anuladas no cuentan en ningún total.

---

## Inicio y Dashboard

Módulo `src/Dashboard`. Todas las cifras salen de la base en el momento de cargar la página.

**Inicio** (`/`, con sesión): indicadores, avance de cada campaña frente a su meta, top 5 de quienes más pagaron y de quienes más deben, y los últimos 8 pagos.

**Dashboard** (`/dashboard`):
- Indicadores: total recaudado, pago promedio, mejor mes, miembros al día, cuotas y mora pendientes, deuda total.
- Gráfica de recaudación de los últimos 12 meses, separada en lo aplicado a cuotas, a mora y pagos históricos.
- Donas: destino de lo recaudado, estado de los miembros y composición de la deuda.
- Rankings: quienes más pagaron, quienes menos pagaron, quienes más deben y quienes menos deben.
- Tabla con el estado de cuenta de todos los miembros.

Definiciones usadas:

| Término | Cálculo |
|---------|---------|
| Total pagado | Suma de transacciones `income` con `status = true` |
| Cuotas pendientes | Suma de `balance` de `monthly_fees` en estado `pending` o `partial` |
| Mora pendiente | Suma de `balance` de `penalties` en estado `pending` o `partial` |
| Al día | Deuda total (cuotas + mora) igual a 0 |
| Pendiente / Atrasado | Debe 1–2 cuotas / debe 3 o más |
| Pagos históricos | Parte de un pago que no tiene desglose cuota/mora (pagos cargados antes de que existiera el reparto automático) |

Las gráficas están hechas con HTML/CSS, sin librerías de JavaScript.

### Transparencia financiera

En el Inicio, cada campaña tiene el botón **🤝 Compartir transparencia**. En el celular abre el menú de compartir; en la computadora copia el enlace. El enlace lleva a `/transparencia/{uuid-de-la-campaña}`, una página pública (sin login ni menús del sistema) que muestra:

- el nombre de cada integrante activo de la campaña;
- el total que ha pagado **sin contar la mora** (`monto − aplicado a mora` de sus pagos válidos en esa campaña);
- el total aportado.

No muestra deudas, cuotas pendientes ni mora. El UUID de la campaña no se puede adivinar, así que solo la ve quien recibe el enlace.

---

## Amigo Secreto

Módulo `src/Games`. Menú **Juegos → Amigo Secreto**.

**El administrador** (con sesión):
1. Elige la campaña; se cargan sus integrantes activos.
2. Marca quiénes participan.
3. Agrega exclusiones ("a esta persona no le puede tocar esta otra", opcionalmente en ambos sentidos). Se guardan al momento en la base, por campaña.
4. Sortea. Hay una animación de 5 segundos y se genera un enlace privado por participante.
5. Copia cada enlace y lo envía. Ve si cada persona ya lo abrió (**Visto / Pendiente**) y puede **Restaurar** un enlace para que se vuelva a ver.
6. En **Historial de sorteos** puede reabrir cualquier sorteo anterior. Al cargar la página se abre solo el sorteo activo más reciente.

**El participante** abre su enlace, ve una animación de 3 segundos y el nombre de su amigo secreto. **Solo se puede ver una vez**: el enlace queda marcado como visto. La marca se hace desde el navegador, así que la vista previa de WhatsApp no lo gasta.

**Reglas del sorteo** (`SecretSantaDrawer`):
- Nadie se toca a sí mismo y cada persona recibe exactamente un regalo.
- Se respetan todas las exclusiones; si son imposibles de cumplir, se muestra un error claro.
- Aleatoriedad criptográfica (`random_int`), sin semilla.
- Un sorteo nuevo de la misma campaña **invalida los enlaces del anterior**.

---

## Arquitectura y módulos

Cada módulo vive en `src/{Módulo}/` con tres capas:

```
src/{Módulo}/
├── Domain/          Entidades (PHP puro), excepciones, interfaces de repositorio
├── Application/     DTOs, casos de uso (uno por acción) y el Service que usa el controller
└── Infrastructure/  Controllers, Form Requests, rutas, repositorios Eloquent, ServiceProvider
```

Flujo de una petición:

```
Ruta del módulo → Controller → Service → UseCase → Interface del repositorio → Repositorio Eloquent
                      └→ view('{Módulo}.vista', $datos)
```

| Módulo | Responsabilidad |
|--------|-----------------|
| `Auth` | Login y logout |
| `Dashboard` | Inicio y Dashboard con datos reales |
| `Members` | Miembros |
| `Campaigns` | Campañas e inscripción de miembros |
| `FundRaising` | Proceso de cuotas y mora |
| `Transactions` | Pagos y su aplicación a mora/cuotas |
| `FundTransfers` | Transferencias de fondos |
| `Reports` | Reporte de transacciones |
| `Games` | Amigo Secreto |
| `Shared` | Utilidades comunes |

Para agregar un módulo nuevo:
1. Crea `src/{Módulo}` con sus tres capas.
2. Registra su ServiceProvider en `bootstrap/app.php` (`withProviders`).
3. Crea sus vistas en `resources/views/{Módulo}/`.

Las reglas detalladas de convenciones están en `CLAUDE.md`.

---

## Base de datos

Toda tabla de dominio lleva `id → oid → uuid → status → resto de campos`:

| Columna | Tipo | Notas |
|---------|------|-------|
| `oid` | bigint | IDENTITY de PostgreSQL; es la clave que usan las relaciones |
| `uuid` | uuid | `gen_random_uuid()` (pgcrypto); se usa en las URLs |
| `status` | boolean | `true` = activo |

Tablas principales:

| Tabla | Contenido |
|-------|-----------|
| `users` | Administradores |
| `members` | Personas que aportan |
| `fund_raisings` | Campañas |
| `campaign_members` | Inscripción de miembros en campañas |
| `monthly_fees` | Cuotas mensuales |
| `penalties` | Mora diaria |
| `transactions` / `transaction_details` | Pagos y su aplicación a cada cuota/mora |
| `process_executions` | Historial del proceso de cobros |
| `secret_santa_draws` / `secret_santa_assignments` / `secret_santa_exclusions` | Amigo Secreto |

> Ojo: el `oid` de un miembro no siempre coincide con su número de identificación (por ejemplo, Luis Antonio Cedeño tiene identificación `1` y `oid` `13`).

---

## Tests

```bash
php artisan test
```

| Carpeta | Qué prueba |
|---------|------------|
| `tests/Unit/Games` | Algoritmo del sorteo: nadie se toca a sí mismo, todos reciben uno, exclusiones, aleatoriedad |
| `tests/Unit/Campaigns`, `tests/Unit/FundRaising` | Casos de uso con repositorios simulados |
| `tests/Feature/Campaigns` | Endpoints HTTP de campañas (con servicio simulado y usuario autenticado) |
| `tests/Feature/FundRaising` | Proceso de cuotas y mora contra Postgres real |
| `tests/Feature/Games` | Flujo completo del Amigo Secreto contra Postgres real |
| `tests/Feature/Dashboard` | Inicio, Dashboard, permisos y que las cifras coincidan con la base |

Los tests "contra Postgres real" usan tu base local dentro de una transacción que se revierte al terminar, así que no modifican datos.

---

## Despliegue (Render / Railway)

La imagen se construye con el `Dockerfile` y arranca con `start.sh`, que:
1. Usa `RENDER_EXTERNAL_URL` como `APP_URL` si no se definió otra.
2. Cachea la config, las rutas y las vistas.
3. Corre las migraciones.
4. Levanta el servidor en el puerto `$PORT`.

`render.yaml` es un Blueprint opcional que crea el servicio web y una base Postgres.

### Variables de entorno en producción

| Variable | Valor |
|----------|-------|
| `APP_KEY` | Generar con `php artisan key:generate --show` |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` |
| `APP_URL` | No definirla en Render (se toma sola), o tu dominio propio |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Datos de la base (o un único `DB_URL`) |
| `SESSION_DRIVER` / `CACHE_STORE` | `database` |
| `SESSION_SECURE_COOKIE` | `true` |
| `QUEUE_CONNECTION` | `sync` |
| `LOG_CHANNEL` | `stderr` |
| `SEED_TOKEN` | Token para `/run-seeds` |

> Laravel lee `DB_URL`, **no** `DATABASE_URL`. El nombre de la base en `DB_DATABASE` debe coincidir exactamente, incluidas mayúsculas y minúsculas.

### Copiar la base local a producción

```powershell
pg_dump -h 127.0.0.1 -U postgres -d FestiFondo_db -Fc --no-owner --no-acl -f festifondo.dump
pg_restore --clean --if-exists --no-owner --no-acl -d "<EXTERNAL_DATABASE_URL>" festifondo.dump
```

Esto **reemplaza** todo el contenido de la base remota.

---

## Problemas frecuentes

| Síntoma | Causa y solución |
|---------|------------------|
| Un cambio en `.env` o un módulo nuevo no se refleja | La config está cacheada en `bootstrap/cache/config.php`. Ejecuta `php artisan config:cache` (o `config:clear`). |
| `Route [auth.register] not defined` | Una vista enlaza al registro sin verificar `Route::has('auth.register')`. |
| No se puede iniciar sesión en producción | La app no está conectada a la base correcta: revisa `DB_DATABASE`/`DB_URL` y la pestaña Logs. |
| Error 419 al enviar un formulario | Falta el token CSRF o la sesión expiró; recarga la página. |
| Los enlaces del Amigo Secreto apuntan a `localhost` | `APP_URL` mal definido en producción; bórralo en Render. |
