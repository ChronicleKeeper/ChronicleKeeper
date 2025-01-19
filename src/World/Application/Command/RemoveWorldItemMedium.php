<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use Webmozart\Assert\Assert;

class RemoveWorldItemMedium
{
    public function __construct(
        public readonly string $itemId,
        public readonly string $mediumId,
        public readonly string $mediumType,
    ) {
        Assert::uuid($itemId, 'The identifier of the item has to be an UUID.');
        Assert::uuid($mediumId, 'The identifier of the medium has to be an UUID.');
        Assert::oneOf(
            $mediumType,
            ['image', 'document', 'conversation'],
            'The medium type has to be either "image" or "document" or "conversation".',
        );
    }
}
