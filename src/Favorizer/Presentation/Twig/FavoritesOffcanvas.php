<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Presentation\Twig;

use ChronicleKeeper\Favorizer\Application\Query\GetTargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\ChatConversationTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use ChronicleKeeper\Favorizer\Domain\ValueObject\WorldItemTarget;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Generator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

use function ksort;
use function strtoupper;

#[AsLiveComponent('Favorizer:Offcanvas', 'components/favorizer/favorites_offcanvas.html.twig')]
class FavoritesOffcanvas
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    public function __construct(
        private readonly RouterInterface $router,
        private readonly QueryService $queryService,
    ) {
    }

    #[LiveListener('favorites_updated')] // The target bag was updated, so we need to refresh the shortcuts
    public function refresh(): void
    {
    }

    public function getShortcuts(): Generator
    {
        $targets = $this->queryService->query(new GetTargetBag());

        foreach ($targets as $target) {
            yield [
                'icon' => $this->getIconForTarget($target),
                'title' => $target->getTitle(),
                'url' => $this->generateShortcutUrl($target),
            ];
        }
    }

    /** @return array<string, list<array{icon: string, title: string, url: string}>> */
    public function getAlphabeticallyGrouped(): array
    {
        $targets = $this->queryService->query(new GetTargetBag());
        $grouped = [];

        foreach ($targets as $target) {
            $firstLetter = strtoupper((string) $target->getTitle()[0]);

            if (! isset($grouped[$firstLetter])) {
                $grouped[$firstLetter] = [];
            }

            $grouped[$firstLetter][] = [
                'icon' => $this->getIconForTarget($target),
                'title' => $target->getTitle(),
                'url' => $this->generateShortcutUrl($target),
            ];
        }

        ksort($grouped);

        return $grouped;
    }

    private function getIconForTarget(Target $target): string
    {
        if ($target instanceof LibraryDocumentTarget) {
            return 'tabler:file-search';
        }

        if ($target instanceof LibraryImageTarget) {
            return 'tabler:photo-search';
        }

        if ($target instanceof ChatConversationTarget) {
            return 'tabler:message-2-share';
        }

        if ($target instanceof WorldItemTarget) {
            return 'tabler:database-search';
        }

        return 'tabler:file';
    }

    private function generateShortcutUrl(Target $target): string
    {
        if ($target instanceof LibraryDocumentTarget) {
            return $this->router->generate('library_document_view', ['document' => $target->getId()]);
        }

        if ($target instanceof LibraryImageTarget) {
            return $this->router->generate('library_image_view', ['image' => $target->getId()]);
        }

        if ($target instanceof ChatConversationTarget) {
            return $this->router->generate('chat', ['conversationId' => $target->getId()]);
        }

        if ($target instanceof WorldItemTarget) {
            return $this->router->generate('world_item_view', ['id' => $target->getId()]);
        }

        return '#';
    }
}
