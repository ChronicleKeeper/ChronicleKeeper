<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Twig;

use ChronicleKeeper\Chat\Application\Query\FindLatestConversationsParameters;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Chat:Latest', template: 'components/chat/latest-conversations.html.twig')]
class LatestConversation extends AbstractController
{
    use DefaultActionTrait;

    public int $entries = 5;

    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    /** @return Conversation[] */
    public function getConversations(): array
    {
        return $this->queryService->query(new FindLatestConversationsParameters($this->entries));
    }
}
