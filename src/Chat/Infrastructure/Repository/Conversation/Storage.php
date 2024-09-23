<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Chat\Infrastructure\Repository\Conversation;

use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Message\Role;
use Symfony\Component\HttpFoundation\RequestStack;

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
            static function (array $messageArr): Message {
                return new Message($messageArr['content'], Role::from($messageArr['role']));
            },
            $messages,
        );

        $this->requestStack->getSession()->set(self::SESSION_KEY, new MessageBag(...$messages));
    }
}
