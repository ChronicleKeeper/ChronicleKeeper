<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Form\Settings;

use DZunke\NovDoc\Domain\Settings\Settings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Throwable;
use Traversable;

use function is_int;
use function is_string;
use function iterator_to_array;

final class ChatbotGeneralType extends AbstractType implements DataMapperInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Settings::class);
        $resolver->setDefault('twig_view', 'settings/chatbot_general.html.twig');
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setDataMapper($this);

        $builder->add(
            'maxDocumentResponses',
            IntegerType::class,
            [
                'label' => 'Maximale Kontextdokumente',
                'translation_domain' => false,
                'required' => false,
                'constraints' => [new NotBlank()],
            ],
        );

        $builder->add(
            'chatbotName',
            TextType::class,
            [
                'label' => 'Name des Chatbot',
                'translation_domain' => false,
                'required' => false,
                'constraints' => [new NotBlank()],
            ],
        );

        $builder->add(
            'chatterName',
            TextType::class,
            [
                'label' => 'Name des Benutzers',
                'translation_domain' => false,
                'required' => false,
                'constraints' => [new NotBlank()],
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

        $forms['maxDocumentResponses']->setData($viewData->getChatbotGeneral()->getMaxDocumentResponses());
        $forms['chatbotName']->setData($viewData->getChatbotGeneral()->getChatbotName());
        $forms['chatterName']->setData($viewData->getChatbotGeneral()->getChatterName());
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

            $maxDocumentResponses = $forms['maxDocumentResponses']->getData();
            if (! is_int($maxDocumentResponses)) {
                throw new UnexpectedTypeException($maxDocumentResponses, 'int');
            }

            $chatbotName = $forms['chatbotName']->getData();
            if (! is_string($chatbotName)) {
                throw new UnexpectedTypeException($chatbotName, 'string');
            }

            $chatterName = $forms['chatterName']->getData();
            if (! is_string($chatterName)) {
                throw new UnexpectedTypeException($chatterName, 'string');
            }

            $viewData->setChatbotGeneral(new Settings\ChatbotGeneral($maxDocumentResponses, $chatbotName, $chatterName));
        } catch (Throwable) {
            return;
        }
    }
}