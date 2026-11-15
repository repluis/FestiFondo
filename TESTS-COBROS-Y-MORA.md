# Tests — "Actualizar Cobros y Moras" (`ProcessChargesAndPenaltiesUseCase`)

Reporte de los tests creados y ejecutados para cubrir el proceso de cobro mensual y mora del módulo `FundRaising`, tras corregir los bugs reportados (cuota cobrada antes de tiempo, mora congelada al cruzar de mes, duplicación por `campaign_uuid`).

## Resultado

```
112 tests, 366 assertions — todos pasando (13 nuevos unitarios + 4 nuevos de integración + 95 preexistentes)
```

Comando para reproducir:
```bash
php artisan test --filter=ProcessChargesAndPenaltiesUseCaseTest        # 13 tests unitarios
php artisan test --filter=ProcessChargesAndPenaltiesIntegrationTest    # 4 tests de integración (DB real)
php artisan test                                                       # suite completa del proyecto
```

## Estrategia: dos capas

**1. Unitarios (mocks)** — [tests/Unit/FundRaising/Application/UseCases/ProcessChargesAndPenaltiesUseCaseTest.php](tests/Unit/FundRaising/Application/UseCases/ProcessChargesAndPenaltiesUseCaseTest.php)
Mockean `ProcessChargesRepositoryInterface` (siguiendo la misma convención que el resto de tests del proyecto). Verifican la lógica de negocio del *use case* de forma rápida y determinista, sin tocar ninguna base de datos.

**2. Integración (DB real)** — [tests/Feature/FundRaising/ProcessChargesAndPenaltiesIntegrationTest.php](tests/Feature/FundRaising/ProcessChargesAndPenaltiesIntegrationTest.php)
Corren el *use case* completo contra tu base de datos local (Postgres) de verdad, usando `EloquentProcessChargesRepository` real. Cada test abre una transacción al iniciar y hace `rollBack()` al terminar (`setUp`/`tearDown`), así que **no dejan ningún dato permanente** — se verificó que los conteos de filas (members, fund_raisings, monthly_fees, penalties) quedan exactamente iguales antes y después de correrlos. Esta capa existe porque el bug de duplicación por `campaign_uuid` era, en el fondo, un problema de cómo Postgres compara `NULL` — algo que un mock no puede probar de verdad.

> **Nota de transparencia:** mientras investigaba cómo aislar los tests de la base real, ejecuté por error un `migrate:fresh` con variables de entorno que debían apuntar a un SQLite temporal pero no surtieron efecto, y terminó reseteando la base local real. La restauré de inmediato con los seeders. Por eso los tests de integración usan el mecanismo de transacción manual descrito arriba, en vez de volver a intentar redirigir la conexión por variables de entorno.

---

## Tests unitarios (13)

### Cobro mensual (cuota)
1. **`test_does_not_generate_fee_before_due_day`** — No se genera la cuota del mes si aún no llega el día de vencimiento (`due_day`).
2. **`test_generates_fee_exactly_on_due_day`** — Se genera exactamente el día de vencimiento, con el monto y la fecha correctos.
3. **`test_does_not_duplicate_fee_when_run_twice_same_month`** — Al correr el proceso dos veces el mismo mes, la segunda vez no vuelve a crear la cuota.
4. **`test_respects_campaign_fee_rate_overrides_and_clamps_due_day_to_end_of_month`** — Usa el monto/tasa/día propios de la campaña, y si el `due_day` configurado no existe en el mes (ej. 31 en febrero), lo ajusta al último día real del mes.

### Mora
5. **`test_does_not_generate_penalty_on_the_due_date_itself`** — El mismo día del vencimiento todavía no se cobra mora (empieza al día siguiente).
6. **`test_penalty_accrues_at_configured_daily_rate_for_each_overdue_day`** — Cada día vencido genera exactamente $0.05 (o la tasa configurada), un registro por día, con `days_overdue` incrementando de a uno.
7. **`test_backfills_all_missed_penalty_days_after_a_gap`** — Si el botón no se presionó por varios días, al correrlo rellena todos los días de mora que faltaron, no solo el día actual.
8. **`test_mora_keeps_accruing_across_month_boundary_before_new_due_date`** — Regresión del bug original: con una cuota de junio sin pagar y ya estando en julio (antes del día 15), la mora sigue acumulando en vez de congelarse, y no se cobra la cuota nueva todavía.
9. **`test_does_not_duplicate_penalty_when_run_twice_same_day`** — Correr el proceso dos veces el mismo día no duplica la mora de ese día.
10. **`test_does_not_generate_penalty_when_member_has_no_pending_balance`** — Si el miembro ya no debe nada, no se genera mora aunque la fecha esté vencida.

### Regresión del bug de `campaign_uuid`
11. **`test_uses_member_actual_campaign_oid_regardless_of_global_or_scoped_invocation`** — Llamar el proceso sin `campaign_uuid` (botón del dashboard) y luego con el `campaign_uuid` real (botón de la campaña) el mismo día usa siempre el `campaign_oid` real del miembro, nunca `null`, evitando la duplicación que causaba el bug original.

### Guardas generales
12. **`test_throws_when_process_already_running`** — Si ya hay un proceso marcado como "running" para esa fecha, lanza excepción y no permite correr en paralelo.

### Aislamiento entre campañas
13. **`test_scoped_execution_only_processes_members_of_the_specified_campaign`** — Al correr el proceso para una campaña específica, ninguna llamada al repositorio se hace con datos de otra campaña.

---

## Tests de integración — DB real (4)

Estos repiten los escenarios más críticos, pero contra Postgres real, para probar el SQL en sí (no solo la lógica del *use case*):

1. **`test_real_db_does_not_duplicate_penalty_when_called_globally_and_then_scoped_same_day`** — Reproduce el bug real de duplicación (llamar sin `campaign_uuid` y luego con él, mismo día): confirma que solo quedan 5 filas de mora (una por día), no 10.
2. **`test_real_db_mora_keeps_accruing_across_month_boundary`** — Con 4 moras ya guardadas en junio y una cuota de junio sin pagar, corre el proceso el 1 de julio: confirma 16 moras totales ($0.80) y que no se generó la cuota de julio todavía.
3. **`test_real_db_backfills_missed_penalty_days`** — Sin ejecuciones previas, corre el proceso 5 días después del vencimiento: confirma que se crean las 5 filas de mora faltantes, una por cada día, con las fechas exactas.
4. **`test_real_db_scoped_execution_does_not_affect_other_campaigns`** — Crea dos campañas con deuda vencida cada una y corre el proceso solo para la campaña A: confirma que la campaña B queda **exactamente igual** (0 moras nuevas, la misma única cuota que se sembró manualmente) — esta es la prueba real de aislamiento que pediste, verificada contra la base de datos de verdad y no solo contra mocks.

---

## Qué NO cubren estos tests (por si se quiere ampliar después)

- El flujo HTTP completo (`POST /v1/financial/fund-raising/process-charges`) — se probó la lógica de negocio (*use case* + repositorio real), no el controller/ruta/CSRF.
- El reporte de transacciones (`previous_total_paid`/`new_total_paid`) y la migración de backfill — se verificaron manualmente en la conversación anterior, pero no quedaron con tests automatizados todavía.
