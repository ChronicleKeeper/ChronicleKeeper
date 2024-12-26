<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Serializer;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webmozart\Assert\Assert;

use function array_diff;
use function array_keys;
use function is_string;

#[Autoconfigure(lazy: true)]
class ImageDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    /** @var array<string, Image> */
    private array $cachedEntries = [];

    private DenormalizerInterface $denormalizer;

    public function __construct(private readonly FilesystemImageRepository $imageRepository)
    {
    }

    public function setDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->denormalizer = $denormalizer;
    }

    /** @inheritDoc */
    public function denormalize(mixed $data, string $type, string|null $format = null, array $context = []): Image
    {
        if (is_string($data)) {
            return $this->imageRepository->findById($data) ?? throw new NotFoundHttpException();
        }

        Assert::isArray($data);
        Assert::true(array_diff([
            'id',
            'title',
            'mime_type',
            'encoded_image',
            'description',
            'directory',
            'last_updated',
        ], array_keys($data)) === []);
        Assert::uuid($data['id']);

        if (isset($this->cachedEntries[$data['id']])) {
            return $this->cachedEntries[$data['id']];
        }

        $image = new Image(
            $data['id'],
            $data['title'],
            $data['mime_type'],
            $data['encoded_image'],
            $data['description'],
            $this->denormalizer->denormalize(
                $data['directory'],
                Directory::class,
                $format,
                $context,
            ),
            new DateTimeImmutable($data['last_updated']),
        );

        $this->cachedEntries[$image->getId()] = $image;

        return $image;
    }

    /** @inheritDoc */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): bool {
        return $type === Image::class;
    }

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [Image::class => true];
    }
}
