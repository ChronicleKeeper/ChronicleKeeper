<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Constraint;

use ChronicleKeeper\Settings\Presentation\Constraint\ValidWeekdayCollection;
use ChronicleKeeper\Settings\Presentation\Constraint\ValidWeekdayCollectionValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

#[CoversClass(ValidWeekdayCollectionValidator::class)]
#[CoversClass(ValidWeekdayCollection::class)]
#[Small]
final class ValidWeekdayCollectionValidatorTest extends TestCase
{
    private ExecutionContextInterface&MockObject $context;
    private ValidWeekdayCollectionValidator $validator;
    private ValidWeekdayCollection $constraint;

    protected function setUp(): void
    {
        $this->context   = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new ValidWeekdayCollectionValidator();
        $this->validator->initialize($this->context);
        $this->constraint = new ValidWeekdayCollection();
    }

    protected function tearDown(): void
    {
        unset($this->context);
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
            ValidWeekdayCollectionValidator::class,
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
    public function itShouldPassValidationForValidWeekdays(): void
    {
        $weekdays = [
            'weekdays' => [
                [
                    'index' => 1,
                    'name' => 'Monday',
                ],
                [
                    'index' => 2,
                    'name' => 'Tuesday',
                ],
                [
                    'index' => 3,
                    'name' => 'Wednesday',
                ],
            ],
        ];

        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate($weekdays, $this->constraint);
    }

    #[Test]
    public function itShouldFailWhenWeekdaysIsEmpty(): void
    {
        $weekdays = ['weekdays' => []];

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->emptyCollectionMessage)
            ->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validator->validate($weekdays, $this->constraint);
    }

    #[Test]
    public function itShouldFailWhenIndicesAreNotSequential(): void
    {
        $weekdays = [
            'weekdays' => [
                [
                    'index' => 1,
                    'name' => 'Monday',
                ],
                [
                    'index' => 3, // Should be 2
                    'name' => 'Wednesday',
                ],
                [
                    'index' => 4,
                    'name' => 'Thursday',
                ],
            ],
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->nonSequentialIndicesMessage)
            ->willReturn($violationBuilder);

        // Use a callback to verify setParameter calls with different parameters
        $invocation = $this->exactly(2);
        $violationBuilder->expects($invocation)
            ->method('setParameter')
            ->willReturnCallback(static function (string $name, string $value) use ($violationBuilder, $invocation) {
                if ($invocation->numberOfInvocations() === 1) {
                    self::assertSame('{{ expected }}', $name);
                    self::assertSame('2', $value);
                } elseif ($invocation->numberOfInvocations() === 2) {
                    self::assertSame('{{ actual }}', $name);
                    self::assertSame('3', $value);
                }

                return $violationBuilder;
            });

        $violationBuilder->expects($this->once())->method('atPath')->with('weekdays[1].index')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validator->validate($weekdays, $this->constraint);
    }

    #[Test]
    public function itShouldFailWhenIndicesAreDuplicate(): void
    {
        $weekdays = [
            'weekdays' => [
                [
                    'index' => 1,
                    'name' => 'Monday',
                ],
                [
                    'index' => 1, // Duplicate
                    'name' => 'Another Monday',
                ],
                [
                    'index' => 3,
                    'name' => 'Wednesday',
                ],
            ],
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->duplicateIndicesMessage)
            ->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validator->validate($weekdays, $this->constraint);
    }

    #[Test]
    public function itShouldSkipValidationWhenValueIsNull(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate(null, $this->constraint);
    }

    #[Test]
    public function itShouldSkipValidationWhenWeekdaysKeyIsMissing(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate([], $this->constraint);
    }
}
