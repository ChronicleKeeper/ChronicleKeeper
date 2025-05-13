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
                'empty_data' => '',
            ],
        );
    }

    /** @param Traversable<FormInterface> $forms */
    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            // Create a default document, when there are no view data given to set default values
            $viewData = Document::create('', '');
        }

        if (! $viewData instanceof Document) {
            throw new UnexpectedTypeException($viewData, Document::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $forms['title']->setData($viewData->getTitle());
        $forms['content']->setData($viewData->getContent());
        $forms['directory']->setData($viewData->getDirectory());
    }

    /** @param Traversable<FormInterface> $forms */
    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        if (! $viewData instanceof Document) {
            // Create a new document

            $viewData = Document::create(
                (string) $forms['title']->getData(),
                (string) $forms['content']->getData(),
                $forms['directory']->getData(),
            );

            return;
        }

        // Update an existing document

        $directory = $forms['directory']->getData();
        if ($directory !== $viewData->getDirectory()) {
            $viewData->moveToDirectory($forms['directory']->getData());
        }

        $title = $forms['title']->getData();
        if ($title !== $viewData->getTitle()) {
            $viewData->rename($title);
        }

        $content = (string) $forms['content']->getData();
        if ($content === $viewData->getContent()) {
            return;
        }

        $viewData->changeContent($content);
    }
}
