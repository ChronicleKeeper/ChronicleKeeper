<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Constraint;

use Attribute;
use Override;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class ValidCalendarDate extends Constraint
{
    public string $message = 'Das Datum "{{ date }}" ist, basierend auf deinen Einstellungen, kein gültiges Datum.';

    #[Override]
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
