<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Presentation\Controller;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\ImageGenerator\Presentation\Form\GeneratorRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

use function assert;

#[Route('/image_generator/create', name: 'image_generator_create')]
final class Create extends AbstractController
{
    public function __invoke(Request $request, MessageBusInterface $bus): Response
    {
        $form = $this->createForm(GeneratorRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            assert($data instanceof GeneratorRequest);

            $bus->dispatch(new StoreGeneratorRequest($data));

            return $this->redirectToRoute('image_generator_generator', ['generatorRequest' => $data->id]);
        }

        return $this->render('image_generator/create.html.twig', ['form' => $form->createView()]);
    }
}
