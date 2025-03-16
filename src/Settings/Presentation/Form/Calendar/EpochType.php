<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Form\Calendar;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class EpochType extends AbstractType
{
    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('start_year', IntegerType::class)
            ->add('end_year', IntegerType::class);
    }
}
