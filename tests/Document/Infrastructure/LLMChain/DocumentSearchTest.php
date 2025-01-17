<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Infrastructure\LLMChain;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Document\Application\Query\SearchSimilarVectors;
use ChronicleKeeper\Document\Infrastructure\LLMChain\DocumentSearch;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotGeneral;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotTuning;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentSearch::class)]
#[Small]
class DocumentSearchTest extends TestCase
{
    #[Test]
    public function itIsAbleToSearchForDocuments(): void
    {
        $settings = (new SettingsBuilder())
            ->withChatbotGeneral(new ChatbotGeneral(maxDocumentResponses: 20))
            ->withChatbotTuning(new ChatbotTuning(documentsMaxDistance: 0.85))
            ->build();

        $settingsHanlder = $this->createMock(SettingsHandler::class);
        $settingsHanlder->expects($this->once())->method('get')->willReturn($settings);

        $embeddingCalculatoir = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculatoir->expects($this->once())
            ->method('getSingleEmbedding')
            ->with('I am searching for documents')
            ->willReturn([0.1, 0.2, 0.3]);

        $foundDocuments = [
            ['document' => (new DocumentBuilder())->build(), 'content' => 'First Content', 'distance' => 0.1],
            ['document' => (new DocumentBuilder())->build(), 'content' => 'Second Content', 'distance' => 0.2],
        ];

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::isInstanceOf(SearchSimilarVectors::class))
            ->willReturnCallback(static function (SearchSimilarVectors $query) use ($foundDocuments) {
                self::assertSame([0.1, 0.2, 0.3], $query->searchedVectors);

                self::assertSame(0.85, $query->maxDistance);
                self::assertSame(20, $query->maxResults);

                return $foundDocuments;
            });

        $runtimeCollector = $this->createMock(RuntimeCollector::class);
        $runtimeCollector->expects($this->exactly(2))->method('addReference');
        $runtimeCollector->expects($this->once())
            ->method('addFunctionDebug')
            ->with(self::isInstanceOf(FunctionDebug::class));

        $documentSearch = new DocumentSearch(
            $embeddingCalculatoir,
            $settingsHanlder,
            $queryService,
            $runtimeCollector,
        );

        self::assertSame(
            <<<'TEXT'
            I have found the following information that are associated to the question:
            # Title: Default Title
            Storage Directory: Hauptverzeichnis
            First Content

            # Title: Default Title
            Storage Directory: Hauptverzeichnis
            Second Content


            TEXT,
            $documentSearch('I am searching for documents'),
        );
    }

    #[Test]
    public function itIsLoggingAnEmptyResult(): void
    {
        $settings = (new SettingsBuilder())->build();

        $settingsHanlder = $this->createMock(SettingsHandler::class);
        $settingsHanlder->expects($this->once())->method('get')->willReturn($settings);

        $embeddingCalculatoir = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculatoir->expects($this->once())
            ->method('getSingleEmbedding')
            ->with('I am searching for documents')
            ->willReturn([0.1, 0.2, 0.3]);

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::isInstanceOf(SearchSimilarVectors::class))
            ->willReturn([]);

        $runtimeCollector = $this->createMock(RuntimeCollector::class);
        $runtimeCollector->expects($this->never())->method('addReference');

        $documentSearch = new DocumentSearch(
            $embeddingCalculatoir,
            $settingsHanlder,
            $queryService,
            $runtimeCollector,
        );

        $response = $documentSearch('I am searching for documents');

        self::assertSame('There are no matching documents.', $response);
    }

    #[Test]
    public function itIsAbleToSetOneTimeMaxDistanceForSearch(): void
    {
        $settings = (new SettingsBuilder())
            ->withChatbotGeneral(new ChatbotGeneral(maxDocumentResponses: 20))
            ->withChatbotTuning(new ChatbotTuning(documentsMaxDistance: 0.85))
            ->build();

        $settingsHanlder = $this->createMock(SettingsHandler::class);
        $settingsHanlder->expects($this->once())->method('get')->willReturn($settings);

        $embeddingCalculatoir = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculatoir->expects($this->once())
            ->method('getSingleEmbedding')
            ->with('I am searching for documents')
            ->willReturn([0.1, 0.2, 0.3]);

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::isInstanceOf(SearchSimilarVectors::class))
            ->willReturnCallback(static function (SearchSimilarVectors $query) {
                self::assertSame([0.1, 0.2, 0.3], $query->searchedVectors);

                self::assertSame(0.15, $query->maxDistance);
                self::assertSame(20, $query->maxResults);

                return [['document' => (new DocumentBuilder())->build(), 'content' => 'My Content', 'distance' => 0.1]];
            });

        $runtimeCollector = $this->createMock(RuntimeCollector::class);
        $runtimeCollector->expects($this->once())->method('addReference');
        $runtimeCollector->expects($this->once())
            ->method('addFunctionDebug')
            ->with(self::isInstanceOf(FunctionDebug::class));

        $documentSearch = new DocumentSearch(
            $embeddingCalculatoir,
            $settingsHanlder,
            $queryService,
            $runtimeCollector,
        );
        $documentSearch->setOneTimeMaxDistance(0.15);

        self::assertSame(
            <<<'TEXT'
            I have found the following information that are associated to the question:
            # Title: Default Title
            Storage Directory: Hauptverzeichnis
            My Content


            TEXT,
            $documentSearch('I am searching for documents'),
        );
    }
}
