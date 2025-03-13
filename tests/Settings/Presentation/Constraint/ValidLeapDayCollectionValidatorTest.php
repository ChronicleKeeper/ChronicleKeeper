<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Constraint;

use ChronicleKeeper\Settings\Presentation\Constraint\ValidLeapDayCollection;
use ChronicleKeeper\Settings\Presentation\Constraint\ValidLeapDayCollectionValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

#[CoversClass(ValidLeapDayCollectionValidator::class)]
#[CoversClass(ValidLeapDayCollection::class)]
#[Small]
final class ValidLeapDayCollectionValidatorTest extends TestCase
{
    private ExecutionContextInterface&MockObject $context;
    private ValidLeapDayCollectionValidator $validator;
    private ValidLeapDayCollection $constraint;

    protected function setUp(): void
    {
        $this->context   = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new ValidLeapDayCollectionValidator();
        $this->validator->initialize($this->context);
        $this->constraint = new ValidLeapDayCollection();
    }

    protected function tearDown(): void
    {
        unset($this->context, $this->validator, $this->constraint);
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
            ValidLeapDayCollectionValidator::class,
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
    public function itShouldPassValidationForValidLeapDays(): void
    {
        $leapDays = [
            'leap_days' => [
                [
                    'day' => 5,
                    'name' => 'Special Day 1',
                    'year_interval' => 4,
                ],
                [
                    'day' => 15,
                    'name' => 'Special Day 2',
                ],
                [
                    'day' => 25,
                    'name' => 'Special Day 3',
                    'year_interval' => 2,
                ],
            ],
        ];

        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate($leapDays, $this->constraint);
    }

    #[Test]
    public function itShouldFailWhenLeapDaysHaveDuplicateDays(): void
    {
        $leapDays = [
            'leap_days' => [
                [
                    'day' => 5,
                    'name' => 'Special Day 1',
                ],
                [
                    'day' => 5, // Duplicate
                    'name' => 'Special Day 2',
                ],
                [
                    'day' => 15,
                    'name' => 'Special Day 3',
                ],
            ],
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->duplicateDayMessage)
            ->willReturn($violationBuilder);

        $violationBuilder->expects($this->once())
            ->method('setParameter')
            ->with('{{ day }}', '5')
            ->willReturnSelf();

        $violationBuilder->expects($this->once())
            ->method('atPath')
            ->with('leap_days[1].day')
            ->willReturnSelf();

        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validator->validate($leapDays, $this->constraint);
    }

    #[Test]
    public function itShouldSkipValidationWhenValueIsNull(): void
    {
        $this->context->expects($this->never())->method('buildViolation');
        $this->validator->validate(null, $this->constraint);
    }

    #[Test]
    public function itShouldSkipValidationWhenLeapDaysKeyIsMissing(): void
    {
        $this->context->expects($this->never())->method('buildViolation');
        $this->validator->validate([], $this->constraint);
    }
}
