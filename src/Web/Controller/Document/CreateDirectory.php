<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Document;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/documents/directory/create', name: 'documents_directory_create')]
class CreateDirectory
{
    public function __construct()
    {
    }

    public function __invoke(Request $request): Response
    {
        return new Response('foo');
    }
}
