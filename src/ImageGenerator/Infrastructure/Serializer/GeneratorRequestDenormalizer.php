<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Infrastructure\Serializer;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\UserInput;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class GeneratorRequestDenormalizer implements DenormalizerInterface
{
    /** @inheritDoc */
    public function denormalize(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): GeneratorRequest {
        $request     = new GeneratorRequest(
            $data['title'],
            new UserInput($data['userInput']['prompt'], $data['userInput']['systemPrompt']),
        );
        $request->id = $data['id'];

        if ($data['prompt'] !== null) {
            $request->prompt = new OptimizedPrompt($data['prompt']);
        }

        return $request;
    }

    /** @inheritDoc */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): bool {
        return $type === GeneratorRequest::class;
    }

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [GeneratorRequest::class => true];
    }
}
