<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\LLMChain;

use ChronicleKeeper\Chat\Domain\ValueObject\Reference;

use function array_filter;
use function array_values;

class RuntimeCollector
{
    /** @var list<Reference> */
    private array $references = [];

    public function addReference(Reference $reference): void
    {
        $this->references[] = $reference;
    }

    /** @return list<Reference> */
    public function flushReferenceByType(string $type): array
    {
        $references = array_filter(
            $this->references,
            static fn (Reference $reference) => $reference->type === $type,
        );

        $this->references = array_values(array_filter(
            $this->references,
            static fn (Reference $reference) => $reference->type !== $type,
        ));

        return array_values($references);
    }

    public function reset(): void
    {
        $this->references = [];
    }
}
