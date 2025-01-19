<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Form;

use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Traversable;

use function iterator_to_array;

final class WorldItemType extends AbstractType implements DataMapperInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
            'empty_data' => false,
        ]);
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setDataMapper($this);

        $builder->add('type', EnumType::class, [
            'label' => 'Typ',
            'class' => ItemType::class,
            'choice_label' => static fn (ItemType $type) => $type->getLabel(),
        ]);

        $builder->add('name', TextType::class, [
            'label' => 'Bezeichnung',
            'required' => true,
            'constraints' => [new NotBlank()],
        ]);

        $builder->add('shortDescription', TextareaType::class, [
            'label' => 'Kurzbeschreibung',
            'required' => false,
            'empty_data' => '',
            'help' => 'Ausführliche Informationen sollten über die Bibliothek und die Beziehungen verwaltet werden.',
        ]);
    }

    /** @param Traversable<FormInterface> $forms */
    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            $viewData = Item::create(ItemType::PERSON, '', '');
        }

        if (! $viewData instanceof Item) {
            throw new UnexpectedTypeException($viewData, Item::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $forms['type']->setData($viewData->getType());
        $forms['name']->setData($viewData->getName());
        $forms['shortDescription']->setData($viewData->getShortDescription());
    }

    /** @param Traversable<FormInterface> $forms */
    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        if (! $viewData instanceof Item) {
            $viewData = Item::create(
                $forms['type']->getData(),
                (string) $forms['name']->getData(),
                (string) $forms['shortDescription']->getData(),
            );
        }

        $viewData->rename((string) $forms['name']->getData());
        $viewData->changeShortDescription((string) $forms['shortDescription']->getData());
    }
}
