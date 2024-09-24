<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Twig;

use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\Entity\Image;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class TestExtension extends AbstractExtension
{
    /** @inheritDoc+ */
    public function getTests(): array
    {
        return [
            new TwigTest('image', [$this, 'isImage']),
            new TwigTest('document', [$this, 'isDocument']),
        ];
    }

    public function isImage(object $var): bool
    {
        return $var instanceof Image;
    }

    public function isDocument(object $var): bool
    {
        return $var instanceof Document;
    }
}
