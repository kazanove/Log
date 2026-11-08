<?php

declare(strict_types=1);

namespace CodeX\Log\Handler;

use CodeX\Log\Level;
use CodeX\Log\LogRecord;
use DateTimeImmutable;
use JsonException;
use RuntimeException;

/**
 * Записывает логи в файл с поддержкой ежедневной ротации.
 */
final readonly class FileHandler implements Handler
{
    public function __construct(
        private string $filePath,
        private Level  $minLevel = Level::Debug,
        private bool   $daily = true,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function handle(LogRecord $record): void
    {
        $path = $this->resolvePath();
        $dir = dirname($path);

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException(
                'Не удалось создать директорию для логов: ' . $dir
            );
        }

        $line = $this->format($record) . PHP_EOL;
        $result = file_put_contents($path, $line, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            error_log('Логгер: не удалось записать в файл: ' . $path);
        }
    }

    public function isHandling(LogRecord $record): bool
    {
        return $record->level->value <= $this->minLevel->value;
    }

    private function resolvePath(): string
    {
        if (!$this->daily) {
            return $this->filePath;
        }

        $info = pathinfo($this->filePath);
        $dir = $info['dirname'] ?? '.';
        $filename = $info['filename'] ?? 'log';
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';

        return $dir . DIRECTORY_SEPARATOR
            . $filename . '-' . date('Y-m-d') . $extension;
    }

    /**
     * @throws JsonException
     */
    private function format(LogRecord $record): string
    {
        $time = ($record->time ?? new DateTimeImmutable())
            ->format('Y-m-d H:i:s');

        $context = $record->getContextString();
        $contextStr = $context !== '{}' ? ' ' . $context : '';

        return $record->level->name()
                |> strtoupper(...)
                |> (static fn($x) => sprintf('[%s] %s.%s: %s%s', $time, $record->channel, $x, $record->message, $contextStr));
    }
}