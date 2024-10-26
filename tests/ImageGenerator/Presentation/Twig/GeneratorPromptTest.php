<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Presentation\Twig;

use ChronicleKeeper\ImageGenerator\Presentation\Twig\GeneratorPrompt;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

#[CoversClass(GeneratorPrompt::class)]
#[Large]
class GeneratorPromptTest extends KernelTestCase
{
    use InteractsWithLiveComponents;

    #[Test]
    public function componentIsRenderable(): void
    {
        $testComponent = $this->createLiveComponent(
            name: GeneratorPrompt::class,
            data: ['initialFormData' => (new GeneratorRequestBuilder())->build()],
        );

        self::assertStringContainsString('Details zum künstlerischen Auftrag', $testComponent->render()->toString());
    }
}
