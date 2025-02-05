<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreGeneratorResultHandler
{
    public function __construct(private readonly DatabasePlatform $platform)
    {
    }

    public function __invoke(StoreGeneratorResult $request): void
    {
        $this->platform->createQueryBuilder()->createInsert()
            ->asReplace()
            ->insert('generator_results')
            ->values([
                'id'             => $request->generatorResult->id,
                'generatorRequest' => $request->requestId,
                'encodedImage'   => $request->generatorResult->encodedImage,
                'revisedPrompt'  => $request->generatorResult->revisedPrompt,
                'mimeType'       => $request->generatorResult->mimeType,
                'image'          => $request->generatorResult->image?->getId(),
            ])
            ->execute();
    }
}
