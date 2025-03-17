<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Presentation\Twig\Helper;

use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\Twig\Helper\PHPEnums;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(PHPEnums::class)]
#[Small]
final class PHPEnumsTest extends TestCase
{
    private PHPEnums $phpEnums;

    protected function setUp(): void
    {
        $this->phpEnums = new PHPEnums();
    }

    protected function tearDown(): void
    {
        unset($this->phpEnums);
    }

    #[Test]
    public function itRegisteresTheCorrectFilters(): void
    {
        $filters = $this->phpEnums->getFilters();

        self::assertCount(1, $filters);
        self::assertSame('enum', $filters[0]->getName());
    }

    #[Test]
    public function itWillCreateAnEnumFromAString(): void
    {
        $enum = $this->phpEnums->createEnum('warning', Alert::class);

        self::assertInstanceOf(Alert::class, $enum);
        self::assertSame(Alert::WARNING, $enum);
    }

    #[Test]
    public function itWillFailCreatingAnEnumWithAnInvalidValue(): void
    {
        $result = $this->phpEnums->createEnum(
            'invalid',
            Alert::class,
        );

        self::assertNull($result);
    }

    #[Test]
    public function itWillNotCreateAnEnumWithAnInvalidEnumClass(): void
    {
        $result = $this->phpEnums->createEnum(
            'value',
            'NonExistentEnumClass', // @phpstan-ignore argument.type
        );

        self::assertNull($result);
    }

    #[Test]
    public function itWillNotCreateAnEnumWithAnNotBackedEnumClass(): void
    {
        $result = $this->phpEnums->createEnum(
            'value',
            stdClass::class, // @phpstan-ignore argument.type
        );

        self::assertNull($result);
    }
}
