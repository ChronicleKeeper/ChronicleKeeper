<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Infrastructure\Serializer;

use ChronicleKeeper\Image\Application\Query\GetImage;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webmozart\Assert\Assert;

use function array_keys;

#[Autoconfigure(lazy: true)]
class VectorImageDenormalizer implements DenormalizerInterface
{
    /** @var array<string, VectorImage> */
    private array $cachedEntries = [];

    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    /** @inheritDoc */
    public function denormalize(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): VectorImage {
        Assert::isArray($data);
        Assert::same(['id', 'imageId', 'content', 'vectorContentHash', 'vector'], array_keys($data));
        Assert::uuid($data['id']);

        if (isset($this->cachedEntries[$data['id']])) {
            return $this->cachedEntries[$data['id']];
        }

        $image = $this->queryService->query(new GetImage($data['imageId']));

        $vectorImage     = new VectorImage(
            $image,
            $data['content'],
            $data['vectorContentHash'],
            $data['vector'],
        );
        $vectorImage->id = $data['id'];

        $this->cachedEntries[$vectorImage->id] = $vectorImage;

        return $vectorImage;
    }

    /** @inheritDoc */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): bool {
        return $type === VectorImage::class;
    }

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [VectorImage::class => true];
    }
}
