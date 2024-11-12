<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Form;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Throwable;
use Traversable;

use function is_string;
use function iterator_to_array;

final class ApplicationType extends AbstractType implements DataMapperInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Settings::class);
        $resolver->setDefault('twig_view', 'settings/application.html.twig');
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setDataMapper($this);

        $builder->add(
            'openAIApiKey',
            TextareaType::class,
            [
                'label' => 'OpenAI API Key',
                'translation_domain' => false,
                'required' => false,
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

        $forms['openAIApiKey']->setData($viewData->getApplication()->openAIApiKey);
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

            $openAIApiKey = $forms['openAIApiKey']->getData();
            if (! is_string($openAIApiKey)) {
                throw new UnexpectedTypeException($openAIApiKey, 'string');
            }

            $viewData->setApplication(new Application($openAIApiKey));
        } catch (Throwable) {
            return;
        }
    }
}
