<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Application\Query\FindAllGeneratorRequests;
use ChronicleKeeper\ImageGenerator\Application\Query\FindAllGeneratorRequestsQuery;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[CoversClass(FindAllGeneratorRequestsQuery::class)]
#[CoversClass(FindAllGeneratorRequests::class)]
#[Small]
class FindAllGeneratorRequestsQueryTest extends TestCase
{
    #[Test]
    public function correctQueryClassIsliked(): void
    {
        self::assertSame(
            FindAllGeneratorRequestsQuery::class,
            (new FindAllGeneratorRequests())->getQueryClass(),
        );
    }

    #[Test]
    public function isDeliveringFoundGeneratorRequests(): void
    {
        $databasePlatform = $this->createMock(DatabasePlatform::class);
        $databasePlatform->expects($this->once())
            ->method('fetch')
            ->with('SELECT * FROM generator_requests ORDER BY title')
            ->willReturn([
                ['title' => 'foo', 'userInput' => '{"foo": true}'],
                ['title' => 'bar', 'userInput' => '{"bar": true}'],
            ]);

        $denormalizer        = $this->createMock(DenormalizerInterface::class);
        $denormalizerInvoker = $this->exactly(2);
        $denormalizer
            ->expects($denormalizerInvoker)
            ->method('denormalize')
            ->willReturnCallback(
                static function (array $data, string $target) use ($denormalizerInvoker): GeneratorRequest {
                    self::assertSame(GeneratorRequest::class, $target);

                    if ($denormalizerInvoker->numberOfInvocations() === 1) {
                        self::assertSame('foo', $data['title']);
                        self::assertSame(['foo' => true], $data['userInput']);

                        return (new GeneratorRequestBuilder())->withTitle('foo')->build();
                    }

                    self::assertSame('bar', $data['title']);
                    self::assertSame(['bar' => true], $data['userInput']);

                    return (new GeneratorRequestBuilder())->withTitle('bar')->build();
                },
            );

        $response = (new FindAllGeneratorRequestsQuery($denormalizer, $databasePlatform))
            ->query(new FindAllGeneratorRequests());

        self::assertCount(2, $response);
    }
}
