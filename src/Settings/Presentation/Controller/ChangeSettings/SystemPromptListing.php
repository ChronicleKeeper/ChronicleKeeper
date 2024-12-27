<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Controller\ChangeSettings;

use ChronicleKeeper\Settings\Application\Service\SystemPromptRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/settings/chatbot_system_prompts', name: 'settings_system_prompts', priority: 10)]
class SystemPromptListing extends AbstractController
{
    public function __construct(
        private readonly SystemPromptRegistry $systemPromptRegistry,
    ) {
    }

    public function __invoke(): Response
    {
        return $this->render(
            'settings/system_prompt_listing.html.twig',
            ['systemPrompts' => $this->systemPromptRegistry->all()],
        );
    }
}
