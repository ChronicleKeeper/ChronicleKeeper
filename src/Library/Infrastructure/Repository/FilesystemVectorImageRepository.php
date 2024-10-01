<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Repository;

use ChronicleKeeper\Library\Infrastructure\VectorStorage\Distance\CosineDistance;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function array_filter;
use function array_keys;
use function array_slice;
use function asort;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_readable;
use function json_decode;
use function json_encode;
use function json_validate;

use const DIRECTORY_SEPARATOR;
use const JSON_PRETTY_PRINT;

#[Autoconfigure(lazy: true)]
class FilesystemVectorImageRepository
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly FilesystemImageRepository $imageRepository,
        private readonly CosineDistance $distance,
        private readonly Filesystem $filesystem,
        private readonly string $vectorImagesPath,
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function store(VectorImage $vectorImage): void
    {
        $filename = $vectorImage->id . '.json';
        $filepath = $this->vectorImagesPath . DIRECTORY_SEPARATOR . $filename;
        $array    = $vectorImage->toArray();
        $json     = json_encode($array, JSON_PRETTY_PRINT);

        file_put_contents($filepath, $json);
    }

    /** @return list<VectorImage> */
    public function findAll(): array
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->vectorImagesPath)
            ->files();

        $images = [];
        foreach ($finder as $found) {
            try {
                $images[] = $this->convertJsonToVectorImage($found->getContents());
            } catch (RuntimeException $e) {
                $this->logger->debug($e);
            }
        }

        return $images;
    }

    public function findById(string $id): VectorImage|null
    {
        $json = $this->getContentOfVectorImageFile($id . '.json');
        if ($json === null || ! json_validate($json)) {
            return null;
        }

        try {
            return $this->convertJsonToVectorImage($json);
        } catch (RuntimeException) {
            return null;
        }
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

        // Maximum of distance an image is allowed to be away from the result
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

        asort($distances); // Sort by distance (ascending).

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
        $filepath = $this->vectorImagesPath . DIRECTORY_SEPARATOR . $vectorImage->id . '.json';
        if (! file_exists($filepath) || ! is_readable($filepath)) {
            return;
        }

        $this->filesystem->remove($filepath);
    }

    private function convertJsonToVectorImage(string $json): VectorImage
    {
        $vectorImageArr = json_decode($json, true);

        if (! is_array($vectorImageArr) || ! VectorImage::isVectorImageArray($vectorImageArr)) {
            throw new RuntimeException('Image to load contain invalid content.');
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

    private function getContentOfVectorImageFile(string $filename): string|null
    {
        $filepath = $this->vectorImagesPath . DIRECTORY_SEPARATOR . $filename;
        if (! file_exists($filepath) || ! is_readable($filepath)) {
            return null;
        }

        $json = file_get_contents($filepath);
        if ($json === false || ! json_validate($json)) {
            return null;
        }

        return $json;
    }
}
