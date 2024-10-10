<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Service\PromptOptimizer;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use const DIRECTORY_SEPARATOR;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

#[AsMessageHandler]
class StoreGeneratorResultHandler
{
    public function __construct(
        public readonly FileAccess $fileAccess,
        public readonly SerializerInterface $serializer,
        public readonly PromptOptimizer $promptOptimizer,
    ) {
    }

    public function __invoke(StoreGeneratorResult $request): void
    {
        $requestImagesDirectory = DIRECTORY_SEPARATOR . $request->requestId;

        $this->fileAccess->write(
            'generator.images',
            $requestImagesDirectory . DIRECTORY_SEPARATOR . $request->generatorResult->id . '.json',
            $this->serializer->serialize(
                $request->generatorResult,
                JsonEncoder::FORMAT,
                [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT],
            ),
        );
    }
}
