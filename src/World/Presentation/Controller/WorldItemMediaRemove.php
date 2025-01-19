<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Controller;

use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use ChronicleKeeper\World\Application\Command\RemoveWorldItemMedium;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

use function explode;

#[Route(
    '/world/item/{id}/remove_medium/{mediumIdentifier}',
    name: 'world_item_remove_media',
    requirements: ['id' => Requirement::UUID],
)]
final class WorldItemMediaRemove extends AbstractController
{
    use HandleFlashMessages;

    public function __invoke(Request $request, MessageBusInterface $bus, string $id, string $mediumIdentifier): Response
    {
        [$mediumType, $mediumId] = explode('_', $mediumIdentifier);

        $bus->dispatch(new RemoveWorldItemMedium($id, $mediumId, $mediumType));

        $this->addFlashMessage($request, Alert::SUCCESS, 'Die Verlinkung wurden erfolgreich aufgehoben.');

        return $this->redirectToRoute('world_item_view', ['id' => $id]);
    }
}
