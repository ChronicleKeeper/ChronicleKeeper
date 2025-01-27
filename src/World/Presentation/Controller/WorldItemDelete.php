<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Controller;

use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use ChronicleKeeper\World\Application\Command\DeleteWorldItem;
use ChronicleKeeper\World\Domain\Entity\Item;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(
    '/world/item/{id}/delete',
    name: 'world_item_delete',
    requirements: ['id' => Requirement::UUID],
)]
final class WorldItemDelete extends AbstractController
{
    use HandleFlashMessages;

    public function __invoke(Request $request, MessageBusInterface $bus, Item $item): Response
    {
        if ($request->get('confirm', 0) === 0) {
            $this->addFlashMessage(
                $request,
                Alert::WARNING,
                'Das Löschen des Eintrags muss erst bestätigt werden!',
            );

            return $this->redirectToRoute('world_item_view', ['id' => $item->getId()]);
        }

        $bus->dispatch(new DeleteWorldItem($item));

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Der Eintrag wurde erfolgreich gelöscht.',
        );

        return $this->redirectToRoute('world_item_listing');
    }
}
