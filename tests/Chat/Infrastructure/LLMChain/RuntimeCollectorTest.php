<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\LLMChain;

use ChronicleKeeper\Chat\Domain\ValueObject\Reference;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[CoversClass(RuntimeCollector::class)]
#[Small]
class RuntimeCollectorTest extends TestCase
{
    #[Test]
    public function aReferenceCanBeAdded(): void
    {
        $collector = new RuntimeCollector();
        $collector->addReference(new Reference('id', 'type', 'title'));

        self::assertCount(1, (new ReflectionProperty($collector, 'references'))->getValue($collector));
    }

    #[Test]
    public function isCanFetchReferencesByType(): void
    {
        $collector = new RuntimeCollector();
        $collector->addReference(new Reference('id1', 'foo', 'title'));
        $collector->addReference(new Reference('id2', 'bar', 'title'));
        $collector->addReference(new Reference('id3', 'type', 'title'));

        $references = $collector->flushReferenceByType('type');

        self::assertCount(2, (new ReflectionProperty($collector, 'references'))->getValue($collector));
        self::assertCount(1, $references);
    }

    #[Test]
    public function isCanReset(): void
    {
        $collector = new RuntimeCollector();
        $collector->addReference(new Reference('id1', 'foo', 'title'));

        $collector->reset();

        self::assertEmpty((new ReflectionProperty($collector, 'references'))->getValue($collector));
    }
}
