<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Controller;

use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use ChronicleKeeper\World\Application\Command\StoreItemRelation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(
    '/world/item/{itemId}/relation/add',
    name: 'world_item_relation_add',
    methods: ['POST'],
    requirements: ['itemId' => Requirement::UUID],
)]
final class WorldItemRelationAdd extends AbstractController
{
    use HandleFlashMessages;

    public function __invoke(Request $request, MessageBusInterface $bus, string $itemId): Response
    {
        $relation = $request->get('item_relation');
        if ($relation === null || $relation['world_item'] === $itemId) {
            return $this->redirectToRoute('world_item_view', ['id' => $itemId]);
        }

        $worldItem    = $relation['world_item'];
        $relationType = $relation['type'];

        if ((string) $relationType === '' || (string) $worldItem === '') {
            return $this->redirectToRoute('world_item_view', ['id' => $itemId]);
        }

        $bus->dispatch(new StoreItemRelation($itemId, $worldItem, $relationType));

        $this->addFlashMessage($request, Alert::SUCCESS, 'Die Beziehung wurde erfolgreich hinzugefügt.');

        return $this->redirectToRoute('world_item_view', ['id' => $itemId]);
    }
}
