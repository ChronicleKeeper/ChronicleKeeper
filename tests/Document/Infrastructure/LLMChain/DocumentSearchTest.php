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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
            [
                'document' => (new DocumentBuilder())->withContent('First Content')->build(),
                'content' => 'First Content',
                'distance' => 0.1,
            ],
            [
                'document' => (new DocumentBuilder())->withContent('Second Content')->build(),
                'content' => 'Second Content',
                'distance' => 0.2,
            ],
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

        $routeGenerator = $this->createMock(UrlGeneratorInterface::class);
        $routeGenerator->expects($this->exactly(2))
            ->method('generate')
            ->willReturn('http://localhost/document/id');

        $documentSearch = new DocumentSearch(
            $embeddingCalculatoir,
            $settingsHanlder,
            $queryService,
            $runtimeCollector,
            $routeGenerator,
        );

        self::assertSame(
            <<<'TEXT'
            I have found the following information that are associated to the question:
            # Title: Default Title
            - Storage Directory: Hauptverzeichnis
            - Url: http://localhost/document/id

            First Content

            # Title: Default Title
            - Storage Directory: Hauptverzeichnis
            - Url: http://localhost/document/id

            Second Content


            TEXT,
            $documentSearch('I am searching for documents'),
        );
    }

    #[Test]
    public function isIsMergingFoundVectorsToDocuments(): void
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

        $foundDocument = (new DocumentBuilder())->withContent('Only Content')->build();

        $foundDocuments = [
            ['document' => $foundDocument, 'content' => 'First Content', 'distance' => 0.1],
            ['document' => $foundDocument, 'content' => 'Second Content', 'distance' => 0.2],
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
        $runtimeCollector->expects($this->once())->method('addReference');
        $runtimeCollector->expects($this->once())
            ->method('addFunctionDebug')
            ->with(self::isInstanceOf(FunctionDebug::class));

        $routeGenerator = $this->createMock(UrlGeneratorInterface::class);
        $routeGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('http://localhost/document/id');

        $documentSearch = new DocumentSearch(
            $embeddingCalculatoir,
            $settingsHanlder,
            $queryService,
            $runtimeCollector,
            $routeGenerator,
        );

        self::assertSame(
            <<<'TEXT'
            I have found the following information that are associated to the question:
            # Title: Default Title
            - Storage Directory: Hauptverzeichnis
            - Url: http://localhost/document/id

            Only Content


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

        $routeGenerator = $this->createMock(UrlGeneratorInterface::class);
        $routeGenerator->expects($this->never())->method('generate');

        $documentSearch = new DocumentSearch(
            $embeddingCalculatoir,
            $settingsHanlder,
            $queryService,
            $runtimeCollector,
            $routeGenerator,
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

                return [
                    [
                        'document' => (new DocumentBuilder())->withContent('My Content')->build(),
                        'content' => 'My Content',
                        'distance' => 0.1,
                    ],
                ];
            });

        $runtimeCollector = $this->createMock(RuntimeCollector::class);
        $runtimeCollector->expects($this->once())->method('addReference');
        $runtimeCollector->expects($this->once())
            ->method('addFunctionDebug')
            ->with(self::isInstanceOf(FunctionDebug::class));

        $routeGenerator = $this->createMock(UrlGeneratorInterface::class);
        $routeGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('http://localhost/document/id');

        $documentSearch = new DocumentSearch(
            $embeddingCalculatoir,
            $settingsHanlder,
            $queryService,
            $runtimeCollector,
            $routeGenerator,
        );
        $documentSearch->setOneTimeMaxDistance(0.15);

        self::assertSame(
            <<<'TEXT'
            I have found the following information that are associated to the question:
            # Title: Default Title
            - Storage Directory: Hauptverzeichnis
            - Url: http://localhost/document/id

            My Content


            TEXT,
            $documentSearch('I am searching for documents'),
        );
    }
}
