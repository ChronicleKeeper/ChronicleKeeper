<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use Webmozart\Assert\Assert;

class RemoveItemRelation
{
    public function __construct(
        public string $sourceItemId,
        public string $targetItemId,
        public string $relationType,
    ) {
        Assert::uuid($sourceItemId, 'The source item identifier has to be an UUID.');
        Assert::uuid($targetItemId, 'The target item identifier has to be an UUID.');
        Assert::notEmpty($relationType, 'The relation type has to be a non-empty string.');
    }
}
