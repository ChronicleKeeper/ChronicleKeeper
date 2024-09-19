<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Form\Application;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

class ImportType extends AbstractType
{
    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'archive',
            FileType::class,
            [
                'label' => 'Archiv',
                'translation_domain' => false,
                'constraints' => [new NotNull(), new File(['extensions' => ['zip']])],
            ],
        );

        $builder->add(
            'overwrite_settings',
            CheckboxType::class,
            [
                'label' => 'Überschreiben der Einstellungen',
                'translation_domain' => false,
                'required' => false,
            ],
        );

        $builder->add(
            'prune_library',
            CheckboxType::class,
            [
                'label' => 'Leeren der Bibliothek vor Import',
                'translation_domain' => false,
                'required' => false,
            ],
        );

        $builder->add(
            'overwrite_library',
            CheckboxType::class,
            [
                'label' => 'Überschreiben vorhandener Daten (Ohne Leeren)',
                'translation_domain' => false,
                'required' => false,
            ],
        );
    }
}
