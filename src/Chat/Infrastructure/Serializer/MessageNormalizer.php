<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\Message;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class MessageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [Message::class => true];
    }

    /** @inheritDoc */
    public function supportsNormalization($data, string|null $format = null, array $context = []): bool
    {
        return $data instanceof Message;
    }

    /** @inheritDoc */
    public function normalize(mixed $data, string|null $format = null, array $context = []): array
    {
        if (! $data instanceof Message) {
            throw new InvalidArgumentException('Expected Instance of "' . Message::class . '"');
        }

        return [
            'id' => $data->getId(),
            'role' => $data->getRole()->value,
            'content' => $data->getContent(),
            'context' => $this->normalizer->normalize($data->getContext(), $format, $context),
        ];
    }
}
