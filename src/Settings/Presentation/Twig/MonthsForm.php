<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Twig;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\MonthsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;

/** @phpstan-import-type MonthSettingsArray from MonthSettings */
#[AsLiveComponent(name: 'SettingsMonthsForm', template: 'components/settings/months_form.html.twig')]
class MonthsForm extends AbstractController
{
    use DefaultActionTrait;
    use LiveCollectionTrait;

    /** @var array{weekdays: array<MonthSettingsArray>}|null */
    #[LiveProp(fieldName: 'formData')]
    public array|null $months = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(
            MonthsType::class,
            $this->months,
        );
    }
}
