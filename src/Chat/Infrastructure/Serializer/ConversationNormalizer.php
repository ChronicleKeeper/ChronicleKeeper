<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ConversationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [Conversation::class => true];
    }

    /** @inheritDoc */
    public function supportsNormalization(mixed $data, string|null $format = null, array $context = []): bool
    {
        return $data instanceof Conversation;
    }

    /** @inheritDoc */
    public function normalize(mixed $data, string|null $format = null, array $context = []): array
    {
        if (! $data instanceof Conversation) {
            throw new InvalidArgumentException('Expected Instance of "' . Conversation::class . '"');
        }

        return [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'directory' => $this->normalizer->normalize($data->getDirectory(), $format, $context),
            'settings' => $this->normalizer->normalize($data->getSettings(), $format, $context),
            'messages' => $this->normalizer->normalize($data->getMessages(), $format, $context),
        ];
    }
}
