<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Benchmark\Image;

use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorImageRepository;
use ChronicleKeeper\Test\Benchmark\UseSymfonyKernel;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;

use function array_fill;
use function assert;

class SearchSimilarVectorsBench
{
    use UseSymfonyKernel;

    private FilesystemVectorImageRepository $vectorImageRepository;

    /** @var list<float> */
    private array $searchForVectors;

    public function setUp(): void
    {
        $kernel = $this->getKernel();

        $vectorImageRepository = $kernel->getContainer()->get(FilesystemVectorImageRepository::class);
        assert($vectorImageRepository instanceof FilesystemVectorImageRepository);

        $this->vectorImageRepository = $vectorImageRepository;
        $this->searchForVectors      = self::getExampleSearchedVector();
    }

    /** @BeforeMethods("setUp") */
    public function benchSearchSimlarImages(): void
    {
        $this->vectorImageRepository->findSimilar($this->searchForVectors, 1.0, 10);
    }

    /** @return list<float> */
    private static function getExampleSearchedVector(): array
    {
        // Deliver a vector with the length of 1535 float values
        return array_fill(0, 1536, 0.1);
    }
}
