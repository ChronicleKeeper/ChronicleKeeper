<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Controller\ChangeSettings;

use ChronicleKeeper\Settings\Application\Command\StoreSystemPrompt;
use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Settings\Presentation\Form\SystemPromptType;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

use function assert;

#[Route(
    '/settings/chatbot_system_prompts/create',
    name: 'settings_system_prompts_create',
    priority: 10,
)]
class SystemPromptCreate extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(SystemPromptType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $systemPrompt = $form->getData();
            assert($systemPrompt instanceof SystemPrompt); // Ensured by Form

            $this->bus->dispatch(new StoreSystemPrompt($systemPrompt));

            $this->addFlashMessage($request, Alert::SUCCESS, 'Der System Prompt wurde erfolgreich erstellt.');

            return $this->redirectToRoute('settings_system_prompts');
        }

        return $this->render(
            'settings/system_prompt_create.html.twig',
            ['form' => $form->createView()],
        );
    }
}
