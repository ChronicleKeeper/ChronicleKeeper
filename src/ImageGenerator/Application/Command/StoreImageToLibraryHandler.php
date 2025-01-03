<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\ImageGenerator\Application\Query\GetGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Query\GetImageOfGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

use function assert;

#[AsMessageHandler]
class StoreImageToLibraryHandler
{
    public function __construct(
        private readonly FilesystemImageRepository $imageRepository,
        private readonly QueryService $queryService,
        private readonly DatabasePlatform $platform,
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

        $this->imageRepository->store($image);

        $this->platform->query('UPDATE generator_results SET image = :image WHERE id = :id', [
            'image' => $image->getId(),
            'id'    => $request->imageId,
        ]);
    }
}
