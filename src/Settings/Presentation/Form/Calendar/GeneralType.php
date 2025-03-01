<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Form\Calendar;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotNull;

final class GeneralType extends AbstractType
{
    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'moonCycleDays',
            IntegerType::class,
            [
                'label' => 'Mondzyklus in Tagen',
                'constraints' => [new NotNull(), new GreaterThanOrEqual(1)],
            ],
        );
    }
}
