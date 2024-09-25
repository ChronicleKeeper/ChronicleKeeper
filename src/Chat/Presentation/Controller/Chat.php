<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Application\Service\Chat as ChatTool;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessage;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessageBag;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\Content\ContentInterface;
use PhpLlm\LlmChain\Message\Content\Text;
use PhpLlm\LlmChain\Message\Role;
use PhpLlm\LlmChain\Message\UserMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

use function array_column;
use function array_filter;
use function implode;
use function is_string;

use const PHP_EOL;

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

            if ($originMessage->getRole() !== Role::User && $originMessage->getRole() !== Role::Assistant) {
                continue;
            }

            $role = $settings->getChatbotGeneral()->getChatbotName();
            if ($originMessage->getRole() === Role::User) {
                $role = $settings->getChatbotGeneral()->getChatterName();
            }

            $content = '';
            if ($originMessage instanceof UserMessage) {
                $contentText = array_filter(
                    $originMessage->content,
                    static fn (ContentInterface $entry) => $entry instanceof Text,
                );
                $content     = implode(PHP_EOL, array_column($contentText, 'text'));
            } elseif ($originMessage instanceof AssistantMessage) {
                $content = (string) $originMessage->content;
            }

            $messages[] = [
                'role' => $role,
                'message' => $content,
                'extended' => $extendedMessage,
            ];
        }

        return $messages;
    }
}
