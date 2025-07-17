<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Form;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotFunctions;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolboxFactory;
use PhpLlm\LlmChain\Platform\Tool\Tool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Throwable;
use Traversable;

use function is_bool;
use function iterator_to_array;
use function preg_replace;
use function time;

final class ChatbotFunctionsType extends AbstractType implements DataMapperInterface
{
    /** @var Tool[] */
    private readonly array $tools;

    public function __construct(
        private readonly ToolboxFactory $toolboxFactory,
    ) {
        $this->tools = $this->toolboxFactory->create()->getTools();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Settings::class);
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

        foreach ($this->tools as $tool) {
            $builder->add(
                $tool->name,
                TextareaType::class,
                [
                    'label' => $tool->name,
                    'translation_domain' => false,
                    'required' => false,
                    'empty_data' => '',
                ],
            );
        }

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

        $descriptions = $viewData->getChatbotFunctions()->getFunctionDescriptions();
        foreach ($this->tools as $tool) {
            $forms[$tool->name]->setData($descriptions[$tool->name] ?? $tool->description);
        }
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

            $descriptions = [];
            foreach ($this->tools as $tool) {
                $formData = $forms[$tool->name]->getData();
                $formData = preg_replace("/\r|\n/", "\n", (string) $formData); // From windows to linux line breaks

                $formDataWithoutWhitespaces        = preg_replace("/\r|\n/", '', (string) $formData);
                $toolDescriptionWithoutWhitespaces = preg_replace("/\r|\n/", '', $tool->description);
                if ($formData === '') {
                    continue;
                }

                if ($formDataWithoutWhitespaces === $toolDescriptionWithoutWhitespaces) {
                    continue;
                }

                $descriptions[$tool->name] = $forms[$tool->name]->getData() ?? '';
            }

            $viewData->setChatbotFunctions(new ChatbotFunctions($allowDebugOutput, $descriptions));
        } catch (Throwable) {
            return;
        }
    }
}
