<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Constraint;

use Attribute;
use Override;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ValidLeapDayCollection extends Constraint
{
    public string $duplicateDayMessage = 'Doppelter Schalttag: Tag {{ day }} ist mehrfach definiert.';

    #[Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    #[Override]
    public function validatedBy(): string
    {
        return ValidLeapDayCollectionValidator::class;
    }
}
