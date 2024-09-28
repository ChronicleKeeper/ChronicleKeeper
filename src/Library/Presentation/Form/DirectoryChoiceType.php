<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Form;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDirectoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DirectoryChoiceType extends AbstractType
{
    public function __construct(
        private readonly FilesystemDirectoryRepository $directoryRepository,
    ) {
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'label' => 'Verschieben in Verzeichnis ...',
                'translation_domain' => false,
                'required' => false,
                'data' => RootDirectory::get(),
                'choices' => $this->directoryRepository->findAll(),
                'placeholder' => false,
                'choice_value' => static fn (Directory $directory): string => $directory->id,
                'choice_label' => static fn (Directory $directory): string => $directory->flattenHierarchyTitle(),
            ],
        );
    }
}
