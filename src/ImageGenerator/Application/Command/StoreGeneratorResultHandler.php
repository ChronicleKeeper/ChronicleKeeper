<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @phpstan-type preparedDataArray array{
 *      id: string,
 *      generatorRequest: string,
 *      encodedImage: string,
 *      revisedPrompt: string,
 *      mimeType: string,
 *      image: string|null
 *  }
 */
#[AsMessageHandler]
final class StoreGeneratorResultHandler
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(StoreGeneratorResult $request): void
    {
        $data = $this->prepareData($request);

        try {
            $this->connection->beginTransaction();
            $this->upsertGeneratorResult($data);
            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }

    /** @return preparedDataArray */
    private function prepareData(StoreGeneratorResult $request): array
    {
        return [
            'id' => $request->generatorResult->id,
            'generatorRequest' => $request->requestId,
            'encodedImage' => $request->generatorResult->encodedImage,
            'revisedPrompt' => $request->generatorResult->revisedPrompt,
            'mimeType' => $request->generatorResult->mimeType,
            'image' => $request->generatorResult->image?->getId(),
        ];
    }

    /** @param preparedDataArray $data */
    private function upsertGeneratorResult(array $data): void
    {
        $exists = $this->recordExists($data['id']);

        if ($exists) {
            $this->updateGeneratorResult($data);
        } else {
            $this->insertGeneratorResult($data);
        }
    }

    private function recordExists(string $id): bool
    {
        $result = $this->connection->createQueryBuilder()
            ->select('1')
            ->from('generator_results')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchOne();

        return $result !== false;
    }

    /** @param array<string, string|null> $data */
    private function updateGeneratorResult(array $data): void
    {
        $this->connection->createQueryBuilder()
            ->update('generator_results')
            ->set('"generatorRequest"', ':generatorRequest')
            ->set('"encodedImage"', ':encodedImage')
            ->set('"revisedPrompt"', ':revisedPrompt')
            ->set('"mimeType"', ':mimeType')
            ->set('image', ':image')
            ->where('id = :id')
            ->setParameters([
                'id' => $data['id'],
                'generatorRequest' => $data['generatorRequest'],
                'encodedImage' => $data['encodedImage'],
                'revisedPrompt' => $data['revisedPrompt'],
                'mimeType' => $data['mimeType'],
                'image' => $data['image'],
            ])
            ->executeStatement();
    }

    /** @param array<string, string|null> $data */
    private function insertGeneratorResult(array $data): void
    {
        $this->connection->createQueryBuilder()
            ->insert('generator_results')
            ->values([
                'id' => ':id',
                '"generatorRequest"' => ':generatorRequest',
                '"encodedImage"' => ':encodedImage',
                '"revisedPrompt"' => ':revisedPrompt',
                '"mimeType"' => ':mimeType',
                'image' => ':image',
            ])
            ->setParameters([
                'id' => $data['id'],
                'generatorRequest' => $data['generatorRequest'],
                'encodedImage' => $data['encodedImage'],
                'revisedPrompt' => $data['revisedPrompt'],
                'mimeType' => $data['mimeType'],
                'image' => $data['image'],
            ])
            ->executeStatement();
    }
}
