<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Form;

use ChronicleKeeper\Chat\Application\ValueObject\Settings;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Throwable;
use Traversable;

use function is_float;
use function is_string;
use function iterator_to_array;

final class ConversationSettingsType extends AbstractType implements DataMapperInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Settings::class);
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setDataMapper($this);

        $builder->add(
            'version',
            ChoiceType::class,
            [
                'choices' => [
                    Version::gpt4oMini()->name => Version::gpt4oMini()->name,
                    Version::gpt4o()->name => Version::gpt4o()->name,
                    Version::o1Mini()->name => Version::o1Mini()->name,
                ],
            ],
        );

        $builder->add(
            'temperature',
            NumberType::class,
            [
                'label' => 'Temperatur',
                'translation_domain' => false,
                'required' => false,
                'scale' => 2,
                'constraints' => [
                    new LessThanOrEqual(2.0),
                    new GreaterThanOrEqual(0.1),
                ],
            ],
        );

        $builder->add(
            'imagesMaxDistance',
            NumberType::class,
            [
                'label' => 'Maximale Distanz bei Bildersuche',
                'translation_domain' => false,
                'required' => false,
                'scale' => 2,
                'constraints' => [
                    new LessThanOrEqual(1.0),
                    new GreaterThanOrEqual(0.1),
                ],
            ],
        );

        $builder->add(
            'documentsMaxDistance',
            NumberType::class,
            [
                'label' => 'Maximale Distanz bei Dokumentensuche',
                'translation_domain' => false,
                'required' => false,
                'scale' => 2,
                'constraints' => [
                    new LessThanOrEqual(1.0),
                    new GreaterThanOrEqual(0.1),
                ],
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
            throw new Exception\UnexpectedTypeException($viewData, Settings::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $forms['version']->setData($viewData->version);
        $forms['temperature']->setData($viewData->temperature);
        $forms['imagesMaxDistance']->setData($viewData->imagesMaxDistance);
        $forms['documentsMaxDistance']->setData($viewData->documentsMaxDistance);
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

            $version = $forms['version']->getData();
            if (! is_string($version)) {
                throw new Exception\UnexpectedTypeException($version, 'string');
            }

            $temperature = $forms['temperature']->getData();
            if (! is_float($temperature)) {
                throw new Exception\UnexpectedTypeException($temperature, 'float');
            }

            $imagesMaxDistance = $forms['imagesMaxDistance']->getData();
            if (! is_float($imagesMaxDistance)) {
                throw new Exception\UnexpectedTypeException($imagesMaxDistance, 'float');
            }

            $documentsMaxDistance = $forms['documentsMaxDistance']->getData();
            if (! is_float($documentsMaxDistance)) {
                throw new Exception\UnexpectedTypeException($documentsMaxDistance, 'float');
            }

            $viewData = new Settings($version, $temperature, $imagesMaxDistance, $documentsMaxDistance);
        } catch (Throwable) {
            return;
        }
    }
}
