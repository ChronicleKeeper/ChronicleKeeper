<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Serializer;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDirectoryRepository;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webmozart\Assert\Assert;

use function array_diff;
use function array_keys;
use function is_string;

#[Autoconfigure(lazy: true)]
class DirectoryDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    private DenormalizerInterface $denormalizer;

    public function __construct(private readonly FilesystemDirectoryRepository $documentRepository)
    {
    }

    public function setDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->denormalizer = $denormalizer;
    }

    /** @inheritDoc */
    public function denormalize(mixed $data, string $type, string|null $format = null, array $context = []): Directory
    {
        if (is_string($data)) {
            return $this->documentRepository->findById($data) ?? RootDirectory::get(); // Throw Exception instead?
        }

        Assert::isArray($data);
        Assert::true(array_diff(['id', 'title', 'parent'], array_keys($data)) === []);

        $directory         = new Directory($data['title']);
        $directory->id     = $data['id'];
        $directory->parent = $this->denormalizer->denormalize($data['parent'], Directory::class, $format, $context);

        return $directory;
    }

    /** @inheritDoc */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): bool {
        return $type === Directory::class;
    }

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [Directory::class => true];
    }
}
