<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Infrastructure\Serializer;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\GeneratorResult;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\UserInput;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class GeneratorRequestDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    private DenormalizerInterface $denormalizer;

    public function setDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->denormalizer = $denormalizer;
    }

    /** @inheritDoc */
    public function denormalize(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): GeneratorRequest {
        $request     = new GeneratorRequest($data['title'], new UserInput($data['userInput']));
        $request->id = $data['id'];

        if ($data['prompt'] !== null) {
            $request->prompt = new OptimizedPrompt($data['prompt']);
        }

        $request->exchangeArray($this->denormalizer->denormalize(
            $data['results'],
            GeneratorResult::class . '[]',
            $format,
            $context,
        ));

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
