<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Controller\ChangeSettings;

use ChronicleKeeper\Settings\Application\Command\StoreSystemPrompt;
use ChronicleKeeper\Settings\Application\Service\SystemPromptRegistry;
use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Settings\Presentation\Form\SystemPromptType;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

use function assert;

#[Route(
    '/settings/chatbot_system_prompts/{id}',
    name: 'settings_system_prompts_edit',
    requirements: ['id' => Requirement::UUID],
    priority: 10,
)]
class SystemPromptEdit extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly SystemPromptRegistry $systemPromptRegistry,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $systemPrompt = $this->systemPromptRegistry->getById($id);
        $form         = $this->createForm(SystemPromptType::class, $systemPrompt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $systemPrompt = $form->getData();
            assert($systemPrompt instanceof SystemPrompt); // Ensured by Form

            $this->bus->dispatch(new StoreSystemPrompt($systemPrompt));

            $this->addFlashMessage($request, Alert::SUCCESS, 'Der System Prompt wurde erfolgreich bearbeitet.');

            return $this->redirectToRoute('settings_system_prompts');
        }

        return $this->render(
            'settings/system_prompt_edit.html.twig',
            ['form' => $form->createView(), 'systemPrompt' => $systemPrompt],
        );
    }
}
