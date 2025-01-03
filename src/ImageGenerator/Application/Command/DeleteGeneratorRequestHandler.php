<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteGeneratorRequestHandler
{
    public function __construct(
        private readonly DatabasePlatform $platform,
    ) {
    }

    public function __invoke(DeleteGeneratorRequest $request): void
    {
        $this->platform->query(
            'DELETE FROM generator_results WHERE generatorRequest = :id',
            ['id' => $request->requestId],
        );
        $this->platform->query('DELETE FROM generator_requests WHERE id = :id', ['id' => $request->requestId]);
    }
}
