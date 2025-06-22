<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Service\PromptOptimizer;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/** @phpstan-type preparedDataArray array{id: string, title: string, 'userInput': string, prompt: string|null} */
#[AsMessageHandler]
final class StoreGeneratorRequestHandler
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly PromptOptimizer $promptOptimizer,
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(StoreGeneratorRequest $request): void
    {
        if (! $request->request->prompt instanceof OptimizedPrompt) {
            $optimizedPrompt          = $this->promptOptimizer->optimize($request->request->userInput->prompt);
            $request->request->prompt = new OptimizedPrompt($optimizedPrompt);
        }

        $data = $this->prepareData($request);

        try {
            $this->connection->beginTransaction();
            $this->upsertGeneratorRequest($data);
            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }

    /** @return preparedDataArray */
    private function prepareData(StoreGeneratorRequest $request): array
    {
        return [
            'id' => $request->request->id,
            'title' => $request->request->title,
            'userInput' => $this->serializer->serialize($request->request->userInput, JsonEncoder::FORMAT),
            'prompt' => $request->request->prompt?->prompt,
        ];
    }

    /** @param preparedDataArray $data */
    private function upsertGeneratorRequest(array $data): void
    {
        $exists = $this->recordExists($data['id']);

        if ($exists) {
            $this->updateGeneratorRequest($data);
        } else {
            $this->insertGeneratorRequest($data);
        }
    }

    private function recordExists(string $id): bool
    {
        $result = $this->connection->createQueryBuilder()
            ->select('1')
            ->from('generator_requests')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchOne();

        return $result !== false;
    }

    /** @param preparedDataArray $data */
    private function updateGeneratorRequest(array $data): void
    {
        $this->connection->createQueryBuilder()
            ->update('generator_requests')
            ->set('title', ':title')
            ->set('"userInput"', ':userInput')
            ->set('prompt', ':prompt')
            ->where('id = :id')
            ->setParameters([
                'id' => $data['id'],
                'title' => $data['title'],
                'userInput' => $data['userInput'],
                'prompt' => $data['prompt'],
            ])
            ->executeStatement();
    }

    /** @param preparedDataArray $data */
    private function insertGeneratorRequest(array $data): void
    {
        $this->connection->createQueryBuilder()
            ->insert('generator_requests')
            ->values([
                'id' => ':id',
                'title' => ':title',
                '"userInput"' => ':userInput',
                'prompt' => ':prompt',
            ])
            ->setParameters([
                'id' => $data['id'],
                'title' => $data['title'],
                'userInput' => $data['userInput'],
                'prompt' => $data['prompt'],
            ])
            ->executeStatement();
    }
}
