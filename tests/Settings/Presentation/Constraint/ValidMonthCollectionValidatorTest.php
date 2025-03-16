<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Constraint;

use ChronicleKeeper\Settings\Presentation\Constraint\ValidMonthCollection;
use ChronicleKeeper\Settings\Presentation\Constraint\ValidMonthCollectionValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

#[CoversClass(ValidMonthCollectionValidator::class)]
#[CoversClass(ValidMonthCollection::class)]
#[Small]
final class ValidMonthCollectionValidatorTest extends TestCase
{
    private ExecutionContextInterface&MockObject $context;
    private ValidMonthCollectionValidator $validator;
    private ValidMonthCollection $constraint;

    protected function setUp(): void
    {
        $this->context   = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new ValidMonthCollectionValidator();
        $this->validator->initialize($this->context);
        $this->constraint = new ValidMonthCollection();
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
            ValidMonthCollectionValidator::class,
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
    public function itShouldPassValidationForValidMonths(): void
    {
        $months = [
            'months' => [
                [
                    'index' => 1,
                    'name' => 'January',
                    'days' => 31,
                ],
                [
                    'index' => 2,
                    'name' => 'February',
                    'days' => 28,
                ],
                [
                    'index' => 3,
                    'name' => 'March',
                    'days' => 31,
                ],
                [
                    'index' => 4,
                    'name' => 'April',
                    'days' => 30,
                ],
                [
                    'index' => 5,
                    'name' => 'May',
                    'days' => 31,
                ],
                [
                    'index' => 6,
                    'name' => 'June',
                    'days' => 30,
                ],
                [
                    'index' => 7,
                    'name' => 'July',
                    'days' => 31,
                ],
                [
                    'index' => 8,
                    'name' => 'August',
                    'days' => 31,
                ],
                [
                    'index' => 9,
                    'name' => 'September',
                    'days' => 30,
                ],
                [
                    'index' => 10,
                    'name' => 'October',
                    'days' => 31,
                ],
                [
                    'index' => 11,
                    'name' => 'November',
                    'days' => 30,
                ],
                [
                    'index' => 12,
                    'name' => 'December',
                    'days' => 31,
                ],
            ],
        ];

        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate($months, $this->constraint);
    }

    #[Test]
    public function itShouldFailWhenMonthsIsEmpty(): void
    {
        $months = ['months' => []];

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->emptyCollectionMessage)
            ->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validator->validate($months, $this->constraint);
    }

    #[Test]
    public function itShouldFailWhenIndicesAreNotSequential(): void
    {
        $months = [
            'months' => [
                [
                    'index' => 1,
                    'name' => 'January',
                    'days' => 31,
                ],
                [
                    'index' => 3, // Should be 2
                    'name' => 'March',
                    'days' => 31,
                ],
                [
                    'index' => 4,
                    'name' => 'April',
                    'days' => 30,
                ],
            ],
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->nonSequentialIndicesMessage)
            ->willReturn($violationBuilder);

        $violationBuilder->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnCallback(static function (string $name, string $value) use ($violationBuilder, &$invocation) {
                if (! isset($invocation)) {
                    $invocation = 0;
                }

                $invocation++;

                if ($invocation === 1) {
                    self::assertSame('{{ expected }}', $name);
                    self::assertSame('2', $value);
                } elseif ($invocation === 2) {
                    self::assertSame('{{ actual }}', $name);
                    self::assertSame('3', $value);
                }

                return $violationBuilder;
            });

        $violationBuilder->expects($this->once())->method('atPath')->with('months[1].index')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validator->validate($months, $this->constraint);
    }

    #[Test]
    public function itShouldFailWhenIndicesAreDuplicate(): void
    {
        $months = [
            'months' => [
                [
                    'index' => 1,
                    'name' => 'January',
                    'days' => 31,
                ],
                [
                    'index' => 1, // Duplicate
                    'name' => 'Also January',
                    'days' => 30,
                ],
                [
                    'index' => 3,
                    'name' => 'March',
                    'days' => 31,
                ],
            ],
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->duplicateIndicesMessage)
            ->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validator->validate($months, $this->constraint);
    }

    #[Test]
    public function itShouldSkipValidationWhenValueIsNull(): void
    {
        $this->context->expects($this->never())->method('buildViolation');
        $this->validator->validate(null, $this->constraint);
    }

    #[Test]
    public function itShouldSkipValidationWhenMonthsKeyIsMissing(): void
    {
        $this->context->expects($this->never())->method('buildViolation');
        $this->validator->validate([], $this->constraint);
    }
}
