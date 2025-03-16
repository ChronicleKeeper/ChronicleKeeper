<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Form\Calendar;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

final class MonthType extends AbstractType
{
    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('index', IntegerType::class, ['constraints' => [new NotNull(), new GreaterThanOrEqual(1)]]);
        $builder->add('name', TextType::class, ['constraints' => [new NotBlank()]]);
        $builder->add('days', IntegerType::class, ['constraints' => [new NotNull(), new GreaterThanOrEqual(1)]]);
    }
}
