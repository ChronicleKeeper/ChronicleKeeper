<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller;

use DZunke\NovDoc\Domain\Chat as ChatTool;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Message\Role;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

use function is_string;

#[Route('/', name: 'chat')]
class Chat
{
    public function __construct(
        private readonly Environment $environment,
        private readonly ChatTool $chatTool,
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

    /** @return list<array{role: 'Elias'|'Rostbart', message: string}> */
    private function formatMessagesForOutput(MessageBag $messageBag): array
    {
        $messages = [];

        foreach ($messageBag as $originMessage) {
            if ($originMessage->role !== Role::User && $originMessage->role !== Role::Assistant) {
                continue;
            }

            $messages[] = [
                'role' => $originMessage->role === Role::User ? 'Elias' : 'Rostbart',
                'message' => (string) $originMessage->content,
            ];
        }

        return $messages;
    }
}
