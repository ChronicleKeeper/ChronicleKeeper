<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Twig;

use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Application\Query\FindWorldLinksOfMedium;
use ChronicleKeeper\World\Domain\Entity\Item;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('World:ShowWorldLinks', template: 'components/world/show_world_links.html.twig')]
class ShowWorldLinks
{
    public string $type;
    public string $mediumId;

    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    /** @return Item[] */
    public function getLinks(): array
    {
        return $this->queryService->query(new FindWorldLinksOfMedium($this->type, $this->mediumId));
    }
}
