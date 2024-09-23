<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Library\Presentation\Twig;

use DZunke\NovDoc\Library\Domain\Entity\Document;
use DZunke\NovDoc\Library\Domain\Entity\Image;
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
