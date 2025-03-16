<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Constraint;

use ChronicleKeeper\Calendar\Application\Query\LoadCalendar;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Throwable;

use function sprintf;

class ValidCalendarDateValidator extends ConstraintValidator
{
    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof ValidCalendarDate) {
            throw new UnexpectedTypeException($constraint, ValidCalendarDate::class);
        }

        try {
            $calendar = $this->queryService->query(new LoadCalendar());

            new CalendarDate(
                $calendar,
                $value['year'],
                $value['month'],
                $value['day'],
            );
        } catch (Throwable) {
            $this->context->buildViolation($constraint->message)
                ->setParameter(
                    '{{ date }}',
                    sprintf('%d-%d-%d', $value['year'], $value['month'], $value['day']),
                )
                ->addViolation();
        }
    }
}
