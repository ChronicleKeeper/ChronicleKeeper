<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Command\DeleteGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Command\DeleteGeneratorRequestHandler;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(DeleteGeneratorRequestHandler::class)]
#[CoversClass(DeleteGeneratorRequest::class)]
#[Small]
class DeleteGeneratorRequestHandlerTest extends TestCase
{
    #[Test]
    public function instantiationOfCommandFailsWithNonUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "invalid-uuid" is not a valid UUID.');

        new DeleteGeneratorRequest('invalid-uuid');
    }

    #[Test]
    public function deletionWorksAsExcpected(): void
    {
        $uuidToDelete     = '30da9c8d-7c80-4404-b8d2-5fa196e9548c';
        $databasePlatform = $this->createMock(DatabasePlatform::class);

        $invoker = $this->exactly(2);
        $databasePlatform->expects($invoker)
            ->method('query')
            ->willReturnCallback(
                static function (string $query, array $parameters) use ($invoker, $uuidToDelete): void {
                    if ($invoker->numberOfInvocations() === 1) {
                        self::assertSame('DELETE FROM generator_results WHERE generatorRequest = :id', $query);
                        self::assertSame(['id' => $uuidToDelete], $parameters);

                        return;
                    }

                    self::assertSame('DELETE FROM generator_requests WHERE id = :id', $query);
                    self::assertSame(['id' => $uuidToDelete], $parameters);
                },
            );

        $handler = new DeleteGeneratorRequestHandler($databasePlatform);
        $handler(new DeleteGeneratorRequest($uuidToDelete));
    }
}
