<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Chat\Presentation\Controller;

use DZunke\NovDoc\Chat\Application\Service\Chat as ChatTool;
use DZunke\NovDoc\Chat\Infrastructure\LLMChain\ExtendedMessage;
use DZunke\NovDoc\Chat\Infrastructure\LLMChain\ExtendedMessageBag;
use DZunke\NovDoc\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Message\Role;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

use function is_string;

#[Route('/', name: 'chat', methods: [Request::METHOD_GET, Request::METHOD_POST])]
class Chat extends AbstractController
{
    public function __construct(
        private readonly Environment $environment,
        private readonly ChatTool $chatTool,
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $message = $request->get('question', '');
            if (is_string($message) && $message !== '') {
                $this->chatTool->submitMessage($message);
            }
        }

        $messages = $this->chatTool->loadMessages();

        return new Response($this->environment->render(
            'chat.html.twig',
            ['messages' => $this->formatMessagesForOutput($messages)],
        ));
    }

    /** @return list<array{role: string, message: string, extended: ExtendedMessage}> */
    private function formatMessagesForOutput(ExtendedMessageBag $messageBag): array
    {
        $settings = $this->settingsHandler->get();

        $messages = [];

        foreach ($messageBag as $extendedMessage) {
            $originMessage = $extendedMessage->message;

            if ($originMessage->role !== Role::User && $originMessage->role !== Role::Assistant) {
                continue;
            }

            $role = $settings->getChatbotGeneral()->getChatbotName();
            if ($originMessage->role === Role::User) {
                $role = $settings->getChatbotGeneral()->getChatterName();
            }

            $messages[] = [
                'role' => $role,
                'message' => (string) $originMessage->content,
                'extended' => $extendedMessage,
            ];
        }

        return $messages;
    }
}
