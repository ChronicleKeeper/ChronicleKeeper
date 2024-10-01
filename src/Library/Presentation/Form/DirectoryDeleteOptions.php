<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Form;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DirectoryDeleteOptions extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('exclude_directories', []);
        $resolver->setAllowedTypes('exclude_directories', Directory::class . '[]');
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'confirmDeleteAll',
            CheckboxType::class,
            [
                'label' => 'Alle Inhalte Löschen!',
                'translation_domain' => false,
                'required' => false,
            ],
        );

        $builder->add(
            'moveContentTo',
            DirectoryChoiceType::class,
            [
                'label' => 'Inhalt verschieben nach ...',
                'data' => RootDirectory::get(),
                'exclude_directories' => $options['exclude_directories'],
                'translation_domain' => false,
            ],
        );
    }
}
