<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Presentation\Command;

use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use ChronicleKeeper\World\Presentation\Command\DebugItemRelations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

use function assert;

#[CoversClass(DebugItemRelations::class)]
#[Large]
class DebugItemRelationsTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function itIsExecutable(): void
    {
        $kernel = self::$kernel;
        assert($kernel instanceof KernelInterface);

        $application = new Application($kernel);
        $application->add(new DebugItemRelations());

        $command       = $application->find('debug:world:item-relations');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            // pass arguments to the command
            'item_types' => [ItemType::COUNTRY->value],
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('World Debugging of World Item Type Relations', $output);
        self::assertStringContainsString('Checking the following types: ' . ItemType::COUNTRY->value, $output);
        self::assertStringContainsString('disputed', $output);

        // assert the command status code
        self::assertSame(0, $commandTester->getStatusCode());
    }

    #[Test]
    public function itWillFailOnAnInvalidType(): void
    {
        $kernel = self::$kernel;
        assert($kernel instanceof KernelInterface);

        $application = new Application($kernel);
        $application->add(new DebugItemRelations());

        $command       = $application->find('debug:world:item-relations');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            // pass arguments to the command
            'item_types' => ['INVALID_TYPE'],
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Invalid item type provided.', $output);

        // assert the command status code
        self::assertSame(1, $commandTester->getStatusCode());
    }
}
