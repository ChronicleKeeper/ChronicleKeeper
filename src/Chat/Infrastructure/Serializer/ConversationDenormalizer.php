<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\MessageBag;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function is_array;

final class ConversationDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [Conversation::class => true];
    }

    /** @inheritDoc */
    public function supportsDenormalization(mixed $data, string $type, string|null $format = null, array $context = []): bool
    {
        return $type === Conversation::class;
    }

    /** @inheritDoc */
    public function denormalize(mixed $data, string $type, string|null $format = null, array $context = []): Conversation
    {
        if (! is_array($data)) {
            throw new InvalidArgumentException('Expected data to be an array for denormalization.');
        }

        return new Conversation(
            $data['id'],
            $data['title'] ?? '',
            $this->denormalizer->denormalize($data['directory'], Directory::class, $format, $context),
            $this->denormalizer->denormalize($data['settings'] ?? [], Settings::class, $format, $context),
            $this->denormalizer->denormalize($data['messages'] ?? [], MessageBag::class, $format, $context),
        );
    }
}
