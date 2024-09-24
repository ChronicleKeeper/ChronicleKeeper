<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\VectorStorage\Distance;

use InvalidArgumentException;

use function array_map;
use function array_sum;
use function count;
use function sqrt;

class CosineDistance
{
    /**
     * @param list<float> $vector1
     * @param list<float> $vector2
     */
    public function measure(array $vector1, array $vector2): float
    {
        if (count($vector1) !== count($vector2)) {
            throw new InvalidArgumentException('Arrays must have the same length.');
        }

        $dotProduct = array_sum(array_map(static fn (float $a, float $b): float => $a * $b, $vector1, $vector2));

        $magnitude1 = sqrt(array_sum(array_map(static fn (float $a): float => $a * $a, $vector1)));
        $magnitude2 = sqrt(array_sum(array_map(static fn (float $a): float => $a * $a, $vector2)));

        if ($magnitude1 * $magnitude2 === 0.0) {
            return 0;
        }

        return 1 - $dotProduct / ($magnitude1 * $magnitude2);
    }
}
