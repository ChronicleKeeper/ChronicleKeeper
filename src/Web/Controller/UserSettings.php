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
use Twig\Environment;

use function is_string;

#[Route('/settings', name: 'settings')]
class UserSettings
{
    use HandleFlashMessages;

    public function __construct(
        private Environment $environment,
        private SettingsHandler $settingsHandler,
        private RouterInterface $router,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $settings = $this->settingsHandler->get();

        if ($request->isMethod(Request::METHOD_POST)) {
            $currentDate = $request->get('current_date', '');
            if (is_string($currentDate)) {
                $settings->currentDate = $currentDate;
            }

            $systemPrompt = $request->get('system_prompt', '');
            if (is_string($systemPrompt)) {
                $settings->systemPrompt = $systemPrompt;
            }

            $maxDocuments = $request->get('max_documents', 4);
            if (is_string($maxDocuments)) {
                $settings->maxDocumentResponses = (int) $maxDocuments;
            }

            $chatbotName = $request->get('chatbot_name', '');
            if (is_string($chatbotName) && $chatbotName !== '') {
                $settings->chatbotName = $chatbotName;
            }

            $chatterName = $request->get('chatter_name', '');
            if (is_string($chatterName) && $chatterName !== '') {
                $settings->chatterName = $chatterName;
            }

            $this->settingsHandler->store();

            $this->addFlashMessage($request, Alert::SUCCESS, 'Einstellungen wurden erfolgreich gespeichert.');

            return new RedirectResponse($this->router->generate('settings'));
        }

        return new Response($this->environment->render('settings.html.twig', ['settings' => $settings]));
    }
}
