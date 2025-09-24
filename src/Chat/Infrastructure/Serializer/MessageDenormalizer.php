<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageDebug;
use ChronicleKeeper\Chat\Domain\ValueObject\Role;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function array_values;
use function is_array;

final class MessageDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public const string WITH_DEBUG_FUNCTIONS = 'with_debug_functions';

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [Message::class => true];
    }

    /** @inheritDoc */
    public function supportsDenormalization(mixed $data, string $type, string|null $format = null, array $context = []): bool
    {
        return $type === Message::class;
    }

    /** @inheritDoc */
    public function denormalize(mixed $data, string $type, string|null $format = null, array $context = []): Message
    {
        if (! is_array($data)) {
            throw new InvalidArgumentException('Expected data to be an array for denormalization.');
        }

        $debug = [];
        if (isset($context[self::WITH_DEBUG_FUNCTIONS]) && $context[self::WITH_DEBUG_FUNCTIONS] === true) {
            $debug = $this->denormalizer->denormalize(
                $data['debug'] ?? [],
                FunctionDebug::class . '[]',
                $format,
                $context,
            );
        }

        return new Message(
            $data['id'],
            Role::from($data['role'] ?? $data['message']['role']),
            $data['content'] ?? $data['message']['content'],
            $this->denormalizer->denormalize($data['context'], MessageContext::class, $format, $context),
            new MessageDebug(array_values($debug)),
        );
    }
}
