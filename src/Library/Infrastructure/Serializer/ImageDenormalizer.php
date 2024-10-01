<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Serializer;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\Entity\Image;
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

        $image = new Image(
            $data['title'],
            $data['mime_type'],
            $data['encoded_image'],
            $data['description'],
        );

        $image->id        = $data['id'];
        $image->directory = $this->denormalizer->denormalize(
            $data['directory'],
            Directory::class,
            $format,
            $context,
        );
        $image->updatedAt = new DateTimeImmutable($data['last_updated']);

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
