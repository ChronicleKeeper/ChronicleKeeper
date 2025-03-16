<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use function array_column;
use function array_count_values;
use function array_filter;
use function array_keys;
use function array_unique;
use function count;
use function is_array;
use function sprintf;

class ValidLeapDayCollectionValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof ValidLeapDayCollection) {
            throw new UnexpectedTypeException($constraint, ValidLeapDayCollection::class);
        }

        if ($value === null) {
            return;
        }

        if (! isset($value['leap_days']) || ! is_array($value['leap_days'])) {
            return;
        }

        $leapDays = $value['leap_days'];

        // Check for uniqueness of day values
        $days       = array_column($leapDays, 'day');
        $uniqueDays = array_unique($days);

        if (count($uniqueDays) === count($days)) {
            return;
        }

        // Find duplicates
        $counts     = array_count_values($days);
        $duplicates = array_filter($counts, static fn ($count) => $count > 1);

        foreach ($duplicates as $day => $count) {
            // For each duplicate day, find its positions in the array
            $positions = array_keys(array_filter(
                $leapDays,
                static fn ($leapDay) => $leapDay['day'] === $day,
            ));

            // Add violation for the second and subsequent occurrences
            for ($i = 1, $iMax = count($positions); $i < $iMax; $i++) {
                $this->context->buildViolation($constraint->duplicateDayMessage)
                    ->setParameter('{{ day }}', (string) $day)
                    ->atPath(sprintf('leap_days[%d].day', $positions[$i]))
                    ->addViolation();
            }
        }
    }
}
