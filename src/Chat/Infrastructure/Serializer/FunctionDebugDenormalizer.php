<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function is_array;

final class FunctionDebugDenormalizer implements DenormalizerInterface
{
    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [FunctionDebug::class => true];
    }

    /** @inheritDoc */
    public function supportsDenormalization(mixed $data, string $type, string|null $format = null, array $context = []): bool
    {
        return $type === FunctionDebug::class;
    }

    /** @inheritDoc */
    public function denormalize(mixed $data, string $type, string|null $format = null, array $context = []): FunctionDebug
    {
        if (! is_array($data)) {
            throw new InvalidArgumentException('Expected data to be an array for denormalization.');
        }

        return new FunctionDebug(
            $data['tool'],
            $data['arguments'],
            $data['result'] ?? null,
        );
    }
}
