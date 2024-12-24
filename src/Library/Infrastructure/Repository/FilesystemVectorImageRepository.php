<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Repository;

use ChronicleKeeper\Image\Application\Query\GetAllVectorSearchImages;
use ChronicleKeeper\Image\Application\Query\GetVectorImage;
use ChronicleKeeper\Image\Domain\Entity\SearchVector;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\Distance\CosineDistance;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\SerializerInterface;

use function array_filter;
use function array_keys;
use function array_slice;
use function array_values;
use function asort;
use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

#[Autoconfigure(lazy: true)]
class FilesystemVectorImageRepository
{
    private const string STORAGE_NAME = 'vector.images';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CosineDistance $distance,
        private readonly FileAccess $fileAccess,
        private readonly SettingsHandler $settingsHandler,
        private readonly PathRegistry $pathRegistry,
        private readonly QueryService $queryService,
        private readonly SerializerInterface $serializer,
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
                $content = $file->getContents();

                $images[] = $this->serializer->deserialize($content, VectorImage::class, 'json');
            } catch (RuntimeException | UnableToReadFile $e) {
                $this->logger->debug($e);
            }
        }

        return $images;
    }

    /**
     * @param list<float> $searchedVectors
     *
     * @return list<array{vector: VectorImage, distance: float}>
     */
    public function findSimilar(array $searchedVectors, float|null $maxDistance = null, int $maxResults = 4): array
    {
        $distances = [];

        /** @var list<SearchVector> $searchVectors */
        $searchVectors = $this->queryService->query(new GetAllVectorSearchImages());

        $maxDistance ??= $this->settingsHandler->get()->getChatbotTuning()->getImagesMaxDistance();

        foreach ($searchVectors as $index => $search) {
            $dist = $this->distance->measure($searchedVectors, $search->vectors);
            if ($dist > $maxDistance) {
                unset($searchVectors[$index]);
                continue;
            }

            $distances[$index] = $dist;
        }

        asort($distances);

        $topKIndices = array_slice(array_keys($distances), 0, $maxResults, true);

        $results = [];
        foreach ($topKIndices as $index) {
            $vectorImage = $this->queryService->query(new GetVectorImage($searchVectors[$index]->id));

            $results[] = [
                'vector' => $vectorImage,
                'distance' => $distances[$index],
            ];
        }

        return $results;
    }

    /** @return list<VectorImage> */
    public function findAllByImageId(string $id): array
    {
        return array_values(array_filter(
            $this->findAll(),
            static fn (VectorImage $vectorImage): bool => $vectorImage->image->id === $id,
        ));
    }

    public function remove(VectorImage $vectorImage): void
    {
        $filename = $this->generateFilename($vectorImage->id);
        $this->fileAccess->delete(self::STORAGE_NAME, $filename);
    }

    /** @return non-empty-string */
    private function generateFilename(string $id): string
    {
        return $id . '.json';
    }
}
