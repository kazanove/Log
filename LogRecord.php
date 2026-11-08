<?php

declare(strict_types=1);

namespace CodeX\Log;

use DateTimeImmutable;
use JsonException;

/**
 * Неизменяемая запись лога.
 */
final readonly class LogRecord
{
    public function __construct(
        public Level $level,
        public string $message,
        public array $context = [],
        public string $channel = 'app',
        public ?DateTimeImmutable $time = null,
        public string $requestId = '',
    ) {
    }

    /**
     * Форматирует контекст для логирования.
     * @throws JsonException
     */
    public function getContextString(): string
    {
        if ($this->context === []) {
            return '{}';
        }

        $result = json_encode($this->context, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $result !== false ? $result : '{}';
    }
}