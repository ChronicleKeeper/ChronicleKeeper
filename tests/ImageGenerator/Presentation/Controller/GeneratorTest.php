<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Presentation\Controller;

use ChronicleKeeper\ImageGenerator\Presentation\Controller\Generator;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\ResetInterface;

use function assert;
use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(Generator::class)]
#[Large]
class GeneratorTest extends WebTestCase
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
    public function thatRequestingPageWithNonExistentDataIsFailing(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/image_generator/778e656f-2012-4e98-80fe-558539e57e98/generator',
        );

        self::assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function thatRequestingPageWithExistentDataIsSuccess(): void
    {
        $generatorRequest     = (new GeneratorRequestBuilder())->build();
        $generatorRequest->id = '6695cae4-ba8f-4d22-90e6-623675502817';

        $this->fileAccess->write(
            'generator.requests',
            $generatorRequest->id . '.json',
            json_encode($generatorRequest, JSON_THROW_ON_ERROR),
        );

        $this->client->request(
            Request::METHOD_GET,
            '/image_generator/6695cae4-ba8f-4d22-90e6-623675502817/generator',
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Mechthilds Atelier - Default Title');
    }
}
