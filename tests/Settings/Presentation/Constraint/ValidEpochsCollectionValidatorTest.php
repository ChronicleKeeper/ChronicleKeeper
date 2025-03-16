<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Constraint;

use ChronicleKeeper\Settings\Presentation\Constraint\ValidEpochsCollection;
use ChronicleKeeper\Settings\Presentation\Constraint\ValidEpochsCollectionValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

#[CoversClass(ValidEpochsCollectionValidator::class)]
#[CoversClass(ValidEpochsCollection::class)]
#[Small]
final class ValidEpochsCollectionValidatorTest extends TestCase
{
    private ExecutionContextInterface&MockObject $context;
    private ValidEpochsCollectionValidator $validator;
    private ValidEpochsCollection $constraint;

    protected function setUp(): void
    {
        $this->context   = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new ValidEpochsCollectionValidator();
        $this->validator->initialize($this->context);
        $this->constraint = new ValidEpochsCollection();
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
            ValidEpochsCollectionValidator::class,
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
    public function itShouldPassValidationForValidEpochs(): void
    {
        $epochs = [
            'epochs' => [
                [
                    'name' => 'First Epoch',
                    'start_year' => 0,
                    'end_year' => 100,
                ],
                [
                    'name' => 'Second Epoch',
                    'start_year' => 101,
                    'end_year' => 200,
                ],
                [
                    'name' => 'Last Epoch',
                    'start_year' => 201,
                    'end_year' => null,
                ],
            ],
        ];

        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate($epochs, $this->constraint);
    }

    #[Test]
    public function itShouldFailWhenEpochsIsEmpty(): void
    {
        $epochs = ['epochs' => []];

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->emptyCollectionMessage)
            ->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validator->validate($epochs, $this->constraint);
    }

    #[Test]
    public function itShouldFailWhenFirstEpochDoesNotStartWithZero(): void
    {
        $epochs = [
            'epochs' => [
                [
                    'name' => 'First Epoch',
                    'start_year' => 10, // Should be 0
                    'end_year' => 100,
                ],
                [
                    'name' => 'Last Epoch',
                    'start_year' => 101,
                    'end_year' => null,
                ],
            ],
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->startsWithZeroMessage)
            ->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('atPath')->with('epochs[0].start_year')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validator->validate($epochs, $this->constraint);
    }

    #[Test]
    public function itShouldFailWhenEpochsOverlap(): void
    {
        $epochs = [
            'epochs' => [
                [
                    'name' => 'First Epoch',
                    'start_year' => 0,
                    'end_year' => 100,
                ],
                [
                    'name' => 'Overlapping Epoch',
                    'start_year' => 100, // Should be 101
                    'end_year' => 200,
                ],
                [
                    'name' => 'Last Epoch',
                    'start_year' => 201,
                    'end_year' => null,
                ],
            ],
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->overlappingMessage)
            ->willReturn($violationBuilder);

        $violationBuilder->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnCallback(static function ($name, $value) use ($violationBuilder) {
                static $callCount = 0;
                $callCount++;

                if ($callCount === 1) {
                    self::assertSame('{{ end }}', $name);
                    self::assertSame('100', $value);
                } elseif ($callCount === 2) {
                    self::assertSame('{{ start }}', $name);
                    self::assertSame('100', $value);
                }

                return $violationBuilder;
            });

        $violationBuilder->expects($this->once())->method('atPath')->with('epochs[1].start_year')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validator->validate($epochs, $this->constraint);
    }

    #[Test]
    public function itShouldFailWhenNonLastEpochHasNullEndYear(): void
    {
        $epochs = [
            'epochs' => [
                [
                    'name' => 'First Epoch',
                    'start_year' => 0,
                    'end_year' => null, // Should not be null
                ],
                [
                    'name' => 'Last Epoch',
                    'start_year' => 101,
                    'end_year' => null,
                ],
            ],
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->lastEpochEndMessage)
            ->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('atPath')->with('epochs[0].end_year')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validator->validate($epochs, $this->constraint);
    }

    #[Test]
    public function itShouldFailWhenEpochsHaveGaps(): void
    {
        $epochs = [
            'epochs' => [
                [
                    'name' => 'First Epoch',
                    'start_year' => 0,
                    'end_year' => 100,
                ],
                [
                    'name' => 'Gapped Epoch',
                    'start_year' => 102, // Should be 101
                    'end_year' => 200,
                ],
                [
                    'name' => 'Last Epoch',
                    'start_year' => 201,
                    'end_year' => null,
                ],
            ],
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->gapMessage)
            ->willReturn($violationBuilder);

        $violationBuilder->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnCallback(static function ($name, $value) use ($violationBuilder) {
                static $callCount = 0;
                $callCount++;

                if ($callCount === 1) {
                    self::assertSame('{{ end }}', $name);
                    self::assertSame('100', $value);
                } elseif ($callCount === 2) {
                    self::assertSame('{{ start }}', $name);
                    self::assertSame('102', $value);
                }

                return $violationBuilder;
            });

        $violationBuilder->expects($this->once())->method('atPath')->with('epochs[1].start_year')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validator->validate($epochs, $this->constraint);
    }

    #[Test]
    public function itShouldFailWhenLastEpochHasEndYear(): void
    {
        $epochs = [
            'epochs' => [
                [
                    'name' => 'First Epoch',
                    'start_year' => 0,
                    'end_year' => 100,
                ],
                [
                    'name' => 'Last Epoch',
                    'start_year' => 101,
                    'end_year' => 200, // Should be null
                ],
            ],
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->lastEpochNullEndMessage)
            ->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('atPath')->with('epochs[1].end_year')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validator->validate($epochs, $this->constraint);
    }

    #[Test]
    public function itShouldSkipValidationWhenValueIsNull(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate(null, $this->constraint);
    }

    #[Test]
    public function itShouldSkipValidationWhenEpochsKeyIsMissing(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate([], $this->constraint);
    }
}
