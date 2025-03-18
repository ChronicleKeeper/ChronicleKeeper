<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Form\Calendar;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

final class GeneralType extends AbstractType
{
    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('current_day', CurrentDayType::class);

        $builder->add(
            'moonName',
            TextType::class,
            [
                'label' => 'Name des Mondes',
                'empty_data' => 'Moon',
                'constraints' => [new NotBlank()],
            ],
        );

        $builder->add(
            'moonCycleDays',
            IntegerType::class,
            [
                'label' => 'Mondzyklus in Tagen',
                'constraints' => [new NotNull(), new GreaterThanOrEqual(1)],
            ],
        );

        $builder->add(
            'moonCycleOffset',
            NumberType::class,
            [
                'label' => 'Offset des Mondzyklus in Tagen',
                'help' => 'Verschiebt den Mond um x.y Tage vorwärts im Zyklus für den gesamten Kalender.',
                'empty_data' => 0.0,
                'html5' => true,
                'constraints' => [new NotNull(), new GreaterThanOrEqual(0)],
            ],
        );
    }
}
