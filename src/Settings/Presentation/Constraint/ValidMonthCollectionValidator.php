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

class ValidMonthCollectionValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof ValidMonthCollection) {
            throw new UnexpectedTypeException($constraint, ValidMonthCollection::class);
        }

        if ($value === null) {
            return;
        }

        if (! isset($value['months']) || ! is_array($value['months'])) {
            return;
        }

        $months = $value['months'];

        // Check for empty collection
        if (count($months) === 0) {
            $this->context->buildViolation($constraint->emptyCollectionMessage)->addViolation();

            return;
        }

        // Sort the months by index
        usort($months, static fn ($a, $b) => $a['index'] <=> $b['index']);

        // Check for uniqueness of indices
        $indices       = array_column($months, 'index');
        $uniqueIndices = array_unique($indices);

        if (count($uniqueIndices) !== count($indices)) {
            $this->context->buildViolation($constraint->duplicateIndicesMessage)->addViolation();

            return;
        }

        // Check for sequential indices starting with 1
        $expectedIndex = 1;
        foreach ($months as $i => $month) {
            if ($month['index'] !== $expectedIndex) {
                $this->context->buildViolation($constraint->nonSequentialIndicesMessage)
                    ->setParameter('{{ expected }}', (string) $expectedIndex)
                    ->setParameter('{{ actual }}', (string) $month['index'])
                    ->atPath(sprintf('months[%d].index', $i))
                    ->addViolation();
                break;
            }

            $expectedIndex++;
        }
    }
}
