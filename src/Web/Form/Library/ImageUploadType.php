<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Form\Library;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class ImageUploadType extends AbstractType
{
    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'image',
            FileType::class,
            [
                'label' => 'Bild',
                'translation_domain' => false,
                'constraints' => [new NotNull()],
            ],
        );
    }
}
