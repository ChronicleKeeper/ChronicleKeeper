<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Converter;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('chronicle_keeper.row_converter')]
interface RowConverter
{
    /** @return class-string */
    public function getSupportedClass(): string;

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function convert(array $data): array;
}
