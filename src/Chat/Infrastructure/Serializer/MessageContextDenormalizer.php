<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\Reference;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function array_map;
use function array_values;
use function is_array;

final class MessageContextDenormalizer implements DenormalizerInterface
{
    public const string WITH_CONTEXT_DOCUMENTS = 'with_context_documents';
    public const string WITH_CONTEXT_IMAGES    = 'with_context_images';

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [MessageContext::class => true];
    }

    /** @inheritDoc */
    public function supportsDenormalization(mixed $data, string $type, string|null $format = null, array $context = []): bool
    {
        return $type === MessageContext::class;
    }

    /** @inheritDoc */
    public function denormalize(mixed $data, string $type, string|null $format = null, array $context = []): MessageContext
    {
        if (! is_array($data)) {
            throw new InvalidArgumentException('Expected data to be an array for denormalization.');
        }

        $documents = [];
        if (isset($context[self::WITH_CONTEXT_DOCUMENTS]) && $context[self::WITH_CONTEXT_DOCUMENTS] === true) {
            $documents = $this->convertContextToReferences($data['documents'] ?? []);
        }

        $images = [];
        if (isset($context[self::WITH_CONTEXT_IMAGES]) && $context[self::WITH_CONTEXT_IMAGES] === true) {
            $images = $this->convertContextToReferences($data['images'] ?? []);
        }

        return new MessageContext($documents, $images);
    }

    /**
     * @param array<string, mixed> $contextList
     *
     * @return list<Reference>
     */
    private function convertContextToReferences(array $contextList): array
    {
        return array_values(array_map(
            static fn (array $context) => new Reference($context['id'], $context['type'], $context['title']),
            $contextList,
        ));
    }
}
