<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Service\PromptOptimizer;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsMessageHandler]
class StoreGeneratorRequestHandler
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly PromptOptimizer $promptOptimizer,
        private readonly DatabasePlatform $platform,
    ) {
    }

    public function __invoke(StoreGeneratorRequest $request): void
    {
        if (! $request->request->prompt instanceof OptimizedPrompt) {
            $optimizedPrompt          = $this->promptOptimizer->optimize($request->request->userInput->prompt);
            $request->request->prompt = new OptimizedPrompt($optimizedPrompt);
        }

        $this->platform->createQueryBuilder()->createInsert()
            ->asReplace()
            ->insert('generator_requests')
            ->values([
                'id'       => $request->request->id,
                'title'    => $request->request->title,
                'userInput' => $this->serializer->serialize($request->request->userInput, JsonEncoder::FORMAT),
                'prompt'   => $request->request->prompt->prompt,
            ])
            ->execute();
    }
}
