<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Benchmark\Library;

use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryParameters;
use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDirectoryRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Benchmark\UseSymfonyKernel;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;

use function assert;

class LoadDirectoryContentBench
{
    use UseSymfonyKernel;

    private FilesystemDirectoryRepository $directoryRepository;
    private FilesystemImageRepository $imageRepository;
    private QueryService $queryService;

    public function setUp(): void
    {
        $kernel = $this->getKernel();

        $imageRepository = $kernel->getContainer()->get(FilesystemImageRepository::class);
        assert($imageRepository instanceof FilesystemImageRepository);
        $this->imageRepository = $imageRepository;

        $directoryRepository = $kernel->getContainer()->get(FilesystemDirectoryRepository::class);
        assert($directoryRepository instanceof FilesystemDirectoryRepository);
        $this->directoryRepository = $directoryRepository;

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
        $this->queryService->query(new FindDocumentsByDirectory($directory->id));
        $this->imageRepository->findByDirectory($directory);
        $this->queryService->query(new FindConversationsByDirectoryParameters($directory));
        $this->directoryRepository->findByParent($directory);
    }
}
