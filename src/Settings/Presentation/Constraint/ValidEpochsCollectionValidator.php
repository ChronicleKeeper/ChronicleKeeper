<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Constraint;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use function count;
use function sprintf;

/**
 * @phpstan-type EpochSettingsArray = array{
 *      name: string,
 *      start_year: int<0, max>|null,
 *      end_year: int<0, max>|null
 *  }
 */
class ValidEpochsCollectionValidator extends ConstraintValidator
{
    public function __construct(private readonly SettingsHandler $settingsHandler)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof ValidEpochsCollection) {
            throw new UnexpectedTypeException($constraint, ValidEpochsCollection::class);
        }

        if ($value === null || ! isset($value['epochs'])) {
            return;
        }

        /** @var list<EpochSettingsArray> $epochs */
        $epochs = $value['epochs'];

        if ($epochs === []) {
            $this->context->buildViolation($constraint->emptyCollectionMessage)->addViolation();

            return;
        }

        // Check first epoch starts with year official calendar start
        $startsInYear = $this->settingsHandler->get()->getCalendarSettings()->getBeginsInYear();
        if ($epochs[0]['start_year'] !== $startsInYear) {
            $this->context->buildViolation($constraint->startsWithCalendarBeginMessage)
                ->setParameter('{{ calendarBeginsInYear }}', (string) $startsInYear)
                ->atPath('epochs[0].start_year')
                ->addViolation();
        }

        $lastEnd    = null;
        $epochCount = count($epochs);

        foreach ($epochs as $index => $epoch) {
            // Check if current epoch starts after previous one ended
            if ($lastEnd !== null && $epoch['start_year'] <= $lastEnd) {
                $this->context->buildViolation($constraint->overlappingMessage)
                    ->setParameter('{{ end }}', (string) $lastEnd)
                    ->setParameter('{{ start }}', (string) $epoch['start_year'])
                    ->atPath(sprintf('epochs[%d].start_year', $index))
                    ->addViolation();
            }

            // Only the last epoch should have null endYear
            if (($epoch['end_year'] === null) && $index !== $epochCount - 1) {
                $this->context->buildViolation($constraint->lastEpochEndMessage)
                    ->atPath(sprintf('epochs[%d].end_year', $index))
                    ->addViolation();
            }

            // Check for gaps between epochs
            if ($lastEnd !== null && $epoch['start_year'] > $lastEnd + 1) {
                $this->context->buildViolation($constraint->gapMessage)
                    ->setParameter('{{ end }}', (string) $lastEnd)
                    ->setParameter('{{ start }}', (string) $epoch['start_year'])
                    ->atPath(sprintf('epochs[%d].start_year', $index))
                    ->addViolation();
            }

            $lastEnd = $epoch['end_year'] ?? null;
        }

        // Ensure last epoch has no end year
        $lastEpochIndex = $epochCount - 1;
        if ($epochs[$lastEpochIndex]['end_year'] === null) {
            return;
        }

        $this->context->buildViolation($constraint->lastEpochNullEndMessage)
            ->atPath(sprintf('epochs[%d].end_year', $lastEpochIndex))
            ->addViolation();
    }
}
