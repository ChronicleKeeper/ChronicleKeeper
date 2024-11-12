<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/first_start', name: 'first_start')]
class FirstStart extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('shared/first_start.html.twig');
    }
}
