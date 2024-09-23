<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Presentation\Form;

use DZunke\NovDoc\Settings\Domain\ValueObject\Settings;
use DZunke\NovDoc\Settings\Domain\ValueObject\Settings\ChatbotTuning;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Throwable;
use Traversable;

use function is_float;
use function iterator_to_array;

final class ChatbotTuningType extends AbstractType implements DataMapperInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Settings::class);
        $resolver->setDefault('twig_view', 'settings/chatbot_tuning.html.twig');
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setDataMapper($this);

        $builder->add(
            'temperature',
            NumberType::class,
            [
                'label' => 'Temperatur',
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
            throw new UnexpectedTypeException($viewData, Settings::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $forms['temperature']->setData($viewData->getChatbotTuning()->getTemperature());
        $forms['imagesMaxDistance']->setData($viewData->getChatbotTuning()->getImagesMaxDistance());
        $forms['documentsMaxDistance']->setData($viewData->getChatbotTuning()->getDocumentsMaxDistance());
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

            $temperature = $forms['temperature']->getData();
            if (! is_float($temperature)) {
                throw new UnexpectedTypeException($temperature, 'float');
            }

            $imagesMaxDistance = $forms['imagesMaxDistance']->getData();
            if (! is_float($imagesMaxDistance)) {
                throw new UnexpectedTypeException($imagesMaxDistance, 'float');
            }

            $documentsMaxDistance = $forms['documentsMaxDistance']->getData();
            if (! is_float($documentsMaxDistance)) {
                throw new UnexpectedTypeException($documentsMaxDistance, 'float');
            }

            $viewData->setChatbotTuning(new ChatbotTuning(
                $temperature,
                $imagesMaxDistance,
                $documentsMaxDistance,
            ));
        } catch (Throwable) {
            return;
        }
    }
}
