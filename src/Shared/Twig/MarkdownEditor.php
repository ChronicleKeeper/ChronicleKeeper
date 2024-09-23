<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Shared\Twig;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('markdownEditor')]
class MarkdownEditor
{
    public string $name;
    public int $rows       = 10;
    public string $content = '';
}
