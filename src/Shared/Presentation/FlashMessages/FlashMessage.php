<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\FlashMessages;

class FlashMessage
{
    public function __construct(
        public readonly Alert $type,
        public readonly string $content,
    ) {
    }
}
