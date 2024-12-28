<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Form;

use ChronicleKeeper\Settings\Application\Service\SystemPromptRegistry;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use ChronicleKeeper\Settings\Presentation\Form\SystemPromptChoiceType;
use ChronicleKeeper\Test\Settings\Domain\Entity\SystemPromptBuilder;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

#[CoversClass(SystemPromptChoiceType::class)]
#[Small]
final class SystemPromptChoiceTypeTest extends TypeTestCase
{
    private SystemPromptRegistry&MockObject $systemPromptRegistry;

    #[Override]
    public function setUp(): void
    {
        $this->systemPromptRegistry = $this->createMock(SystemPromptRegistry::class);

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->systemPromptRegistry);

        parent::tearDown();
    }

    #[Test]
    public function itIsFilteringForThePurpose(): void
    {
        $imageUploadPrompt  = (new SystemPromptBuilder())->withPurpose(Purpose::IMAGE_UPLOAD)->build();
        $conversationPrompt = (new SystemPromptBuilder())->withPurpose(Purpose::CONVERSATION)->build();

        $this->systemPromptRegistry->expects($this->once())
            ->method('all')
            ->willReturn([$imageUploadPrompt, $conversationPrompt]);

        $form = $this->factory->create(
            SystemPromptChoiceType::class,
            null,
            ['for_purpose' => Purpose::IMAGE_UPLOAD],
        );

        $choices = $form->getConfig()->getOption('choices');
        self::assertSame([$imageUploadPrompt], $choices);
    }

    #[Test]
    public function itHasSortedChoices(): void
    {
        $imageUploadPrompt1 = (new SystemPromptBuilder())->asUser()->asDefault()->withPurpose(Purpose::IMAGE_UPLOAD)->build();
        $imageUploadPrompt2 = (new SystemPromptBuilder())->asSystem()->withPurpose(Purpose::IMAGE_UPLOAD)->build();
        $imageUploadPrompt3 = (new SystemPromptBuilder())->asUser()->withPurpose(Purpose::IMAGE_UPLOAD)->build();

        $this->systemPromptRegistry->expects($this->once())
            ->method('all')
            ->willReturn([$imageUploadPrompt3, $imageUploadPrompt1, $imageUploadPrompt2]);

        $form = $this->factory->create(
            SystemPromptChoiceType::class,
            null,
            ['for_purpose' => Purpose::IMAGE_UPLOAD],
        );

        $choices = $form->getConfig()->getOption('choices');
        self::assertSame([$imageUploadPrompt1, $imageUploadPrompt2, $imageUploadPrompt3], $choices);
    }

    /** @inheritDoc */
    #[Override]
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([new SystemPromptChoiceType($this->systemPromptRegistry)], []),
        ];
    }
}
