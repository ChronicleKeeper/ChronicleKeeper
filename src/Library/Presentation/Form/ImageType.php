<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Form;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\Entity\Image;
use DateTimeImmutable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Throwable;
use Traversable;

use function is_string;
use function iterator_to_array;

class ImageType extends AbstractType implements DataMapperInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Image::class);
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
                'translation_domain' => false,
                'required' => false,
                'constraints' => [new NotBlank()],
            ],
        );

        $builder->add(
            'directory',
            DirectoryChoiceType::class,
        );

        $builder->add(
            'description',
            TextareaType::class,
            [
                'label' => 'Beschreibung',
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

        if (! $viewData instanceof Image) {
            throw new Exception\UnexpectedTypeException($viewData, Image::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $forms['title']->setData($viewData->title);
        $forms['directory']->setData($viewData->directory);
        $forms['description']->setData($viewData->description);
    }

    /** @param Traversable<FormInterface> $forms */
    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        if (! $viewData instanceof Image) {
            throw new Exception\UnexpectedTypeException($viewData, Image::class);
        }

        $viewData->updatedAt = new DateTimeImmutable();

        try {
            /** @var FormInterface[] $forms */
            $forms = iterator_to_array($forms);

            $title = $forms['title']->getData();
            if (is_string($title)) {
                $viewData->title = $title;
            }

            $description = $forms['description']->getData();
            if (is_string($description)) {
                $viewData->description = $description;
            }

            $directory = $forms['directory']->getData();
            if ($directory instanceof Directory) {
                $viewData->directory = $directory;
            }
        } catch (Throwable) {
            return;
        }
    }
}
