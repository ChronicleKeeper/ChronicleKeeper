<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Domain\ValueObject;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\World\Domain\Entity\Item;

class ConversationReference implements MediaReference
{
    public function __construct(
        public readonly Item $item,
        public readonly Conversation $conversation,
    ) {
    }

    public function getIcon(): string
    {
        return 'tabler:message';
    }

    public function getMediaId(): string
    {
        return $this->conversation->getId();
    }

    public function getMediaTitle(): string
    {
        return $this->conversation->getTitle();
    }

    public function getMediaDisplayName(): string
    {
        return $this->conversation->getDirectory()->flattenHierarchyTitle(true) . ' > ' . $this->conversation->getTitle();
    }

    public function getGenericLinkIdentifier(): string
    {
        return 'conversation_' . $this->conversation->getId();
    }
}
