<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Controller\ChangeSettings;

use ChronicleKeeper\Settings\Application\Service\SystemPromptRegistry;
use ChronicleKeeper\Settings\Presentation\Form\SystemPromptType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(
    '/settings/chatbot_system_prompts/{id}',
    name: 'settings_system_prompts_edit',
    requirements: ['id' => Requirement::UUID],
    priority: 10,
)]
class SystemPromptEdit extends AbstractController
{
    public function __construct(
        private readonly SystemPromptRegistry $systemPromptRegistry,
    ) {
    }

    public function __invoke(string $id): Response
    {
        $systemPrompt = $this->systemPromptRegistry->getById($id);
        $form         = $this->createForm(SystemPromptType::class, $systemPrompt);

        return $this->render(
            'settings/system_prompt_edit.html.twig',
            ['form' => $form->createView(), 'systemPrompt' => $systemPrompt],
        );
    }
}
