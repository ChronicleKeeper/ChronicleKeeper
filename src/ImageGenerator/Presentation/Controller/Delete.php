<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Presentation\Controller;

use ChronicleKeeper\ImageGenerator\Application\Command\DeleteGeneratorRequest;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(
    '/image_generator/{generatorRequestId}/delete',
    name: 'image_generator_delete',
    requirements: ['generatorRequestId' => Requirement::UUID],
)]
final class Delete extends AbstractController
{
    use HandleFlashMessages;

    public function __invoke(Request $request, MessageBusInterface $bus, string $generatorRequestId): Response
    {
        if ($request->get('confirm', 0) === 0) {
            $this->addFlashMessage(
                $request,
                Alert::WARNING,
                'Das Löschen muss erst bestätigt werden!',
            );

            return $this->redirectToRoute('image_generator_overview');
        }

        $bus->dispatch(new DeleteGeneratorRequest($generatorRequestId));

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Gerade erst war die Tinte getrocknet, da war einfach jemand die Kunstwerke in den Müll.',
        );

        return $this->redirectToRoute('image_generator_overview');
    }
}
