<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

interface Day
{
    public function getDayOfTheMonth(): int;

    public function getLabel(): string;
}
