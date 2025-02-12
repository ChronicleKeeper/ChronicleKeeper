<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Service\ImportExport;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Application\Service\ImportExport\ConversationImporter;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[CoversClass(ConversationImporter::class)]
#[Large]
class ConversationImporterTest extends DatabaseTestCase
{
    #[Test]
    public function itIsImportingAConversation(): void
    {
        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->once())
            ->method('denormalize')
            ->willReturn((new ConversationBuilder())->build());

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(StoreConversation::class))
            ->willReturn(new Envelope(new stdClass()));

        $fileAttributes = self::createStub(FileAttributes::class);
        $fileAttributes->method('path')->willReturn('library/conversations/06b90bed-97dc-42a2-bb66-10bef04ec881.json');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('listContents')
            ->with('library/conversations/')
            ->willReturn(new DirectoryListing([$fileAttributes]));
        $filesystem->expects($this->once())
            ->method('read')
            ->with('library/conversations/06b90bed-97dc-42a2-bb66-10bef04ec881.json')
            ->willReturn('{"appVersion": "dev", "type": "conversation", "data": {"id": "06b90bed-97dc-42a2-bb66-10bef04ec881"}}');

        $import = new ConversationImporter($this->databasePlatform, $denormalizer, $bus, new NullLogger());
        $import->import($filesystem, new ImportSettings());
    }
}
