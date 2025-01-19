<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Controller;

use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use ChronicleKeeper\World\Application\Command\StoreWorldItem;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Presentation\Form\WorldItemType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

use function assert;

#[Route(
    '/world/item/{id}/edit',
    name: 'world_item_edit',
    requirements: ['id' => Requirement::UUID],
)]
final class WorldItemEdit extends AbstractController
{
    use HandleFlashMessages;

    public function __invoke(Request $request, MessageBusInterface $bus, Item $item): Response
    {
        $form = $this->createForm(WorldItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();
            assert($item instanceof Item);

            $bus->dispatch(new StoreWorldItem($item));

            $this->addFlashMessage(
                $request,
                Alert::SUCCESS,
                'Der Eintrag wurde erfolgreich bearbeitet.',
            );

            return $this->redirectToRoute('world_item_view', ['id' => $item->getId()]);
        }

        return $this->render(
            'world/item_edit.html.twig',
            ['form' => $form->createView(), 'item' => $item],
        );
    }
}
