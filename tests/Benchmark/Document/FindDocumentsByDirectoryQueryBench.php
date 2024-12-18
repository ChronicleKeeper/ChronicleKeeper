<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Benchmark\Document;

use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Benchmark\UseSymfonyKernel;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;

use function assert;

class FindDocumentsByDirectoryQueryBench
{
    use UseSymfonyKernel;

    private QueryService $queryService;

    public function setUp(): void
    {
        $kernel = $this->getKernel();

        $queryService = $kernel->getContainer()->get(QueryService::class);
        assert($queryService instanceof QueryService);

        $this->queryService = $queryService;
    }

    /** @BeforeMethods("setUp") */
    public function benchFindDocumentsByDirectory(): void
    {
        $directory = RootDirectory::get();

        $this->queryService->query(new FindDocumentsByDirectory($directory->id));
    }
}
