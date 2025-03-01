<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Constraint;

use Attribute;
use Override;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class ValidEpochsCollection extends Constraint
{
    public string $startsWithZeroMessage   = 'The first epoch must begin with year 0.';
    public string $overlappingMessage      = 'Epochs overlap at year {{ end }} to {{ start }}.';
    public string $gapMessage              = 'Gap between epochs at year {{ end }} to {{ start }}.';
    public string $lastEpochEndMessage     = 'Only the last epoch can have an undefined end year.';
    public string $lastEpochNullEndMessage = 'The last epoch must have an undefined end year.';
    public string $emptyCollectionMessage  = 'At least one epoch must be defined.';

    #[Override]
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
