<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Form\Settings;

use DZunke\NovDoc\Domain\Settings\Settings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Throwable;
use Traversable;

use function is_string;
use function iterator_to_array;

final class ChatbotSystemPromptType extends AbstractType implements DataMapperInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Settings::class);
        $resolver->setDefault('twig_view', 'settings/chatbot_system_prompt.html.twig');
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setDataMapper($this);

        $builder->add(
            'systemPrompt',
            TextareaType::class,
            [
                'label' => 'System Prompt',
                'required' => false,
                'translation_domain' => false,
                'constraints' => [new NotBlank()],
                'empty_data' => '',
            ],
        );
    }

    /** @param Traversable<FormInterface> $forms */
    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            return;
        }

        if (! $viewData instanceof Settings) {
            throw new UnexpectedTypeException($viewData, Settings::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $forms['systemPrompt']->setData($viewData->getChatbotSystemPrompt()->getSystemPrompt());
    }

    /** @param Traversable<FormInterface> $forms */
    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        if (! $viewData instanceof Settings) {
            $viewData = new Settings();
        }

        try {
            /** @var FormInterface[] $forms */
            $forms = iterator_to_array($forms);

            $systemPrompt = $forms['systemPrompt']->getData();
            if (! is_string($systemPrompt)) {
                throw new UnexpectedTypeException($systemPrompt, 'string');
            }

            $viewData->setChatbotSystemPrompt(new Settings\ChatbotSystemPrompt($systemPrompt));
        } catch (Throwable) {
            return;
        }
    }
}
