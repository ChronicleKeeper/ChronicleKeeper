<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\DatabaseQueryException;
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
        try {
            $this->platform->beginTransaction();

            $this->platform->createQueryBuilder()->createDelete()
                ->from('generator_results')
                ->where('generatorRequest', '=', $request->requestId)
                ->execute();

            $this->platform->createQueryBuilder()->createDelete()
                ->from('generator_requests')
                ->where('id', '=', $request->requestId)
                ->execute();

            $this->platform->commit();
        } catch (DatabaseQueryException $e) {
            $this->platform->rollback();

            throw $e;
        }
    }
}
