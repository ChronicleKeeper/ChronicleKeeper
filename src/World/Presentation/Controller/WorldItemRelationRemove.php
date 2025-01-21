<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Controller;

use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use ChronicleKeeper\World\Application\Command\RemoveItemRelation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(
    '/world/item/{sourceItemId}/relation/{targetItemId}/{relationType}/remove',
    name: 'world_item_relation_remove',
    requirements: [
        'sourceItemId' => Requirement::UUID,
        'targetItemId' => Requirement::UUID,
    ],
)]
final class WorldItemRelationRemove extends AbstractController
{
    use HandleFlashMessages;

    public function __invoke(
        Request $request,
        MessageBusInterface $bus,
        string $sourceItemId,
        string $targetItemId,
        string $relationType,
    ): Response {
        $bus->dispatch(new RemoveItemRelation($sourceItemId, $targetItemId, $relationType));

        $this->addFlashMessage($request, Alert::SUCCESS, 'Die Beziehung wurde erfolgreich entfernt.');

        return $this->redirectToRoute('world_item_view', ['id' => $sourceItemId]);
    }
}
