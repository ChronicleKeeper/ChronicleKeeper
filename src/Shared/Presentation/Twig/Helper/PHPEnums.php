<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\Twig\Helper;

use BackedEnum;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function enum_exists;

class PHPEnums extends AbstractExtension
{
    /** @return TwigFilter[] */
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('enum', $this->createEnum(...)),
        ];
    }

    /**
     * Creates an enum instance from a string value
     *
     * @param string                   $value     The string value to convert
     * @param class-string<BackedEnum> $enumClass The fully qualified enum class name
     *
     * @return BackedEnum|null The corresponding enum instance or null if not found
     */
    public function createEnum(string $value, string $enumClass): BackedEnum|null
    {
        if (! enum_exists($enumClass)) {
            return null;
        }

        return $enumClass::tryFrom($value);
    }
}
