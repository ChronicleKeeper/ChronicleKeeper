<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Presentation\Form;

use DZunke\NovDoc\Settings\Domain\ValueObject\Settings;
use DZunke\NovDoc\Settings\Domain\ValueObject\Settings\Holiday;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Throwable;
use Traversable;

use function is_string;
use function iterator_to_array;

final class CalendarHolidayType extends AbstractType implements DataMapperInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Settings::class);
        $resolver->setDefault('twig_view', 'settings/calendar_holiday.html.twig');
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setDataMapper($this);

        $builder->add(
            'description',
            TextareaType::class,
            [
                'label' => 'Erläuterungen der Feiertage',
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

        if (! $viewData instanceof Settings) {
            throw new UnexpectedTypeException($viewData, Settings::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $forms['description']->setData($viewData->getHoliday()->getDescription());
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

            $description = $forms['description']->getData();
            if (! is_string($description)) {
                throw new UnexpectedTypeException($description, 'string');
            }

            $viewData->setHoliday(new Holiday($description));
        } catch (Throwable) {
            return;
        }
    }
}
