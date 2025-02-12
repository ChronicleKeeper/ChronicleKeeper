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
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

use function assert;

#[AsMessageHandler]
class StoreImageToLibraryHandler
{
    public function __construct(
        private readonly QueryService $queryService,
        private readonly DatabasePlatform $platform,
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

        $this->platform->createQueryBuilder()->createUpdate()
            ->update('generator_results')
            ->set(['image' => $image->getId()])
            ->where('id', '=', $request->imageId)
            ->execute();
    }
}
