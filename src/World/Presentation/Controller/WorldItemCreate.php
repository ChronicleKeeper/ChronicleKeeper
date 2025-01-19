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

use function assert;

#[Route('/world/create_item', name: 'world_item_create')]
final class WorldItemCreate extends AbstractController
{
    use HandleFlashMessages;

    public function __invoke(Request $request, MessageBusInterface $bus): Response
    {
        $form = $this->createForm(WorldItemType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();
            assert($item instanceof Item);

            $bus->dispatch(new StoreWorldItem($item));

            $this->addFlashMessage(
                $request,
                Alert::SUCCESS,
                'Der Eintrag wurde erfolgreich erstellt.',
            );

            return $this->redirectToRoute('world_item_listing');
        }

        return $this->render(
            'world/item_create.html.twig',
            ['form' => $form->createView()],
        );
    }
}
