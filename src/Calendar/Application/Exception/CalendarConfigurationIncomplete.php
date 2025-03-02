<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Application\Exception;

use RuntimeException;

use function implode;

final class CalendarConfigurationIncomplete extends RuntimeException
{
    /** @param list<string> $missingSettings */
    public function __construct(
        public readonly array $missingSettings,
    ) {
        parent::__construct('Missing calendar settings: ' . implode(', ', $missingSettings));
    }
}
