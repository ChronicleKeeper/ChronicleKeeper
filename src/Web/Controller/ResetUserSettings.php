<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller;

use DZunke\NovDoc\Domain\Settings\SettingsHandler;
use DZunke\NovDoc\Web\FlashMessages\Alert;
use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

#[Route('/settings/reset', name: 'settings_reset')]
class ResetUserSettings
{
    use HandleFlashMessages;

    public function __construct(
        private SettingsHandler $settingsHandler,
        private RouterInterface $router,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->settingsHandler->reset();
        $this->addFlashMessage($request, Alert::SUCCESS, 'Einstellungen wurden auf den Standard zurückgesetzt.');

        return new RedirectResponse($this->router->generate('settings'));
    }
}
