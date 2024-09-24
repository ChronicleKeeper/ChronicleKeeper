<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DirectoryType extends AbstractType
{
    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'title',
            TextType::class,
            [
                'label' => 'Titel',
                'translation_domain' => false,
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ],
        );
    }
}
