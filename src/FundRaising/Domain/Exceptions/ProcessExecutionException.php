<?php

namespace Src\FundRaising\Domain\Exceptions;

/**
 * No se puede restablecer una ejecución del proceso de cobros.
 */
class ProcessExecutionException extends FundRaisingException
{
    public static function notFound(): self
    {
        return new self('La ejecución no existe.');
    }

    public static function notCompleted(string $status): self
    {
        return new self("Solo se puede restablecer una ejecución completada (estado actual: {$status}).");
    }

    public static function notLatest(): self
    {
        return new self('Solo se puede restablecer la última actualización de cobros y moras. Restablece primero la más reciente.');
    }

    public static function hasPayments(int $rows): self
    {
        return new self("No se puede restablecer: {$rows} cuota(s) o mora(s) de esta actualización ya recibieron pagos. Anula esos pagos primero.");
    }
}
