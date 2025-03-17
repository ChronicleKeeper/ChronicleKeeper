<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\FlashMessages;

enum Alert: string
{
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case DANGER  = 'danger';
    case INFO    = 'info';

    public function getClass(): string
    {
        return match ($this) {
            self::SUCCESS => 'alert-success',
            self::WARNING => 'alert-warning',
            self::DANGER => 'alert-danger',
            self::INFO => 'alert-info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::SUCCESS => 'tabler:check',
            self::WARNING => 'tabler:alert-triangle',
            self::DANGER => 'tabler:alert-circle',
            self::INFO => 'tabler:info-circle',
        };
    }

    public function getIconClass(): string
    {
        return match ($this) {
            self::SUCCESS => 'fa-solid fa-circle-check',
            self::WARNING => 'fa-solid fa-triangle-exclamation',
            self::DANGER => 'fa-solid fa-circle-exclamation',
            self::INFO => 'fa-solid fa-circle-info',
        };
    }
}
