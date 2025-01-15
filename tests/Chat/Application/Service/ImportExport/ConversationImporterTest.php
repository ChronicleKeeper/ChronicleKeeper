<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Service\ImportExport;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Application\Service\ImportExport\ConversationImporter;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[CoversClass(ConversationImporter::class)]
#[Small]
class ConversationImporterTest extends TestCase
{
    #[Test]
    public function itIsImportingAConversation(): void
    {
        $databasePlatform = $this->createMock(DatabasePlatform::class);
        $databasePlatform->expects($this->once())->method('hasRows')->willReturn(false);

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

        $import = new ConversationImporter($databasePlatform, $denormalizer, $bus, new NullLogger());
        $import->import($filesystem, new ImportSettings());
    }
}
