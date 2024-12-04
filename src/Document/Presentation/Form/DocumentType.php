<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Presentation\Form;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Library\Presentation\Form\DirectoryChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Traversable;

use function iterator_to_array;

class DocumentType extends AbstractType implements DataMapperInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Document::class);
        $resolver->setDefault('empty_data', false);
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
                'required' => true,
                'constraints' => [new NotBlank()],
            ],
        );

        $builder->add(
            'directory',
            DirectoryChoiceType::class,
            ['label' => 'Erstellen in Verzeichnis ...'],
        );

        $builder->add(
            'content',
            TextareaType::class,
            [
                'label' => 'Inhalt',
                'required' => true,
                'constraints' => [new NotBlank()],
            ],
        );
    }

    /** @param Traversable<FormInterface> $forms */
    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            $viewData = new Document('', '');
        }

        if (! $viewData instanceof Document) {
            throw new UnexpectedTypeException($viewData, Document::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $forms['title']->setData($viewData->title);
        $forms['content']->setData($viewData->content);
        $forms['directory']->setData($viewData->directory);
    }

    /** @param Traversable<FormInterface> $forms */
    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        if (! $viewData instanceof Document) {
            $viewData = new Document('', '');
        }

        try {
            /** @var FormInterface[] $forms */
            $forms = iterator_to_array($forms);

            $viewData->title     = (string) $forms['title']->getData();
            $viewData->content   = (string) $forms['content']->getData();
            $viewData->directory = $forms['directory']->getData();
        } catch (UnexpectedTypeException) {
            $viewData = new Document('', '');
        }
    }
}
