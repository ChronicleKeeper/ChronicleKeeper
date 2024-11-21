<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Controller\ChangeSettings;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Presentation\Form\ChatbotFunctionsType;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolboxFactory;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/settings/chatbot_functions', name: 'settings_chatbot_functions', priority: 10)]
class ChangeChatbotFunctions extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly SettingsHandler $settingsHandler,
        private readonly ToolboxFactory $toolboxFactory,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $settings = $this->settingsHandler->get();

        $form = $this->createForm(ChatbotFunctionsType::class, $settings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->settingsHandler->store();

            $this->addFlashMessage($request, Alert::SUCCESS, 'Die Einstellungen wurden gespeichert.');

            return $this->redirectToRoute('settings_chatbot_functions');
        }

        return $this->render(
            'settings/chatbot_functions.html.twig',
            [
                'form' => $form->createView(),
                'tools' => $this->toolboxFactory->create(),
            ],
        );
    }
}
