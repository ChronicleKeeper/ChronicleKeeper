<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Presentation\Twig;

use ChronicleKeeper\ImageGenerator\Presentation\Twig\GeneratedImages;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

#[CoversClass(GeneratedImages::class)]
#[Large]
class GeneratedImagesTest extends KernelTestCase
{
    use InteractsWithLiveComponents;

    #[Test]
    public function componentIsRenderable(): void
    {
        $testComponent = $this->createLiveComponent(
            name: GeneratedImages::class,
            data: ['generatorRequest' => (new GeneratorRequestBuilder())->build()],
        );

        self::assertStringContainsString('Kunstgalerie', $testComponent->render()->toString());
    }
}
