<?php

declare(strict_types=1);

namespace CodeX\Log;

use CodeX\Log\Handler\Handler;
use NoDiscard;
use Throwable;

/**
 * Центральный логгер приложения.
 *
 * Поддерживает именованные каналы с разными обработчиками.
 * Совместим с PSR-3 по сигнатурам методов.
 */
final class Logger
{
    /**
     * @var array<string, Channel>
     */
    private array $channels = [];

    /**
     * @var array<int, Handler>
     */
    private array $defaultHandlers = [];

    private Level $minLevel;

    public function __construct(
        Level $minLevel = Level::Debug,
    ) {
        $this->minLevel = $minLevel;
    }

    /**
     * Устанавливает минимальный уровень логирования.
     */
    public function setMinLevel(Level $level): void
    {
        $this->minLevel = $level;
    }

    /**
     * Добавляет обработчик по умолчанию для всех каналов.
     */
    public function pushHandler(Handler $handler): self
    {
        $this->defaultHandlers[] = $handler;
        return $this;
    }

    /**
     * Возвращает канал по имени, создавая при необходимости.
     */
    #[NoDiscard]
    public function channel(string $name = 'app'): Channel
    {
        if (!isset($this->channels[$name])) {
            $channel = new Channel($name, $this->minLevel);

            foreach ($this->defaultHandlers as $handler) {
                $channel->pushHandler($handler);
            }

            $this->channels[$name] = $channel;
        }

        return $this->channels[$name];
    }

    /**
     * Регистрирует канал с кастомными обработчиками.
     */
    public function registerChannel(string $name, array $handlers, ?Level $minLevel = null): self
    {
        $channel = new Channel($name, $minLevel ?? $this->minLevel);
        foreach ($handlers as $handler) {
            $channel->pushHandler($handler);
        }
        $this->channels[$name] = $channel;
        return $this;
    }

    // ==========================================
    // МЕТОДЫ ЛОГИРОВАНИЯ (совместимы с PSR-3)
    // ==========================================

    public function emergency(string $message, array $context = []): void
    {
        $this->log(Level::Emergency, $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log(Level::Alert, $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log(Level::Critical, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(Level::Error, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(Level::Warning, $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log(Level::Notice, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(Level::Info, $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log(Level::Debug, $message, $context);
    }

    /**
     * Записывает сообщение с указанным уровнем.
     */
    public function log(Level $level, string $message, array $context = []): void
    {
        if ($level->value > $this->minLevel->value) {
            return;
        }

        $channelName = $context['channel'] ?? 'app';
        unset($context['channel']);

        $this->channel($channelName)->log($level, $message, $context);
    }

    /**
     * Записывает исключение как ошибку.
     */
    public function exception(Throwable $e, array $context = []): void
    {
        $context['exception'] = $e::class;
        $context['message'] = $e->getMessage();
        $context['file'] = $e->getFile();
        $context['line'] = $e->getLine();

        $this->error($e->getMessage(), $context);
    }
}