<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\Reference;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use function array_map;

final class MessageContextNormalizer implements NormalizerInterface
{
    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [MessageContext::class => true];
    }

    /** @inheritDoc */
    public function supportsNormalization(mixed $data, string|null $format = null, array $context = []): bool
    {
        return $data instanceof MessageContext;
    }

    /** @inheritDoc */
    public function normalize(mixed $data, string|null $format = null, array $context = []): array
    {
        if (! $data instanceof MessageContext) {
            throw new InvalidArgumentException('Expected Instance of "' . MessageContext::class . '"');
        }

        return [
            'documents' => array_map(
                static fn (Reference $reference) => [
                    'id' => $reference->id,
                    'type' => $reference->type,
                    'title' => $reference->title,
                ],
                $data->documents,
            ),
            'images' => array_map(
                static fn (Reference $reference) => [
                    'id' => $reference->id,
                    'type' => $reference->type,
                    'title' => $reference->title,
                ],
                $data->images,
            ),
        ];
    }
}
