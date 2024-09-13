<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Twig;

use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('milkdown')]
class MilkdownComponent
{
    use DefaultActionTrait;

    public string $name;
    public int $rows       = 10;
    public string $content = '';
}
