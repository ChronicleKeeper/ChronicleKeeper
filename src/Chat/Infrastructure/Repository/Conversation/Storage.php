<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Repository\Conversation;

use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessageBag;
use PhpLlm\LlmChain\Message\Message;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\SerializerInterface;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function unlink;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

class Storage
{
    private const SESSION_KEY = 'chat-messages';

    public function __construct(
        private readonly string $lastConversationFilePath,
        private readonly RequestStack $requestStack,
        private readonly SerializerInterface $serializer,
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

        file_put_contents(
            $this->lastConversationFilePath,
            $this->serializer->serialize(
                $messages,
                'json',
                [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT],
            ),
        );
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

        $messages = $this->serializer->deserialize($conversationContent, ExtendedMessageBag::class, 'json');

        $this->requestStack->getSession()->set(self::SESSION_KEY, $messages);
    }
}
