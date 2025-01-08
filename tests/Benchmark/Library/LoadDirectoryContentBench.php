<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Benchmark\Library;

use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryParameters;
use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectory;
use ChronicleKeeper\Image\Application\Query\FindImagesByDirectory;
use ChronicleKeeper\Library\Application\Query\FindDirectoriesByParent;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Benchmark\UseSymfonyKernel;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;

use function assert;

class LoadDirectoryContentBench
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
    public function benchLoadDirectoryContent(): void
    {
        // We will check the root directory as for every directory the content is fully loaded
        $directory = RootDirectory::get();

        // See src/Library/Presentation/Controller/Library.php for all queries that are executed
        $this->queryService->query(new FindDocumentsByDirectory($directory->getId()));
        $this->queryService->query(new FindImagesByDirectory($directory->getId()));
        $this->queryService->query(new FindConversationsByDirectoryParameters($directory));
        $this->queryService->query(new FindDirectoriesByParent($directory->getId()));
    }
}
