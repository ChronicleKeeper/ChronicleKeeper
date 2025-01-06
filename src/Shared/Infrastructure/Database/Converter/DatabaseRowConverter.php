<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Converter;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

use function array_key_exists;

class DatabaseRowConverter
{
    /** @var array<class-string, RowConverter> */
    private array $rowConverters;

    /** @param iterable<RowConverter> $rowConverters */
    public function __construct(
        #[AutowireIterator('chronicle_keeper.row_converter')]
        iterable $rowConverters,
    ) {
        foreach ($rowConverters as $rowConverter) {
            $this->rowConverters[$rowConverter->getSupportedClass()] = $rowConverter;
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param class-string         $class
     *
     * @return array<string, mixed>
     */
    public function convert(array $data, string $class): array
    {
        if (! array_key_exists($class, $this->rowConverters)) {
            throw UnsupportedDataClass::withClass($class);
        }

        return $this->rowConverters[$class]->convert($data);
    }
}
