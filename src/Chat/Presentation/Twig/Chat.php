<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Twig;

use ChronicleKeeper\Chat\Application\Service\Chat as ChatService;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessage;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\Content\ContentInterface;
use PhpLlm\LlmChain\Message\Content\Text;
use PhpLlm\LlmChain\Message\Role;
use PhpLlm\LlmChain\Message\UserMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

use function array_column;
use function array_filter;
use function implode;

use const PHP_EOL;

#[AsLiveComponent('Chat:Chat', template: 'components/chat/chat.html.twig')]
class Chat extends AbstractController
{
    use DefaultActionTrait;

    public function __construct(
        private readonly ChatService $chat,
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    #[LiveProp]
    public string $message = '';

    /** @return list<array{role: string, message: string, extended: ExtendedMessage}> */
    public function getMessages(): array
    {
        $settings = $this->settingsHandler->get();

        $messages = [];

        foreach ($this->chat->loadMessages() as $extendedMessage) {
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

    #[LiveAction]
    public function submit(
        #[LiveArg]
        string $message,
    ): void {
        $this->chat->submitMessage($message);
    }
}
