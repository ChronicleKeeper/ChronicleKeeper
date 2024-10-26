<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Presentation\Form;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\ImageGenerator\Presentation\Form\GeneratorRequestType;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

#[CoversClass(GeneratorRequestType::class)]
#[Small]
class GeneratorRequestTypeTest extends TypeTestCase
{
    #[Test]
    public function formHandlingForNewEntry(): void
    {
        $form = $this->factory->create(GeneratorRequestType::class);

        $form->submit([
            'title' => 'foo',
            'userInput' => 'bar',
            'prompt' => 'baz',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());

        $data = $form->getData();
        self::assertInstanceOf(GeneratorRequest::class, $data);
        self::assertSame('foo', $data->title);
        self::assertSame('bar', $data->userInput->prompt);
        self::assertSame('baz', $data->prompt?->prompt);
    }

    #[Test]
    public function formHandlingForExistingEntry(): void
    {
        $generatorRequest = (new GeneratorRequestBuilder())->build();
        $form             = $this->factory->create(GeneratorRequestType::class, $generatorRequest);

        $form->submit([
            'title' => 'foo',
            'userInput' => 'bar',
            'prompt' => 'baz',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());

        $data = $form->getData();
        self::assertInstanceOf(GeneratorRequest::class, $data);
        self::assertSame($generatorRequest->id, $data->id);
        self::assertSame('foo', $data->title);
        self::assertSame('bar', $data->userInput->prompt);
        self::assertSame('baz', $data->prompt?->prompt);
    }

    #[Test]
    public function formHandlingForInvalidData(): void
    {
        $form = $this->factory->create(GeneratorRequestType::class);

        $form->submit([
            'title' => '',
            'userInput' => '',
            'prompt' => '',
        ]);

        self::assertFalse($form->isValid());
        self::assertFalse($form->get('title')->isValid());
        self::assertFalse($form->get('userInput')->isValid());
        self::assertTrue($form->get('prompt')->isValid());

        $titleErrors     = $form->get('title')->getErrors();
        $userInputErrors = $form->get('userInput')->getErrors();

        self::assertCount(1, $titleErrors);
        self::assertSame('This value should not be blank.', $titleErrors[0]->getMessage());

        self::assertCount(1, $userInputErrors);
        self::assertSame('This value should not be blank.', $userInputErrors[0]->getMessage());
    }

    /** @return list<FormExtensionInterface> */
    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
        ];
    }
}
