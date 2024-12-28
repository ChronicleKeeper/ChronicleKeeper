<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Presentation\Form;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\UserInput;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use ChronicleKeeper\Settings\Presentation\Form\SystemPromptChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Throwable;
use Traversable;

use function is_string;
use function iterator_to_array;

class GeneratorRequestType extends AbstractType implements DataMapperInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', GeneratorRequest::class);
        $resolver->setDefault('empty_data', static fn () => new GeneratorRequest('', new UserInput('')));
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setDataMapper($this);

        $builder->add(
            'title',
            TextType::class,
            [
                'label' => 'Titel',
                'translation_domain' => false,
                'required' => true,
                'constraints' => [new NotBlank()],
            ],
        );

        $builder->add(
            'userInput',
            TextareaType::class,
            [
                'label' => 'Beschreibung des Auftrages',
                'translation_domain' => false,
                'required' => true,
                'constraints' => [new NotBlank()],
            ],
        );

        $builder->add(
            'systemPrompt',
            SystemPromptChoiceType::class,
            [
                'for_purpose' => Purpose::IMAGE_GENERATOR_OPTIMIZER,
                'label' => 'Template für die Ausarbeitung des Auftrages',
            ],
        );

        $builder->add(
            'prompt',
            TextareaType::class,
            [
                'label' => 'Detailbeschreibung des Auftrages',
                'translation_domain' => false,
                'required' => true,
            ],
        );
    }

    /** @param Traversable<FormInterface> $forms */
    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            return;
        }

        if (! $viewData instanceof GeneratorRequest) {
            throw new UnexpectedTypeException($viewData, GeneratorRequest::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $forms['title']->setData($viewData->title);
        $forms['userInput']->setData($viewData->userInput->prompt);
        $forms['prompt']->setData((string) $viewData->prompt?->prompt);
    }

    /** @param Traversable<FormInterface> $forms */
    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        if (! $viewData instanceof GeneratorRequest) {
            throw new UnexpectedTypeException($viewData, GeneratorRequest::class);
        }

        try {
            /** @var FormInterface[] $forms */
            $forms = iterator_to_array($forms);

            $title = $forms['title']->getData();
            if (is_string($title)) {
                $viewData->title = $title;
            }

            $userInput = $forms['userInput']->getData();
            if (is_string($userInput)) {
                $viewData->userInput = new UserInput($userInput);
            }

            $optimizedPrompt = $forms['prompt']->getData();
            if (is_string($optimizedPrompt)) {
                $viewData->prompt = new OptimizedPrompt($optimizedPrompt);
            }
        } catch (Throwable) {
            return;
        }
    }
}
