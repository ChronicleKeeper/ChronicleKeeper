<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Presentation\Twig;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Favorizer:HeaderShortcuts', 'components/favorizer/header_shortcuts.html.twig')]
class HeaderShortcuts
{
    public function getShortcuts(): array
    {
        return [];
    }
}
