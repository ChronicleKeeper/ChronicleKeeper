<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Chat\Domain\Entity\MessageBag;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function is_array;

final class MessageBagDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [MessageBag::class => true];
    }

    /** @inheritDoc */
    public function supportsDenormalization(mixed $data, string $type, string|null $format = null, array $context = []): bool
    {
        return $type === MessageBag::class;
    }

    /** @inheritDoc */
    public function denormalize(mixed $data, string $type, string|null $format = null, array $context = []): MessageBag
    {
        if (! is_array($data)) {
            throw new InvalidArgumentException('Expected data to be an array for denormalization.');
        }

        $messages = [];
        foreach ($data as $messageData) {
            $messages[] = $this->denormalizer->denormalize($messageData, Message::class, $format, $context);
        }

        return new MessageBag(...$messages);
    }
}
