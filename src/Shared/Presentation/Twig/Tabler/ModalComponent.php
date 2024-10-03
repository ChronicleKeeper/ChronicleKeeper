<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\Twig\Tabler;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Tabler:Modal', template: 'components/shared/tabler/modal.html.twig')]
class ModalComponent
{
    public string|null $id            = null;
    public bool $showClosableInHeader = true;
}
