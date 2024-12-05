<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Infrastructure\Serializer;

use ChronicleKeeper\Image\Domain\Entity\SearchVector;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webmozart\Assert\Assert;

class SearchVectorDenormalizer implements DenormalizerInterface
{
    /** @inheritDoc */
    public function denormalize(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): SearchVector {
        Assert::isArray($data);
        Assert::keyExists($data, 'id');
        Assert::keyExists($data, 'imageId');
        Assert::keyExists($data, 'vector');

        return new SearchVector($data['id'], $data['imageId'], $data['vector']);
    }

    /** @inheritDoc */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): bool {
        return $type === SearchVector::class;
    }

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [SearchVector::class => true];
    }
}
