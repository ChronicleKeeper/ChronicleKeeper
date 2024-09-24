<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Form;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotGeneral;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Throwable;
use Traversable;

use function is_bool;
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
            'maxImageResponses',
            IntegerType::class,
            [
                'label' => 'Maximale Kontextbilder',
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

        $builder->add(
            'showReferencedDocuments',
            CheckboxType::class,
            [
                'label' => 'Referenzierte Dokumente anzeigen',
                'translation_domain' => false,
                'required' => false,
            ],
        );

        $builder->add(
            'showReferencedImages',
            CheckboxType::class,
            [
                'label' => 'Referenzierte Bilder anzeigen',
                'translation_domain' => false,
                'required' => false,
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
        $forms['maxImageResponses']->setData($viewData->getChatbotGeneral()->getMaxImageResponses());
        $forms['chatbotName']->setData($viewData->getChatbotGeneral()->getChatbotName());
        $forms['chatterName']->setData($viewData->getChatbotGeneral()->getChatterName());
        $forms['showReferencedDocuments']->setData($viewData->getChatbotGeneral()->showReferencedDocuments());
        $forms['showReferencedImages']->setData($viewData->getChatbotGeneral()->showReferencedImages());
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

            $maxImageResponses = $forms['maxImageResponses']->getData();
            if (! is_int($maxImageResponses)) {
                throw new UnexpectedTypeException($maxImageResponses, 'int');
            }

            $chatbotName = $forms['chatbotName']->getData();
            if (! is_string($chatbotName)) {
                throw new UnexpectedTypeException($chatbotName, 'string');
            }

            $chatterName = $forms['chatterName']->getData();
            if (! is_string($chatterName)) {
                throw new UnexpectedTypeException($chatterName, 'string');
            }

            $showReferencedDocuments = $forms['showReferencedDocuments']->getData();
            if (! is_bool($showReferencedDocuments)) {
                throw new UnexpectedTypeException($showReferencedDocuments, 'bool');
            }

            $showReferencedImages = $forms['showReferencedImages']->getData();
            if (! is_bool($showReferencedImages)) {
                throw new UnexpectedTypeException($showReferencedImages, 'bool');
            }

            $viewData->setChatbotGeneral(new ChatbotGeneral(
                $maxDocumentResponses,
                $maxImageResponses,
                $chatbotName,
                $chatterName,
                $showReferencedDocuments,
                $showReferencedImages,
            ));
        } catch (Throwable) {
            return;
        }
    }
}
