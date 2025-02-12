<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\Twig\Form;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function method_exists;

trait HandleFooterButtonGroup
{
    private function redirectFromFooter(
        Request $request,
        string $redirectToList,
        string $redirectToView,
        string|null $redirectToCreate = null,
    ): Response {
        if (! method_exists($this, 'redirectToRoute')) { // @phpstan-ignore function.alreadyNarrowedType
            throw new RuntimeException('The controller must extend ' . AbstractController::class);
        }

        if (! $request->request->has('saveAndRedirect')) {
            return new RedirectResponse($redirectToList);
        }

        $saveAndRedirect = $request->request->get('saveAndRedirect');

        return match ($saveAndRedirect) {
            '1' => new RedirectResponse($redirectToView),
            '2' => new RedirectResponse($redirectToCreate ?? throw new InvalidArgumentException('There is no create redirection route given')),
             default => new RedirectResponse($redirectToList),
        };
    }
}
