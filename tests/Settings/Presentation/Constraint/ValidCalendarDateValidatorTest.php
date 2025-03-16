<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Constraint;

use ChronicleKeeper\Settings\Presentation\Constraint\ValidCalendarDate;
use ChronicleKeeper\Settings\Presentation\Constraint\ValidCalendarDateValidator;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\ExampleCalendars;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

#[CoversClass(ValidCalendarDateValidator::class)]
#[CoversClass(ValidCalendarDate::class)]
#[Small]
final class ValidCalendarDateValidatorTest extends TestCase
{
    private QueryService&MockObject $queryService;
    private ExecutionContextInterface&MockObject $context;
    private ValidCalendarDateValidator $validator;
    private ValidCalendarDate $constraint;

    protected function setUp(): void
    {
        $this->queryService = $this->createMock(QueryService::class);
        $this->context      = $this->createMock(ExecutionContextInterface::class);
        $this->validator    = new ValidCalendarDateValidator($this->queryService);
        $this->validator->initialize($this->context);
        $this->constraint = new ValidCalendarDate();
    }

    protected function tearDown(): void
    {
        unset($this->queryService, $this->context, $this->validator, $this->constraint);
    }

    #[Test]
    public function itUtilizesTheCorrectTargets(): void
    {
        self::assertSame(
            Constraint::CLASS_CONSTRAINT,
            $this->constraint->getTargets(),
        );
    }

    #[Test]
    public function itUtilizesTheCorrectValidationClass(): void
    {
        self::assertSame(
            ValidCalendarDateValidator::class,
            $this->constraint->validatedBy(),
        );
    }

    #[Test]
    public function itShouldThrowExceptionForInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate([], self::createStub(Constraint::class));
    }

    #[Test]
    public function itShouldPassValidationForValidDate(): void
    {
        $this->queryService
            ->method('query')
            ->willReturn(ExampleCalendars::getOnlyRegularDays());

        $this->context->expects($this->never())->method('buildViolation');
        $this->validator->validate(['year' => 2023, 'month' => 10, 'day' => 5], $this->constraint);
    }

    #[Test]
    public function itShouldFailValidationForInvalidDate(): void
    {
        $this->queryService
            ->method('query')
            ->willReturn(ExampleCalendars::getOnlyRegularDays());

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->message)
            ->willReturn($violationBuilder);

        $violationBuilder->expects($this->once())
            ->method('setParameter')
            ->with('{{ date }}', '12-10-32')
            ->willReturnSelf();

        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validator->validate(['year' => 12, 'month' => 10, 'day' => 32], $this->constraint);
    }
}
