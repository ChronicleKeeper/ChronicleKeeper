<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Form\Settings;

use DZunke\NovDoc\Domain\Settings\Settings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Throwable;
use Traversable;

use function is_bool;
use function iterator_to_array;
use function time;

final class ChatbotFunctionsType extends AbstractType implements DataMapperInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Settings::class);
        $resolver->setDefault('twig_view', 'settings/chatbot_functions.html.twig');
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setDataMapper($this);

        $builder->add(
            'allowDebugOutput',
            CheckboxType::class,
            [
                'label' => 'Ausgeführte Funktionsaufrufe im Chat anzeigen',
                'translation_domain' => false,
                'required' => false,
            ],
        );

        $builder->add('timestamp', HiddenType::class, ['mapped' => false, 'data' => time()]);
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

        $forms['allowDebugOutput']->setData($viewData->getChatbotFunctions()->isAllowDebugOutput());
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

            $allowDebugOutput = $forms['allowDebugOutput']->getData();
            if (! is_bool($allowDebugOutput)) {
                throw new UnexpectedTypeException($allowDebugOutput, 'bool');
            }

            $viewData->setChatbotFunctions(new Settings\ChatbotFunctions($allowDebugOutput));
        } catch (Throwable) {
            return;
        }
    }
}
