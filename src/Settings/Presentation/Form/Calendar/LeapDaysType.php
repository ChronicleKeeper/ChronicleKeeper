<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Form\Calendar;

use ChronicleKeeper\Settings\Presentation\Constraint\ValidLeapDayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

use function is_array;
use function usort;

final class LeapDaysType extends AbstractType implements DataTransformerInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'constraints' => [new ValidLeapDayCollection()],
            'by_reference' => false,
        ]);
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'index',
            IntegerType::class,
            ['disabled' => true],
        );

        $builder->add(
            'name',
            TextType::class,
            ['constraints' => [new NotBlank()]],
        );

        $builder->add(
            'days',
            IntegerType::class,
            ['constraints' => [new NotNull(), new GreaterThanOrEqual(1)]],
        );

        $builder->add(
            'leap_days',
            LiveCollectionType::class,
            [
                'entry_type' => LeapDayType::class,
                'label'      => false,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
            ],
        );

        $builder->addModelTransformer($this);
    }

    /** @inheritDoc */
    public function transform($value): mixed
    {
        if (isset($value['leap_days']) && is_array($value['leap_days'])) {
            usort($value['leap_days'], static fn ($a, $b) => $a['day'] <=> $b['day']);
        }

        return $value;
    }

    /** @inheritDoc */
    public function reverseTransform($value): mixed
    {
        if (isset($value['leap_days']) && is_array($value['leap_days'])) {
            usort($value['leap_days'], static fn ($a, $b) => $a['day'] <=> $b['day']);
        }

        return $value;
    }
}
