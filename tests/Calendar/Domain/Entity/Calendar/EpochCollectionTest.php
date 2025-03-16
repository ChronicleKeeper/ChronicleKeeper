<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\EpochCollection;
use ChronicleKeeper\Calendar\Domain\ValueObject\Epoch;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(EpochCollection::class)]
#[CoversClass(Epoch::class)]
#[Small]
final class EpochCollectionTest extends TestCase
{
    #[Test]
    public function itWillFailCreationWithoutAnyEpochs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least a single epoch should be given.');

        new EpochCollection();
    }

    #[Test]
    public function itWillFailCreationWithOverlappingEpochs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Epochs overlap at year 2 to 2');

        new EpochCollection(
            new Epoch('First Epoch', 1, 2),
            new Epoch('Second Epoch', 2, 3),
        );
    }

    #[Test]
    public function itWillFailCreationWithGapBetweenEpochs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gap between epochs at year 2 to 4');

        new EpochCollection(
            new Epoch('First Epoch', 1, 2),
            new Epoch('Second Epoch', 4, 5),
        );
    }

    #[Test]
    public function itWillFailCreationWithEpochsNotEndingInLastOne(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only the last epoch can have an undefined end year');

        new EpochCollection(
            new Epoch('First Epoch', 1, null),
            new Epoch('Second Epoch', 3, 6),
        );
    }

    #[Test]
    public function itWillCreateEpochCollection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The last epoch must have an undefined end year');

        new EpochCollection(
            new Epoch('First Epoch', 1, 2),
            new Epoch('Second Epoch', 3, 4),
        );
    }

    #[Test]
    public function itWillCreateEpochCollectionWithValidEpochs(): void
    {
        $epochCollection = new EpochCollection(
            new Epoch('First Epoch', 1, 2),
            new Epoch('Second Epoch', 3, 4),
            new Epoch('Third Epoch', 5, null),
        );

        self::assertCount(3, $epochCollection->getEpochs());
    }

    #[Test]
    public function itWillReturnCorrectEpochForYear(): void
    {
        $epochCollection = new EpochCollection(
            new Epoch('First Epoch', 1, 2),
            new Epoch('Second Epoch', 3, 4),
            new Epoch('Third Epoch', 5, null),
        );

        self::assertSame('First Epoch', $epochCollection->getEpochForYear(1)->name);
        self::assertSame('First Epoch', $epochCollection->getEpochForYear(2)->name);
        self::assertSame('Second Epoch', $epochCollection->getEpochForYear(3)->name);
        self::assertSame('Second Epoch', $epochCollection->getEpochForYear(4)->name);
        self::assertSame('Third Epoch', $epochCollection->getEpochForYear(5)->name);
        self::assertSame('Third Epoch', $epochCollection->getEpochForYear(6)->name);
        self::assertSame('Third Epoch', $epochCollection->getEpochForYear(275)->name);
    }

    #[Test]
    public function testFromArray(): void
    {
        $epochsData = [
            ['name' => 'Second Age', 'startYear' => 1000],
            ['name' => 'First Age', 'startYear' => 1, 'endYear' => 999],
        ];

        $epochs     = EpochCollection::fromArray($epochsData);
        $collection = $epochs->getEpochs();

        self::assertCount(2, $collection);
        self::assertEquals('First Age', $collection[0]->name);
        self::assertEquals(1, $collection[0]->beginsInYear);
        self::assertEquals('Second Age', $collection[1]->name);
        self::assertEquals(1000, $collection[1]->beginsInYear);
    }
}
