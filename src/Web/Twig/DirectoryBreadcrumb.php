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
    public string|null $extraLastNode = null;

    /** @return array<string, string|null> */
    public function getFlattenedBreadcrumb(): array
    {
        $breadcrumbs = ['Hauptverzeichnis' => $this->router->generate('documents_overview')];

        if ($this->directory === null) {
            if ($this->extraLastNode !== null) {
                $breadcrumbs[$this->extraLastNode] = null;
            }

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

        $breadcrumbs = array_merge($breadcrumbs, array_reverse($unsortedBreadcrumbs));

        if ($this->extraLastNode !== null) {
            $breadcrumbs[$this->extraLastNode] = null;
        }

        return $breadcrumbs;
    }
}
