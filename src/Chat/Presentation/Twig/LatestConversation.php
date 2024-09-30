<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Twig;

use ChronicleKeeper\Chat\Application\Entity\Conversation;
use ChronicleKeeper\Chat\Infrastructure\Repository\ConversationFileStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Chat:Latest', template: 'components/chat/latest-conversations.html.twig')]
class LatestConversation extends AbstractController
{
    use DefaultActionTrait;

    public int $entries = 5;

    public function __construct(
        private readonly ConversationFileStorage $storage,
    ) {
    }

    /** @return Conversation[] */
    public function getConversations(): array
    {
        return $this->storage->findLatestConversations($this->entries);
    }
}
