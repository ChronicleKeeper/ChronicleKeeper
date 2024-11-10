<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Infrastructure\Serializer;

use ChronicleKeeper\Favorizer\Domain\ValueObject\ChatConversationTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class TargetDenormalizer implements DenormalizerInterface
{
    /** @inheritDoc */
    public function denormalize(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): Target {
        if (! isset($data['type'])) {
            throw new InvalidArgumentException('Target type is missing.');
        }

        if ($data['type'] === 'LibraryDocumentTarget') {
            return new LibraryDocumentTarget($data['id'], $data['title']);
        }

        if ($data['type'] === 'LibraryImageTarget') {
            return new LibraryImageTarget($data['id'], $data['title']);
        }

        if ($data['type'] === 'ChatConversationTarget') {
            return new ChatConversationTarget($data['id'], $data['title']);
        }

        throw new InvalidArgumentException('Unknown target type.');
    }

    /** @inheritDoc */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): bool {
        return $type === Target::class;
    }

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [Target::class => true];
    }
}
