<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Presentation\Controller;

use ChronicleKeeper\ImageGenerator\Presentation\Controller\Delete;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(Delete::class)]
#[Large]
class DeleteTest extends WebTestCase
{
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
        $this->databasePlatform->insert('generator_requests', [
            'id'       => 'c6f5d897-175c-4938-abe1-613fb51fdd68',
            'title'    => 'Foo Bar Baz',
            'userInput' => '{"foo": "bar"}',
        ]);

        $this->client->request(
            Request::METHOD_GET,
            '/image_generator/c6f5d897-175c-4938-abe1-613fb51fdd68/delete?confirm=1',
        );

        self::assertResponseRedirects('/image_generator');

        $existingEntries = $this->databasePlatform->fetch(
            'SELECT * FROM generator_requests WHERE id = :id',
            ['id' => 'c6f5d897-175c-4938-abe1-613fb51fdd68'],
        );
        self::assertEmpty($existingEntries);
    }
}
