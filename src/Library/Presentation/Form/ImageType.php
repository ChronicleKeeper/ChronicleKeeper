<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Form;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
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

        if (! $viewData instanceof Image) {
            throw new UnexpectedTypeException($viewData, Image::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $forms['title']->setData($viewData->getTitle());
        $forms['directory']->setData($viewData->getDirectory());
        $forms['description']->setData($viewData->getDescription());
    }

    /** @param Traversable<FormInterface> $forms */
    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        if (! $viewData instanceof Image) {
            throw new UnexpectedTypeException($viewData, Image::class);
        }

        try {
            /** @var FormInterface[] $forms */
            $forms = iterator_to_array($forms);

            $title = $forms['title']->getData();
            if (is_string($title)) {
                $viewData->rename($title);
            }

            $description = $forms['description']->getData();
            if (is_string($description)) {
                $viewData->updateDescription($description);
            }

            $directory = $forms['directory']->getData();
            if ($directory instanceof Directory) {
                $viewData->moveToDirectory($directory);
            }
        } catch (Throwable) {
            return;
        }
    }
}
