<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\ImageGenerator\Application\Query\GetGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Query\GetImageOfGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

use function assert;

#[AsMessageHandler]
final class StoreImageToLibraryHandler
{
    public function __construct(
        private readonly QueryService $queryService,
        private readonly Connection $connection,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(StoreImageToLibrary $request): void
    {
        $generatorRequest = $this->queryService->query(new GetGeneratorRequest($request->requestId));
        assert($generatorRequest instanceof GeneratorRequest);

        $generatorResult = $this->queryService->query(new GetImageOfGeneratorRequest(
            $request->requestId,
            $request->imageId,
        ));
        assert($generatorResult instanceof GeneratorResult);

        $image = Image::create(
            $generatorRequest->title,
            $generatorResult->mimeType,
            $generatorResult->encodedImage,
            $generatorRequest->userInput->prompt,
        );

        $this->bus->dispatch(new StoreImage($image));
        $this->updateGeneratorResult($request->imageId, $image->getId());
    }

    private function updateGeneratorResult(string $generatorResultId, string $imageId): void
    {
        $this->connection->createQueryBuilder()
            ->update('generator_results')
            ->set('image', ':imageId')
            ->where('id = :generatorResultId')
            ->setParameters([
                'imageId' => $imageId,
                'generatorResultId' => $generatorResultId,
            ])
            ->executeStatement();
    }
}
