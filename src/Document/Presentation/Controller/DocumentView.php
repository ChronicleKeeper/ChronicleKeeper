<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Presentation\Controller;

use ChronicleKeeper\Document\Domain\Entity\Document;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Twig\Environment;

#[Route(
    '/library/document/{document}',
    name: 'library_document_view',
    requirements: ['document' => Requirement::UUID],
)]
class DocumentView extends AbstractController
{
    public function __construct(
        private readonly Environment $environment,
    ) {
    }

    public function __invoke(Request $request, Document $document): Response
    {
        return new Response($this->environment->render(
            'library/document_view.html.twig',
            ['document' => $document],
        ));
    }
}
