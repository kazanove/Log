<?php

declare(strict_types=1);

namespace CodeX\Log;

use CodeX\Log\Handler\Handler;

/**
 * Именованный канал логирования с набором обработчиков.
 */
final class Channel
{
    /**
     * @var array<int, Handler>
     */
    private array $handlers = [];

    public function __construct(
        public readonly string $name,
        private readonly Level $minLevel = Level::Debug,
    ) {
    }

    /**
     * Добавляет обработчик в канал.
     */
    public function pushHandler(Handler $handler): self
    {
        $this->handlers[] = $handler;
        return $this;
    }

    /**
     * Записывает сообщение в канал.
     */
    public function log(Level $level, string $message, array $context = []): void
    {
        if ($level->value > $this->minLevel->value) {
            return;
        }

        $record = new LogRecord(
            level: $level,
            message: $message,
            context: $context,
            channel: $this->name,
        );

        foreach ($this->handlers as $handler) {
            if ($handler->isHandling($record)) {
                $handler->handle($record);
            }
        }
    }
}