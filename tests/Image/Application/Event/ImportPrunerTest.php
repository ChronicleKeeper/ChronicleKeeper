<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Event;

use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Image\Application\Command\StoreImageVectors;
use ChronicleKeeper\Image\Application\Event\ImportPruner;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\VectorImageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(ImportPruner::class)]
#[Large]
class ImportPrunerTest extends DatabaseTestCase
{
    private ImportPruner $importPruner;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(ImportPruner::class);
        assert($handler instanceof ImportPruner);

        $this->importPruner = $handler;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->importPruner);
    }

    #[Test]
    public function itIsPruning(): void
    {
        // ------------------- The test setup -------------------

        $image = (new ImageBuilder())->build();
        $this->bus->dispatch(new StoreImage($image));

        $imageVectors = (new VectorImageBuilder())->withImage($image)->build();
        $this->bus->dispatch(new StoreImageVectors($imageVectors));

        // ------------------- The test assertions -------------------

        ($this->importPruner)(new ExecuteImportPruning(new ImportSettings()));

        // ------------------- The test assertions -------------------

        $this->assertTableIsEmpty('images_vectors');
        $this->assertTableIsEmpty('images');
    }
}
