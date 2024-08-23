<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\FlashMessages;

enum Alert: string
{
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case DANGER  = 'danger';
    case INFO    = 'info';

    public function getClass(): string
    {
        return match ($this) {
            Alert::SUCCESS => 'alert-success',
            Alert::WARNING => 'alert-warning',
            Alert::DANGER => 'alert-danger',
            Alert::INFO => 'alert-info',
        };
    }

    public function getIconClass(): string
    {
        return match ($this) {
            Alert::SUCCESS => 'fa-solid fa-circle-check',
            Alert::WARNING => 'fa-solid fa-triangle-exclamation',
            Alert::DANGER => 'fa-solid fa-circle-exclamation',
            Alert::INFO => 'fa-solid fa-circle-info',
        };
    }
}
