<?php

declare(strict_types=1);

namespace CodeX\Log\Handler;

use CodeX\Log\LogRecord;

/**
 * Контракт обработчика записей лога.
 */
interface Handler
{
    /**
     * Обрабатывает запись лога.
     */
    public function handle(LogRecord $record): void;

    /**
     * Проверяет, обрабатывает ли обработчик данный уровень.
     */
    public function isHandling(LogRecord $record): bool;
}