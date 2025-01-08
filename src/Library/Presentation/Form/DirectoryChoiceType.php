<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Form;

use ChronicleKeeper\Library\Application\Query\FindAllDirectories;
use ChronicleKeeper\Library\Application\Query\FindDirectoriesByParent;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_map;
use function array_merge;
use function array_values;
use function in_array;

final class DirectoryChoiceType extends AbstractType
{
    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    #[Override]
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
                'choices' => $this->queryService->query(new FindAllDirectories()),
                'placeholder' => false,
                'choice_value' => static fn (Directory $directory): string => $directory->getId(),
                'choice_label' => static fn (Directory $directory): string => $directory->flattenHierarchyTitle(),
            ],
        );

        // Handle Exclusion
        $resolver->setDefault('exclude_directories', []);
        $resolver->setAllowedTypes('exclude_directories', Directory::class . '[]');
        $resolver->setNormalizer('exclude_directories', $this->extendExcludedDirectoriesByTreeSTructure(...));

        $resolver->setDefault('exclude_children', true);
        $resolver->setAllowedTypes('exclude_children', 'bool');

        // Filter Choices
        $resolver->setNormalizer('choices', $this->filterChoicesByExclusionSettings(...));
    }

    /**
     * @param list<Directory> $directories
     *
     * @return list<Directory>
     */
    private function extendExcludedDirectoriesByTreeSTructure(Options $options, array $directories): array
    {
        if ($options['exclude_children'] === false) {
            return $directories;
        }

        $reMappedDirectories = [];
        foreach ($directories as $directory) {
            $reMappedDirectories = array_merge(
                $reMappedDirectories,
                $this->fetchFlattenedTree($directory),
            );
        }

        return $reMappedDirectories;
    }

    /** @return list<Directory> */
    public function fetchFlattenedTree(Directory $root): array
    {
        $flattenedTree = [$root];

        $children = $this->queryService->query(new FindDirectoriesByParent($root->getId()));
        foreach ($children as $child) {
            $flattenedTree = array_merge(
                $flattenedTree,
                $this->fetchFlattenedTree($child),
            );
        }

        return $flattenedTree;
    }

    /**
     * @param list<Directory> $directories
     *
     * @return list<Directory>
     */
    private function filterChoicesByExclusionSettings(Options $options, array $directories): array
    {
        /** @var list<Directory> $excludeDirectories */
        $excludeDirectories = $options['exclude_directories'];
        if ($excludeDirectories === []) {
            return $directories;
        }

        $excludeDirectories = array_map(
            static fn (Directory $directory): string => $directory->getId(),
            $excludeDirectories,
        );

        /** @var list<Directory> $existingChoices */
        $existingChoices = $directories;

        foreach ($existingChoices as $index => $choice) {
            if (! in_array($choice->getId(), $excludeDirectories, true)) {
                continue;
            }

            unset($existingChoices[$index]);
        }

        return array_values($existingChoices);
    }
}
