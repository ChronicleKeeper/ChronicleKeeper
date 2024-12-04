<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Benchmark\Document\Application\Query;

use ChronicleKeeper\Document\Application\Query\SearchSimilarVectors;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Kernel;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;

use function array_fill;
use function assert;

class SearchSimilarVectorsQueryBench
{
    private QueryService $queryService;

    /** @var list<float> */
    private array $searchForVectors;

    public function setUp(): void
    {
        $kernel = new Kernel('bench', false);
        $kernel->boot();

        $queryService = $kernel->getContainer()->get(QueryService::class);
        assert($queryService instanceof QueryService);

        $this->queryService     = $queryService;
        $this->searchForVectors = self::getExampleSearchedVector();
    }

    /** @BeforeMethods("setUp") */
    public function benchSearchSimlarDocuments(): void
    {
        $result = $this->queryService->query(
            new SearchSimilarVectors(
                $this->searchForVectors,
                1.0,
                10,
            ),
        );
    }

    /** @return list<float> */
    private static function getExampleSearchedVector(): array
    {
        // Deliver a vector with the length of 1535 float values
        return array_fill(0, 1536, 0.1);
    }
}
