<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\LLMChain\Exception;

use RuntimeException;
use Throwable;

final class EmbeddingCalculationFailed extends RuntimeException
{
    public function __construct(
        Throwable|null $previous = null,
    ) {
        parent::__construct(
            message: 'Embedding calculation has failed for some text chunks.',
            previous: $previous,
        );
    }
}
