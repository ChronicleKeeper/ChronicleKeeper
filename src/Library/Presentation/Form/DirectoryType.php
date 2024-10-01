<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Form;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDirectoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

use function array_map;
use function array_merge;
use function in_array;

class DirectoryType extends AbstractType
{
    public function __construct(private readonly FilesystemDirectoryRepository $directoryRepository)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('exclude_directories', []);
        $resolver->setAllowedTypes('exclude_directories', Directory::class . '[]');

        $directoryRepository = $this->directoryRepository;
        $resolver->setNormalizer(
            'exclude_directories',
            static function (Options $options, array $directories) use ($directoryRepository): array {
                if ($options['exclude_children'] === false) {
                    return $directories;
                }

                $reMappedDirectories = [];
                foreach ($directories as $directory) {
                    $reMappedDirectories = array_merge(
                        $reMappedDirectories,
                        $directoryRepository->fetchFlattenedTree($directory),
                    );
                }

                return $reMappedDirectories;
            },
        );

        $resolver->setDefault('exclude_children', true);
        $resolver->setAllowedTypes('exclude_children', 'bool');
    }

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
            ],
        );

        $builder->add(
            'parent',
            DirectoryChoiceType::class,
            [
                'label' => 'In Verzeichnis ...',
                'translation_domain' => false,
                'constraints' => [new NotNull()],
            ],
        );

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            static function (FormEvent $event): void {
                /** @var list<Directory> $excludeDirectories */
                $excludeDirectories = $event->getForm()->getConfig()->getOption('exclude_directories');
                if ($excludeDirectories === []) {
                    return;
                }

                $excludeDirectories = array_map(
                    static fn (Directory $directory): string => $directory->id,
                    $excludeDirectories,
                );

                $form        = $event->getForm()->get('parent');
                $formOptions = $form->getConfig()->getOptions();

                /** @var list<Directory> $existingChoices */
                $existingChoices = $formOptions['choices'];

                foreach ($existingChoices as $index => $choice) {
                    if (! in_array($choice->id, $excludeDirectories, true)) {
                        continue;
                    }

                    unset($existingChoices[$index]);
                }

                $formOptions['choices'] = $existingChoices;

                $event->getForm()->add(
                    $form->getName(),
                    $form->getConfig()->getType()->getInnerType()::class,
                    $formOptions,
                );
            },
        );
    }
}
