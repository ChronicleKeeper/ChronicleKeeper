<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Service\ImportExport;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Service\ImportExport\ConversationExporter;
use ChronicleKeeper\Settings\Application\Service\Exporter\ExportSettings;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Serializer\SerializerInterface;
use ZipArchive;

#[CoversClass(ConversationExporter::class)]
#[Small]
class ConversationExporterTest extends TestCase
{
    #[Test]
    public function itIsDoingNothingOnEmptyConversationFetch(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->never())->method('serialize');

        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectFetch('SELECT id FROM conversations', [], []);

        $export = new ConversationExporter($queryService, $serializer, $databasePlatform, new NullLogger());
        $export->export(self::createStub(ZipArchive::class), new ExportSettings('dev'));
    }

    #[Test]
    public function itIsAddingASerializedConversationToArchive(): void
    {
        $conversation = (new ConversationBuilder())->withId('06b90bed-97dc-42a2-bb66-10bef04ec881')->build();

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::isInstanceOf(FindConversationByIdParameters::class))
            ->willReturn($conversation);

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('serialize')
            ->willReturn('{"id":"06b90bed-97dc-42a2-bb66-10bef04ec881","name":"Test"}');

        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectFetch('SELECT id FROM conversations', [], [['id' => '06b90bed-97dc-42a2-bb66-10bef04ec881']]);

        $archive = $this->createMock(ZipArchive::class);
        $archive->expects($this->once())
            ->method('addFromString')
            ->with(
                'library/conversations/06b90bed-97dc-42a2-bb66-10bef04ec881.json',
                '{"id":"06b90bed-97dc-42a2-bb66-10bef04ec881","name":"Test"}',
            );

        $export = new ConversationExporter($queryService, $serializer, $databasePlatform, new NullLogger());
        $export->export($archive, new ExportSettings('dev'));
    }
}
