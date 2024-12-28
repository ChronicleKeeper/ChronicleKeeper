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
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

#[AsMessageHandler]
class StoreImageToLibraryHandler
{
    public function __construct(
        public readonly FileAccess $fileAccess,
        public readonly SerializerInterface $serializer,
        public readonly FilesystemImageRepository $imageRepository,
        public readonly QueryService $queryService,
        public readonly MessageBusInterface $bus,
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

        $generatorResult->image = $image;
        $this->bus->dispatch(new StoreGeneratorResult($request->requestId, $generatorResult));
    }
}
