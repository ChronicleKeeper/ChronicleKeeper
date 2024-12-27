<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Controller\ChangeSettings;

use ChronicleKeeper\Settings\Application\Command\DeleteSystemPrompt;
use ChronicleKeeper\Settings\Application\Service\SystemPromptRegistry;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(
    '/settings/chatbot_system_prompts/{id}/delete',
    name: 'settings_system_prompts_delete',
    requirements: ['id' => Requirement::UUID],
    priority: 10,
)]
class SystemPromptDelete extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly SystemPromptRegistry $systemPromptRegistry,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if ($request->get('confirm', 0) === 0) {
            $this->addFlashMessage(
                $request,
                Alert::WARNING,
                'Das Löschen des Prompts muss erst bestätigt werden. Bitte versichere, dass es sich nicht um ein Versehen handelt.',
            );

            return $this->redirectToRoute('settings_system_prompts');
        }

        $systemPrompt = $this->systemPromptRegistry->getById($id);
        $this->bus->dispatch(new DeleteSystemPrompt($systemPrompt));

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Der Prompt wurde erfolgreich gelöscht.',
        );

        return $this->redirectToRoute('settings_system_prompts');
    }
}
