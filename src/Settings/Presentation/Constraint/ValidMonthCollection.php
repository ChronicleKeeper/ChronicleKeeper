<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Constraint;

use Attribute;
use Override;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ValidMonthCollection extends Constraint
{
    public string $emptyCollectionMessage      = 'Es muss mindestens ein Monat definiert werden.';
    public string $duplicateIndicesMessage     = 'Die Indizes der Monate müssen eindeutig sein.';
    public string $nonSequentialIndicesMessage = 'Die Indizes der Monate müssen sequentiell sein. Erwarteter Index: {{ expected }}, tatsächlicher Index: {{ actual }}.';

    #[Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    #[Override]
    public function validatedBy(): string
    {
        return ValidMonthCollectionValidator::class;
    }
}
