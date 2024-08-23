<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\FlashMessages;

class FlashMessage
{
    public function __construct(
        public readonly Alert $type,
        public readonly string $content,
    ) {
    }
}
