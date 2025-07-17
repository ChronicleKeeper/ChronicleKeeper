<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\MessageBag;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class MessageBagNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [MessageBag::class => true];
    }

    /** @inheritDoc */
    public function supportsNormalization(mixed $data, string|null $format = null, array $context = []): bool
    {
        return $data instanceof MessageBag;
    }

    /** @inheritDoc */
    public function normalize(mixed $data, string|null $format = null, array $context = []): array
    {
        if (! $data instanceof MessageBag) {
            throw new InvalidArgumentException('Expected Instance of "' . MessageBag::class . '"');
        }

        $messages = [];
        foreach ($data as $message) {
            $messages[] = $this->normalizer->normalize($message, $format, $context);
        }

        return $messages;
    }
}
