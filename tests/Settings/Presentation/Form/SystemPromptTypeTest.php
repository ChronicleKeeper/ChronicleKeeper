<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Form;

use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use ChronicleKeeper\Settings\Presentation\Form\SystemPromptType;
use ChronicleKeeper\Test\Settings\Domain\Entity\SystemPromptBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Form\Test\TypeTestCase;

#[CoversClass(SystemPromptType::class)]
#[Small]
final class SystemPromptTypeTest extends TypeTestCase
{
    #[Test]
    public function itCanTakeValidDataToCreateNewPrompt(): void
    {
        $formData = [
            'name' => 'Test Prompt',
            'purpose' => Purpose::IMAGE_GENERATOR_OPTIMIZER->value,
            'content' => 'Test Content',
            'isDefault' => true,
        ];

        $form = $this->factory->create(SystemPromptType::class);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());

        $createdSystemPrompt = $form->getData();
        self::assertSame($formData['name'], $createdSystemPrompt->getName());
        self::assertSame($formData['purpose'], $createdSystemPrompt->getPurpose()->value);
        self::assertSame($formData['content'], $createdSystemPrompt->getContent());
        self::assertSame($formData['isDefault'], $createdSystemPrompt->isDefault());
        self::assertFalse($createdSystemPrompt->isSystem());
    }

    #[Test]
    public function itWillModifyAnExistingPrompt(): void
    {
        $prompt   = (new SystemPromptBuilder())->asDefault()->build();
        $formData = [
            'name' => 'Edited Test Prompt',
            'purpose' => Purpose::IMAGE_GENERATOR_OPTIMIZER->value,
            'content' => 'Edited Test Content',
            'isDefault' => false,
        ];

        $form = $this->factory->create(SystemPromptType::class, $prompt);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());

        $createdSystemPrompt = $form->getData();
        self::assertSame($formData['name'], $createdSystemPrompt->getName());
        self::assertSame($formData['purpose'], $createdSystemPrompt->getPurpose()->value);
        self::assertSame($formData['content'], $createdSystemPrompt->getContent());
        self::assertSame($formData['isDefault'], $createdSystemPrompt->isDefault());
        self::assertFalse($createdSystemPrompt->isSystem());
    }
}
