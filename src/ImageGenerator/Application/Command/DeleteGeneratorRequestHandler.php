<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Service\PromptOptimizer;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\SerializerInterface;

#[AsMessageHandler]
class DeleteGeneratorRequestHandler
{
    public function __construct(
        public readonly FileAccess $fileAccess,
        public readonly SerializerInterface $serializer,
        public readonly PromptOptimizer $promptOptimizer,
    ) {
    }

    public function __invoke(DeleteGeneratorRequest $request): void
    {
        // First delete the generated images
        $this->fileAccess->delete('generator.images', $request->requestId);

        // Second delete the request itself
        $this->fileAccess->delete('generator.requests', $request->requestId . '.json');
    }
}
