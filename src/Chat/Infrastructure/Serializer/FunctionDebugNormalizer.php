<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class FunctionDebugNormalizer implements NormalizerInterface
{
    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [FunctionDebug::class => true];
    }

    /** @inheritDoc */
    public function supportsNormalization(mixed $data, string|null $format = null, array $context = []): bool
    {
        return $data instanceof FunctionDebug;
    }

    /** @inheritDoc */
    public function normalize(mixed $data, string|null $format = null, array $context = []): array
    {
        if (! $data instanceof FunctionDebug) {
            throw new InvalidArgumentException('Expected Instance of "' . FunctionDebug::class . '"');
        }

        return [
            'tool' => $data->tool,
            'arguments' => $data->arguments,
            'result' => $data->result,
        ];
    }
}
