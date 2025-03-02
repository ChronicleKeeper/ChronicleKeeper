<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Form\Calendar;

use ChronicleKeeper\Settings\Presentation\Constraint\ValidCalendarDate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotNull;

final class CurrentDayType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('constraints', [new ValidCalendarDate()]);
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('year', IntegerType::class, [
            'label' => 'Jahr',
            'empty_data' => 0,
            'constraints' => [new NotNull(), new GreaterThanOrEqual(0)],
        ]);

        $builder->add('month', IntegerType::class, [
            'label' => 'Monat',
            'empty_data' => 1,
            'constraints' => [new NotNull(), new GreaterThanOrEqual(1)],
        ]);

        $builder->add('day', IntegerType::class, [
            'label' => 'Tag',
            'empty_data' => 1,
            'constraints' => [new NotNull(), new GreaterThanOrEqual(1)],
        ]);
    }
}
