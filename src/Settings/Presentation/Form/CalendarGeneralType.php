<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Form;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\Calendar;
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

final class CalendarGeneralType extends AbstractType implements DataMapperInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Settings::class);
        $resolver->setDefault('twig_view', 'settings/calendar_general.html.twig');
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setDataMapper($this);

        $builder->add(
            'currentDate',
            TextType::class,
            [
                'label' => 'Aktuelles Datum',
                'translation_domain' => false,
                'required' => false,
                'constraints' => [new NotBlank()],
            ],
        );

        $builder->add(
            'calendarDescription',
            TextareaType::class,
            [
                'label' => 'Erläuterungen zum Kalender',
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

        $forms['currentDate']->setData($viewData->getCalendar()->getCurrentDate());
        $forms['calendarDescription']->setData($viewData->getCalendar()->getCalendarDescription());
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

            $currentDate = $forms['currentDate']->getData();
            if (! is_string($currentDate)) {
                throw new UnexpectedTypeException($currentDate, 'string');
            }

            $description = $forms['calendarDescription']->getData();
            if (! is_string($description)) {
                throw new UnexpectedTypeException($description, 'string');
            }

            $viewData->setCalendar(new Calendar($currentDate, $description));
        } catch (Throwable) {
            return;
        }
    }
}
