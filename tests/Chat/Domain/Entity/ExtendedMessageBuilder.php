<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageDebug;
use ChronicleKeeper\Chat\Domain\ValueObject\Role;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Message\MessageInterface;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use Symfony\Component\Uid\Uuid;

class ExtendedMessageBuilder
{
    private string $id;
    private MessageInterface $message;
    private MessageContext $context;
    private MessageDebug $debug;

    public function __construct()
    {
        $this->id      = Uuid::v4()->toString();
        $this->message = new UserMessage(new Text('Default message'));
        $this->context = new MessageContext();
        $this->debug   = new MessageDebug();
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withMessage(MessageInterface $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function withContext(MessageContext $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function withDebug(MessageDebug $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function build(): Message
    {
        // Convert LLMChain message to our internal Message format
        $role    = $this->convertToRole($this->message);
        $content = $this->extractContent($this->message);

        return new Message(
            $this->id,
            $role,
            $content,
            $this->context,
            $this->debug,
        );
    }

    private function convertToRole(MessageInterface $message): Role
    {
        return match ($message->getRole()->value) {
            'system' => Role::SYSTEM,
            'user' => Role::USER,
            'assistant' => Role::ASSISTANT,
            default => Role::USER,
        };
    }

    private function extractContent(MessageInterface $message): string
    {
        if ($message instanceof SystemMessage || $message instanceof AssistantMessage) {
            return (string) $message->content;
        }

        if ($message instanceof UserMessage) {
            $content = $message->content[0] ?? null;
            if ($content instanceof Text) {
                return $content->text;
            }
        }

        return '';
    }
}
