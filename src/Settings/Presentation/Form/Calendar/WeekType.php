<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Form\Calendar;

use ChronicleKeeper\Settings\Presentation\Constraint\ValidWeekdayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

use function is_array;
use function usort;

final class WeekType extends AbstractType implements DataTransformerInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'constraints' => [new ValidWeekdayCollection()],
            'by_reference' => false,
        ]);
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'weekdays',
            LiveCollectionType::class,
            [
                'entry_type' => WeekdayType::class,
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
        if (isset($value['weekdays']) && is_array($value['weekdays'])) {
            usort($value['weekdays'], static fn ($a, $b) => $a['index'] <=> $b['index']);
        }

        return $value;
    }

    /** @inheritDoc */
    public function reverseTransform($value): mixed
    {
        if (isset($value['weekdays']) && is_array($value['weekdays'])) {
            usort($value['weekdays'], static fn ($a, $b) => $a['index'] <=> $b['index']);
        }

        return $value;
    }
}
