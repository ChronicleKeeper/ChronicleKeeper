<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Service\PromptOptimizer;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

#[AsMessageHandler]
class StoreGeneratorRequestHandler
{
    public function __construct(
        public readonly FileAccess $fileAccess,
        public readonly SerializerInterface $serializer,
        public readonly PromptOptimizer $promptOptimizer,
    ) {
    }

    public function __invoke(StoreGeneratorRequest $request): void
    {
        if ($request->request->prompt === null) {
            $optimizedPrompt          = $this->promptOptimizer->optimize($request->request->userInput->prompt);
            $request->request->prompt = new OptimizedPrompt($optimizedPrompt);
        }

        $this->fileAccess->write(
            'generator.images',
            $request->request->id . '.json',
            $this->serializer->serialize(
                $request->request,
                JsonEncoder::FORMAT,
                [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT],
            ),
        );
    }
}
