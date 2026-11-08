<?php

declare(strict_types=1);

namespace CodeX\Log\Handler;

use CodeX\DataBase\Connection\Manager;
use CodeX\Log\Level;
use CodeX\Log\LogRecord;
use DateTimeImmutable;
use Throwable;

/**
 * Записывает логи в таблицу базы данных.
 *
 * Ожидаемая структура таблицы:
 * CREATE TABLE logs (
 *     id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 *     channel VARCHAR(50) NOT NULL,
 *     level VARCHAR(20) NOT NULL,
 *     message TEXT NOT NULL,
 *     context JSON NULL,
 *     request_id VARCHAR(64) NULL,
 *     created_at DATETIME NOT NULL,
 *     INDEX idx_level (level),
 *     INDEX idx_channel (channel),
 *     INDEX idx_created_at (created_at)
 * );
 */
final readonly class DatabaseHandler implements Handler
{
    public function __construct(
        private Manager $manager,
        private Level   $minLevel = Level::Error,
        private string  $table = 'logs',
        private ?string $connection = null,
    ) {
    }

    public function handle(LogRecord $record): void
    {
        try {
            $pdo = $this->manager->connection($this->connection);

            $stmt = $pdo->prepare(
                'INSERT INTO ' . $this->table . '
                    (channel, level, message, context, request_id, created_at)
                 VALUES
                    (:channel, :level, :message, :context, :request_id, :created_at)'
            );

            $stmt->execute([
                'channel' => $record->channel,
                'level' => $record->level->name(),
                'message' => $record->message,
                'context' => $record->getContextString(),
                'request_id' => $record->requestId,
                'created_at' => ($record->time ?? new DateTimeImmutable())
                    ->format('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            // Резервное логирование при ошибке записи в БД
           error_log(
                'DatabaseHandler: не удалось записать лог: ' . $e->getMessage()
            );
        }
    }

    public function isHandling(LogRecord $record): bool
    {
        return $record->level->value <= $this->minLevel->value;
    }
}