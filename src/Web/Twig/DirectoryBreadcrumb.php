<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Twig;

use DZunke\NovDoc\Domain\Document\Directory;
use Symfony\Component\Routing\RouterInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

use function array_reverse;

#[AsTwigComponent('directoryBreadcrumb')]
class DirectoryBreadcrumb
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {
    }

    public Directory $directory;
    public string|null $extraLastNode = null;

    /** @return array<string, string|null> */
    public function getFlattenedBreadcrumb(): array
    {
        $workOnBreadCrumb = $this->directory;

        $unsortedBreadcrumbs = [];
        do {
            $unsortedBreadcrumbs[$workOnBreadCrumb->title] = $this->router->generate(
                'library',
                ['directory' => $workOnBreadCrumb->id],
            );

            if ($workOnBreadCrumb->parent === null) {
                // Break the loop as soon as the root directory is reached
                break;
            }

            $workOnBreadCrumb = $workOnBreadCrumb->parent;
        } while (true);

        $breadcrumbs = array_reverse($unsortedBreadcrumbs);

        if ($this->extraLastNode !== null) {
            $breadcrumbs[$this->extraLastNode] = null;
        }

        return $breadcrumbs;
    }
}
