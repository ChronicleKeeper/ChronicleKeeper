<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Form;

use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Traversable;

use function in_array;
use function iterator_to_array;
use function ksort;
use function strcmp;
use function usort;

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

        $groupedTypes = ItemType::getGroupedTypes();
        ksort($groupedTypes); // Sort the groups alphabetically

        $choices = [];
        foreach ($groupedTypes as $groupItems) {
            usort($groupItems, static fn (ItemType $a, ItemType $b) => strcmp($a->getLabel(), $b->getLabel()));
            $choices = [...$choices, ...$groupItems];
        }

        $builder->add('type', ChoiceType::class, [
            'label' => 'Typ',
            'choices' => $choices,
            'choice_value' => static fn (ItemType|null $type) => $type?->value,
            'choice_label' => static fn (ItemType $type) => $type->getLabel(),
            'group_by' => static function (ItemType $type): string {
                foreach (ItemType::getGroupedTypes() as $groupName => $types) {
                    if (in_array($type, $types, true)) {
                        return $groupName;
                    }
                }

                return 'Sonstiges';
            },
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

            return;
        }

        $viewData->rename((string) $forms['name']->getData());
        $viewData->changeShortDescription((string) $forms['shortDescription']->getData());
    }
}
