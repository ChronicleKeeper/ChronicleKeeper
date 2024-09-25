<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Repository\Conversation;

use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessage;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessageBag;
use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\Content\Text;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\Role;
use PhpLlm\LlmChain\Message\SystemMessage;
use PhpLlm\LlmChain\Message\UserMessage;
use Symfony\Component\HttpFoundation\RequestStack;

use function array_filter;
use function array_map;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function json_decode;
use function json_encode;
use function unlink;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

class Storage
{
    private const SESSION_KEY = 'chat-messages';

    public function __construct(
        private readonly string $lastConversationFilePath,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function reset(): void
    {
        @unlink($this->lastConversationFilePath);
    }

    public function store(): void
    {
        /** @var array<Message>|null $messages */
        $messages = $this->requestStack->getSession()->get(self::SESSION_KEY);
        if ($messages === null) {
            return;
        }

        file_put_contents($this->lastConversationFilePath, json_encode($messages, JSON_PRETTY_PRINT));
    }

    public function load(): void
    {
        if (! file_exists($this->lastConversationFilePath)) {
            return;
        }

        $conversationContent = file_get_contents($this->lastConversationFilePath);
        if ($conversationContent === false) {
            return;
        }

        $messages = json_decode($conversationContent, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($messages)) {
            return;
        }

        $messages = array_map(
            static function (array $messageArr): ExtendedMessage|null {
                $role    = Role::from($messageArr['message']['role']);
                $message = null;
                if ($role === Role::System) {
                    $message = new SystemMessage($messageArr['message']['content']);
                } elseif ($role === Role::Assistant) {
                    $message = new AssistantMessage($messageArr['message']['content']);
                } elseif ($role === Role::User) {
                    $message = new UserMessage(new Text($messageArr['message']['content']));
                }

                if ($message === null) {
                    return null;
                }

                return new ExtendedMessage($message);
            },
            $messages,
        );

        $this->requestStack->getSession()->set(self::SESSION_KEY, new ExtendedMessageBag(...array_filter($messages)));
    }
}
