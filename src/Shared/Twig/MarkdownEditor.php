<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Twig;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('markdownEditor')]
class MarkdownEditor
{
    public string $name;
    public int $rows       = 10;
    public string $content = '';
}
