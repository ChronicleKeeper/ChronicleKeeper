<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\Twig\Form;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    name: 'Form:FotterButtonGroup',
    template: 'components/shared/form/footer-button-group.html.twig',
)]
class FooterButtonGroup
{
    public string|null $cancelLink    = null;
    public bool $showRedirectToCreate = true;
    public bool $showRedirectToView   = true;
}
