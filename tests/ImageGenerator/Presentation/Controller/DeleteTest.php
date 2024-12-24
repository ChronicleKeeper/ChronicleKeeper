<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Presentation\Controller;

use ChronicleKeeper\ImageGenerator\Presentation\Controller\Delete;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\ResetInterface;

use function assert;

#[CoversClass(Delete::class)]
#[Large]
class DeleteTest extends WebTestCase
{
    private KernelBrowser $client;
    private FileAccess&ResetInterface $fileAccess;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $fileAccess = $this->client->getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccess);
        assert($fileAccess instanceof ResetInterface);

        $this->fileAccess = $fileAccess;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->fileAccess->reset();

        unset($this->client, $this->fileAccess);
    }

    #[Test]
    public function thatRequestingPageWithoutConfirmFails(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/image_generator/778e656f-2012-4e98-80fe-558539e57e98/delete',
        );

        self::assertResponseRedirects('/image_generator');
    }

    #[Test]
    public function thatRequestingPageWithNonExistentDataIsSuccess(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/image_generator/778e656f-2012-4e98-80fe-558539e57e98/delete?confirm=1',
        );

        self::assertResponseRedirects('/image_generator');
    }

    #[Test]
    public function thatRequestingPageWithExistentDataIsSuccess(): void
    {
        $this->fileAccess->write(
            'generator.requests',
            'c6f5d897-175c-4938-abe1-613fb51fdd68.json',
            'Foo Bar Baz',
        );

        $this->client->request(
            Request::METHOD_GET,
            '/image_generator/c6f5d897-175c-4938-abe1-613fb51fdd68/delete?confirm=1',
        );

        self::assertResponseRedirects('/image_generator');
        self::assertFalse($this->fileAccess->exists(
            'generator.requests',
            'c6f5d897-175c-4938-abe1-613fb51fdd68.json',
        ));
    }
}
