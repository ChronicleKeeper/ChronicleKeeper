<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Service\ImportExport;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Application\Service\ImportExport\ConversationExporter;
use ChronicleKeeper\Settings\Application\Service\Exporter\ExportSettings;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBagBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\AssistantMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\SystemMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\UserMessageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;
use Symfony\Component\Serializer\SerializerInterface;
use ZipArchive;

#[CoversClass(ConversationExporter::class)]
#[Large]
class ConversationExporterTest extends DatabaseTestCase
{
    #[Test]
    public function itIsDoingNothingOnEmptyConversationFetch(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->never())->method('serialize');

        $export = new ConversationExporter($queryService, $serializer, $this->databasePlatform, new NullLogger());

        $export->export(self::createStub(ZipArchive::class), new ExportSettings('dev'));
    }

    #[Test]
    public function itIsAddingASerializedConversationToArchive(): void
    {
        // ------------------- The test setup -------------------
        $messages = (new ExtendedMessageBagBuilder())
            ->withMessages(
                (new ExtendedMessageBuilder())->withMessage((new SystemMessageBuilder())->build())->build(),
                (new ExtendedMessageBuilder())->withMessage((new UserMessageBuilder())->build())->build(),
                (new ExtendedMessageBuilder())->withMessage((new AssistantMessageBuilder())->build())->build(),
            )
            ->build();

        $conversation = (new ConversationBuilder())
            ->withId('06b90bed-97dc-42a2-bb66-10bef04ec881')
            ->withTitle('Test conversation')
            ->withMessages($messages)
            ->build();

        $this->bus->dispatch(new StoreConversation($conversation));

        // ------------------- Preparation of Mocks -----------------

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('serialize')
            ->willReturn('{"id":"06b90bed-97dc-42a2-bb66-10bef04ec881","name":"Test conversation"}');

        $archive = $this->createMock(ZipArchive::class);
        $archive->expects($this->once())
            ->method('addFromString')
            ->with(
                'library/conversations/06b90bed-97dc-42a2-bb66-10bef04ec881.json',
                '{"id":"06b90bed-97dc-42a2-bb66-10bef04ec881","name":"Test conversation"}',
            );

        // ------------------- The test execution -------------------

        $export = new ConversationExporter($this->queryService, $serializer, $this->databasePlatform, new NullLogger());
        $export->export($archive, new ExportSettings('dev'));
    }
}
