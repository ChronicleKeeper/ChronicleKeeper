<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Presentation\Twig;

use ChronicleKeeper\ImageGenerator\Presentation\Twig\GeneratedImages;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

#[CoversClass(GeneratedImages::class)]
#[Large]
class GeneratedImagesTest extends WebTestCase
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
