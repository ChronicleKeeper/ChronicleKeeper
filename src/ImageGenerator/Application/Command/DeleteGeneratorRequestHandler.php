<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteGeneratorRequestHandler
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(DeleteGeneratorRequest $request): void
    {
        try {
            $this->connection->beginTransaction();

            // Delete related generator results first
            $this->connection->createQueryBuilder()
                ->delete('generator_results')
                ->where('"generatorRequest" = :requestId')
                ->setParameter('requestId', $request->requestId)
                ->executeStatement();

            // Delete the generator request
            $this->connection->createQueryBuilder()
                ->delete('generator_requests')
                ->where('id = :requestId')
                ->setParameter('requestId', $request->requestId)
                ->executeStatement();

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }
}
