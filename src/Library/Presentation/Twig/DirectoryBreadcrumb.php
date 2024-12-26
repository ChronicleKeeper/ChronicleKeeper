<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Twig;

use ChronicleKeeper\Library\Domain\Entity\Directory;
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
            $unsortedBreadcrumbs[$workOnBreadCrumb->getTitle()] = $this->router->generate(
                'library',
                ['directory' => $workOnBreadCrumb->getId()],
            );

            if (! $workOnBreadCrumb->getParent() instanceof Directory) {
                // Break the loop as soon as the root directory is reached
                break;
            }

            $workOnBreadCrumb = $workOnBreadCrumb->getParent();
        } while (true);

        $breadcrumbs = array_reverse($unsortedBreadcrumbs);

        if ($this->extraLastNode !== null) {
            $breadcrumbs[$this->extraLastNode] = null;
        }

        return $breadcrumbs;
    }
}
