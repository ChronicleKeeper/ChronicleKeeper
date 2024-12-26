<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Repository;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Image\Domain\Event\ImageDeleted;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function array_filter;
use function array_values;
use function json_encode;
use function json_validate;
use function strcasecmp;
use function usort;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

#[Autoconfigure(lazy: true)]
class FilesystemImageRepository
{
    private const string STORAGE_NAME = 'library.images';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
        private readonly FilesystemVectorImageRepository $vectorRepository,
        private readonly PathRegistry $pathRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function store(Image $image): void
    {
        $filename = $this->generateFilename($image->getId());
        $content  = json_encode($image->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        $this->fileAccess->write(self::STORAGE_NAME, $filename, $content);

        // flush domain Events of the image
        $events = $image->flushEvents();
        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }

    /** @return list<Image> */
    public function findAll(): array
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->pathRegistry->get(self::STORAGE_NAME))
            ->files();

        $images = [];
        foreach ($finder as $file) {
            try {
                $images[] = $this->serializer->deserialize($file->getContents(), Image::class, 'json');
            } catch (RuntimeException $e) {
                $this->logger->error($e, ['file' => $file]);
            }
        }

        usort(
            $images,
            static fn (Image $left, Image $right) => strcasecmp($left->getTitle(), $right->getTitle()),
        );

        return $images;
    }

    /** @return list<Image> */
    public function findByDirectory(Directory $directory): array
    {
        $images = $this->findAll();

        return array_values(array_filter($images, static fn (Image $image) => $image->getDirectory()->equals($directory)));
    }

    public function findById(string $id): Image|null
    {
        $filename = $this->generateFilename($id);

        try {
            $json = $this->fileAccess->read(self::STORAGE_NAME, $filename);

            if (! json_validate($json)) {
                return null;
            }

            return $this->serializer->deserialize($json, Image::class, 'json');
        } catch (UnableToReadFile) {
            return null;
        }
    }

    public function remove(Image $image): void
    {
        $filename = $this->generateFilename($image->getId());

        foreach ($this->vectorRepository->findAllByImageId($image->getId()) as $vectors) {
            $this->vectorRepository->remove($vectors);
        }

        $this->fileAccess->delete(self::STORAGE_NAME, $filename);

        $this->eventDispatcher->dispatch(new ImageDeleted($image));
    }

    /** @return non-empty-string */
    private function generateFilename(string $id): string
    {
        return $id . '.json';
    }
}
