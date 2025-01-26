<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command\Exception;

use LogicException;

use function sprintf;

final class WorldItemRelationAlreadyExists extends LogicException
{
    public static function fromSourceAndTargetIds(string $sourceItemId, string $targetItemId): self
    {
        return new self(sprintf(
            'The relation between the source item with id "%s" and the target item with id "%s" already exists.',
            $sourceItemId,
            $targetItemId,
        ));
    }
}
