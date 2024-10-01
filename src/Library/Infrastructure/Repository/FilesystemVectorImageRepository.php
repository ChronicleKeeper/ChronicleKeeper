<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Repository;

use ChronicleKeeper\Library\Infrastructure\VectorStorage\Distance\CosineDistance;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function array_filter;
use function array_keys;
use function array_slice;
use function asort;
use function is_array;
use function json_decode;
use function json_encode;
use function json_validate;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

#[Autoconfigure(lazy: true)]
class FilesystemVectorImageRepository
{
    private const STORAGE_NAME = 'vector.images';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly FilesystemImageRepository $imageRepository,
        private readonly CosineDistance $distance,
        private readonly FileAccess $fileAccess,
        private readonly SettingsHandler $settingsHandler,
        private readonly PathRegistry $pathRegistry,
    ) {
    }

    public function store(VectorImage $vectorImage): void
    {
        $filename = $this->generateFilename($vectorImage->id);
        $content  = json_encode($vectorImage->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        $this->fileAccess->write(self::STORAGE_NAME, $filename, $content);
    }

    /** @return list<VectorImage> */
    public function findAll(): array
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->pathRegistry->get(self::STORAGE_NAME))
            ->files();

        $images = [];
        foreach ($finder as $file) {
            try {
                $images[] = $this->convertJsonToVectorImage($file->getContents());
            } catch (RuntimeException | UnableToReadFile $e) {
                $this->logger->debug($e);
            }
        }

        return $images;
    }

    public function findById(string $id): VectorImage|null
    {
        $filename = $this->generateFilename($id);
        $json     = $this->fileAccess->read(self::STORAGE_NAME, $filename);

        if (! json_validate($json)) {
            return null;
        }

        return $this->convertJsonToVectorImage($json);
    }

    /**
     * @param list<float> $searchedVectors
     *
     * @return list<array{vector: VectorImage, distance: float}>
     */
    public function findSimilar(array $searchedVectors, float|null $maxDistance = null, int $maxResults = 4): array
    {
        $distances    = [];
        $vectorImages = $this->findAll();

        $maxDistance ??= $this->settingsHandler->get()->getChatbotTuning()->getImagesMaxDistance();

        foreach ($vectorImages as $index => $image) {
            if ($image->image->description === '') {
                continue;
            }

            $dist = $this->distance->measure($searchedVectors, $image->vector);
            if ($dist > $maxDistance) {
                unset($vectorImages[$index]);
                continue;
            }

            $distances[$index] = $dist;
        }

        asort($distances);

        $topKIndices = array_slice(array_keys($distances), 0, $maxResults, true);

        $results = [];
        foreach ($topKIndices as $index) {
            $results[] = [
                'vector' => $vectorImages[$index],
                'distance' => $distances[$index],
            ];
        }

        return $results;
    }

    /** @return list<VectorImage> */
    public function findAllByImageId(string $id): array
    {
        return array_filter(
            $this->findAll(),
            static fn (VectorImage $vectorImage): bool => $vectorImage->image->id === $id,
        );
    }

    public function remove(VectorImage $vectorImage): void
    {
        $filename = $this->generateFilename($vectorImage->id);
        $this->fileAccess->delete(self::STORAGE_NAME, $filename);
    }

    private function convertJsonToVectorImage(string $json): VectorImage
    {
        $vectorImageArr = json_decode($json, true);

        if (! is_array($vectorImageArr) || ! VectorImage::isVectorImageArray($vectorImageArr)) {
            throw new RuntimeException('Image to load contains invalid content.');
        }

        $image = $this->imageRepository->findById($vectorImageArr['imageId']) ?? throw new NotFoundHttpException();

        $vectorImage     = new VectorImage(
            image: $image,
            vectorContentHash: $vectorImageArr['vectorContentHash'],
            vector: $vectorImageArr['vector'],
        );
        $vectorImage->id = $vectorImageArr['id'];

        return $vectorImage;
    }

    /** @return non-empty-string */
    private function generateFilename(string $id): string
    {
        return $id . '.json';
    }
}
