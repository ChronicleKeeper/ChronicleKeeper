<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Twig;

use DZunke\NovDoc\Domain\Document\Directory;
use Symfony\Component\Routing\RouterInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

use function array_merge;
use function array_reverse;

#[AsTwigComponent('directoryBreadcrumb')]
class DirectoryBreadcrumb
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {
    }

    public Directory|null $directory;

    /** @return array<string, string> */
    public function getFlattenedBreadcrumb(): array
    {
        $breadcrumbs = ['Hauptverzeichnis' => $this->router->generate('documents_overview')];

        if ($this->directory === null) {
            return $breadcrumbs;
        }

        $workOnBreadCrumb    = $this->directory;
        $unsortedBreadcrumbs = [];
        do {
            $unsortedBreadcrumbs[$workOnBreadCrumb->title] = $this->router->generate(
                'documents_overview_directory',
                ['directory' => $workOnBreadCrumb->id],
            );

            $workOnBreadCrumb = $workOnBreadCrumb->parent;
        } while ($workOnBreadCrumb !== null);

        return array_merge($breadcrumbs, array_reverse($unsortedBreadcrumbs));
    }
}
