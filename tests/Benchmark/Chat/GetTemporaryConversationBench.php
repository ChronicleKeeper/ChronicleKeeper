<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Benchmark\Chat;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Test\Benchmark\UseSymfonyKernel;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

use function assert;
use function file_get_contents;

class GetTemporaryConversationBench
{
    use UseSymfonyKernel;

    private Serializer $serializer;

    public function setUp(): void
    {
        $kernel = $this->getKernel();

        $serializer = $kernel->getContainer()->get(Serializer::class);
        assert($serializer instanceof Serializer);
        $this->serializer = $serializer;
    }

    /** @BeforeMethods("setUp") */
    public function benchLoadTemporaryLargeFile(): void
    {
        $this->serializer->deserialize(
            file_get_contents(__DIR__ . '/Stub/false_conversation_temporary.json'),
            Conversation::class,
            JsonEncoder::FORMAT,
        );
    }

    /** @BeforeMethods("setUp") */
    public function benchLoadOptimized(): void
    {
        $this->serializer->deserialize(
            file_get_contents(__DIR__ . '/Stub/clean_conversation_temporary.json'),
            Conversation::class,
            JsonEncoder::FORMAT,
        );
    }
}
