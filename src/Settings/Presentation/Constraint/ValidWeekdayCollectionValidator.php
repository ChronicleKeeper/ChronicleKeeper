<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use function array_column;
use function array_unique;
use function count;
use function is_array;
use function sprintf;
use function usort;

class ValidWeekdayCollectionValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof ValidWeekdayCollection) {
            throw new UnexpectedTypeException($constraint, ValidWeekdayCollection::class);
        }

        if ($value === null) {
            return;
        }

        if (! isset($value['weekdays']) || ! is_array($value['weekdays'])) {
            return;
        }

        $weekdays = $value['weekdays'];

        // Check for empty collection
        if (count($weekdays) === 0) {
            $this->context->buildViolation($constraint->emptyCollectionMessage)->addViolation();

            return;
        }

        // Sort the weekdays by index
        usort($weekdays, static fn ($a, $b) => $a['index'] <=> $b['index']);

        // Check for uniqueness of indices
        $indices       = array_column($weekdays, 'index');
        $uniqueIndices = array_unique($indices);

        if (count($uniqueIndices) !== count($indices)) {
            $this->context->buildViolation($constraint->duplicateIndicesMessage)->addViolation();

            return;
        }

        // Check for sequential indices starting with 1
        $expectedIndex = 1;
        foreach ($weekdays as $i => $weekday) {
            if ($weekday['index'] !== $expectedIndex) {
                $this->context->buildViolation($constraint->nonSequentialIndicesMessage)
                    ->setParameter('{{ expected }}', (string) $expectedIndex)
                    ->setParameter('{{ actual }}', (string) $weekday['index'])
                    ->atPath(sprintf('weekdays[%d].index', $i))
                    ->addViolation();
                break;
            }

            $expectedIndex++;
        }
    }
}
