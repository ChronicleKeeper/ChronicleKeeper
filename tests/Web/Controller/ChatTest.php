<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Test\Web\Controller;

use DZunke\NovDoc\Web\Controller\Chat;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ChatTest extends TestCase
{
    public function testReturnsAValidResponse(): void
    {
        self::assertInstanceOf(Response::class, (new Chat(self::createStub(Environment::class)))());
    }
}
