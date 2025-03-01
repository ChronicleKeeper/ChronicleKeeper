<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Form\Calendar;

use ChronicleKeeper\Settings\Presentation\Constraint\ValidEpochsCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

use function is_array;
use function usort;

final class EpochsType extends AbstractType implements DataTransformerInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'constraints' => [new ValidEpochsCollection()],
            'by_reference' => false,
        ]);
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'epochs',
            LiveCollectionType::class,
            [
                'entry_type' => EpochType::class,
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
        if (isset($value['epochs']) && is_array($value['epochs'])) {
            usort($value['epochs'], static fn ($a, $b) => $a['start_year'] <=> $b['start_year']);
        }

        return $value;
    }

    /** @inheritDoc */
    public function reverseTransform($value): mixed
    {
        if (isset($value['epochs']) && is_array($value['epochs'])) {
            usort($value['epochs'], static fn ($a, $b) => $a['start_year'] <=> $b['start_year']);
        }

        return $value;
    }
}
