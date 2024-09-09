<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Twig;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('loader')]
class LoaderComponent
{
    use DefaultActionTrait;
}
