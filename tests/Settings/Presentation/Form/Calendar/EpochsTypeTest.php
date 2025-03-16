<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Form\Calendar;

use ChronicleKeeper\Settings\Presentation\Constraint\ValidEpochsCollection;
use ChronicleKeeper\Settings\Presentation\Constraint\ValidEpochsCollectionValidator;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\EpochsType;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\EpochType;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

#[CoversClass(EpochsType::class)]
#[CoversClass(EpochType::class)]
#[CoversClass(ValidEpochsCollection::class)]
#[CoversClass(ValidEpochsCollectionValidator::class)]
#[Small]
final class EpochsTypeTest extends TypeTestCase
{
    /** @inheritDoc */
    #[Override]
    protected function getExtensions(): array
    {
        $epochType = new EpochType();

        return [
            new PreloadedExtension(
                [EpochType::class => $epochType],
                [],
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    #[Test]
    public function itHandlesSubmittedDataAndSortsItByStartYear(): void
    {
        $formData = [
            'epochs' => [
                [
                    'name' => 'Second Epoch',
                    'start_year' => 101,
                    'end_year' => 200,
                ],
                [
                    'name' => 'First Epoch',
                    'start_year' => 0,
                    'end_year' => 100,
                ],
            ],
        ];

        $form = $this->factory->create(EpochsType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());

        $viewData = $form->getData();
        self::assertArrayHasKey('epochs', $viewData);
        self::assertCount(2, $viewData['epochs']);
        self::assertSame(0, $viewData['epochs'][0]['start_year']);
        self::assertSame(101, $viewData['epochs'][1]['start_year']);
    }

    #[Test]
    public function itConfiguresFormStructureCorrectly(): void
    {
        $form = $this->factory->create(EpochsType::class);

        self::assertTrue($form->has('epochs'));

        $config = $form->get('epochs')->getConfig();
        self::assertSame(EpochType::class, $config->getOption('entry_type'));
        self::assertTrue($config->getOption('allow_add'));
        self::assertTrue($config->getOption('allow_delete'));
        self::assertFalse($config->getOption('label'));
    }

    #[Test]
    public function itAppliesConstraintsCorrectly(): void
    {
        $form        = $this->factory->create(EpochsType::class);
        $constraints = $form->getConfig()->getOption('constraints');

        self::assertNotEmpty($constraints);

        $hasValidEpochsConstraint = false;
        foreach ($constraints as $constraint) {
            if ($constraint instanceof ValidEpochsCollection) {
                $hasValidEpochsConstraint = true;
                break;
            }
        }

        self::assertTrue($hasValidEpochsConstraint);
    }

    #[Test]
    public function itSortsEpochsOnReverseTransform(): void
    {
        $formData = [
            'epochs' => [
                [
                    'name' => 'Third Epoch',
                    'start_year' => 201,
                    'end_year' => null,
                ],
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
            ],
        ];

        $form = $this->factory->create(EpochsType::class);
        $form->submit($formData);

        $result = $form->getData();

        self::assertSame(0, $result['epochs'][0]['start_year']);
        self::assertSame(101, $result['epochs'][1]['start_year']);
        self::assertSame(201, $result['epochs'][2]['start_year']);
    }

    #[Test]
    public function itSetsByReferenceToFalse(): void
    {
        $form = $this->factory->create(EpochsType::class);
        self::assertFalse($form->getConfig()->getOption('by_reference'));
    }

    #[Test]
    public function itShouldFailValidationWhenEpochsHaveGaps(): void
    {
        $formData = [
            'epochs' => [
                [
                    'name' => 'First Epoch',
                    'start_year' => 0,
                    'end_year' => 10,
                ],
                [
                    'name' => 'Second Epoch',
                    'start_year' => 12, // Gap here (should be 11)
                    'end_year' => null,
                ],
            ],
        ];

        $form = $this->factory->create(EpochsType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
        self::assertGreaterThan(0, $form->getErrors(true)->count());
    }

    #[Test]
    public function itShouldFailValidationWhenEpochsOverlap(): void
    {
        $formData = [
            'epochs' => [
                [
                    'name' => 'First Epoch',
                    'start_year' => 0,
                    'end_year' => 100,
                ],
                [
                    'name' => 'Second Epoch',
                    'start_year' => 100, // Overlap with previous end_year
                    'end_year' => null,
                ],
            ],
        ];

        $form = $this->factory->create(EpochsType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
        self::assertGreaterThan(0, $form->getErrors(true)->count());
    }

    #[Test]
    public function itShouldFailValidationWhenNonLastEpochHasNullEndYear(): void
    {
        $formData = [
            'epochs' => [
                [
                    'name' => 'First Epoch',
                    'start_year' => 0,
                    'end_year' => null, // Should not be null for non-last epoch
                ],
                [
                    'name' => 'Second Epoch',
                    'start_year' => 101,
                    'end_year' => 200,
                ],
                [
                    'name' => 'Third Epoch',
                    'start_year' => 201,
                    'end_year' => null,
                ],
            ],
        ];

        $form = $this->factory->create(EpochsType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
        self::assertGreaterThan(0, $form->getErrors(true)->count());
    }

    #[Test]
    public function itShouldFailValidationWhenLastEpochHasEndYear(): void
    {
        $formData = [
            'epochs' => [
                [
                    'name' => 'First Epoch',
                    'start_year' => 0,
                    'end_year' => 100,
                ],
                [
                    'name' => 'Last Epoch',
                    'start_year' => 101,
                    'end_year' => 200, // Last epoch should have null end_year
                ],
            ],
        ];

        $form = $this->factory->create(EpochsType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
        self::assertGreaterThan(0, $form->getErrors(true)->count());
    }

    #[Test]
    public function itShouldPassValidationForCorrectlyDefinedEpochs(): void
    {
        $formData = [
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

        $form = $this->factory->create(EpochsType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
    }

    #[Test]
    public function itRequiresEpochName(): void
    {
        $formData = [
            'epochs' => [
                [
                    'name' => '', // Empty name should fail validation
                    'start_year' => 0,
                    'end_year' => 100,
                ],
                [
                    'name' => 'Last Epoch',
                    'start_year' => 101,
                    'end_year' => null,
                ],
            ],
        ];

        $form = $this->factory->create(EpochsType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
        self::assertGreaterThan(0, $form->getErrors(true)->count());
    }
}
