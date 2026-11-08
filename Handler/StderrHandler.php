<?php

declare(strict_types=1);

namespace CodeX\Log\Handler;

use CodeX\Log\Level;
use CodeX\Log\LogRecord;
use DateTimeImmutable;
use JsonException;

/**
 * Выводит логи в stderr. Используется в консольных командах.
 */
final readonly class StderrHandler implements Handler{
    private const array COLORS = [
        'emergency' => "\033[41;37m",
        'alert' => "\033[41;37m",
        'critical' => "\033[31m",
        'error' => "\033[31m",
        'warning' => "\033[33m",
        'notice' => "\033[36m",
        'info' => "\033[32m",
        'debug' => "\033[90m",
    ];

    private const string RESET = "\033[0m";

    public function __construct(
        private Level $minLevel = Level::Info,
        private bool $useColors = true,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function handle(LogRecord $record): void
    {
        $line = $this->format($record) . PHP_EOL;

        if (defined('STDERR')) {
            fwrite(STDERR, $line);
        } else {
            echo $line;
        }
    }

    public function isHandling(LogRecord $record): bool
    {
        return $record->level->value <= $this->minLevel->value;
    }

    /**
     * @throws JsonException
     */
    private function format(LogRecord $record): string
    {
        $time = ($record->time ?? new DateTimeImmutable())
            ->format('H:i:s');

        $levelName = strtoupper($record->level->name());
        $context = $record->getContextString();
        $contextStr = $context !== '{}' ? ' ' . $context : '';

        $message = sprintf(
            '[%s] %s: %s%s',
            $time,
            $levelName,
            $record->message,
            $contextStr
        );

        if ($this->useColors && $this->supportsColors()) {
            $color = self::COLORS[$record->level->name()] ?? '';
            return $color . $message . self::RESET;
        }

        return $message;
    }

    private function supportsColors(): bool
    {
        if (getenv('NO_COLOR') !== false) {
            return false;
        }

        return PHP_SAPI === 'cli' && function_exists('stream_isatty')
            && stream_isatty(STDERR);
    }
}