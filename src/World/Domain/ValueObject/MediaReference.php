<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Domain\ValueObject;

interface MediaReference
{
    public function getType(): string;

    public function getIcon(): string;

    public function getMediaId(): string;

    public function getMediaTitle(): string;

    public function getMediaDisplayName(): string;

    public function getGenericLinkIdentifier(): string;
}
