<?php

declare(strict_types=1);

namespace CodeX\Log;

use InvalidArgumentException;

/**
 * Уровни логирования, совместимые с PSR-3.
 * Порядок соответствует важности: от критичного к отладочному.
 */
enum Level: int
{
    case Emergency = 0;
    case Alert = 1;
    case Critical = 2;
    case Error = 3;
    case Warning = 4;
    case Notice = 5;
    case Info = 6;
    case Debug = 7;

    /**
     * Возвращает строковое имя уровня.
     */
    public function name(): string
    {
        return match ($this) {
            self::Emergency => 'emergency',
            self::Alert => 'alert',
            self::Critical => 'critical',
            self::Error => 'error',
            self::Warning => 'warning',
            self::Notice => 'notice',
            self::Info => 'info',
            self::Debug => 'debug',
        };
    }

    /**
     * Создаёт уровень из строкового имени.
     */
    public static function fromName(string $name): self
    {
        return match (strtolower($name)) {
            'emergency' => self::Emergency,
            'alert' => self::Alert,
            'critical' => self::Critical,
            'error' => self::Error,
            'warning' => self::Warning,
            'notice' => self::Notice,
            'info' => self::Info,
            'debug' => self::Debug,
            default => throw new InvalidArgumentException('Неизвестный уровень логирования: ' . $name),
        };
    }

    /**
     * Проверяет, является ли уровень ошибочным (error и выше).
     */
    public function isError(): bool
    {
        return $this->value <= self::Error->value;
    }
}