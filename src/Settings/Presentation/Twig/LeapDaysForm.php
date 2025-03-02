<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Twig;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\LeapDaysType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;

/** @phpstan-import-type MonthSettingsArray from MonthSettings */
#[AsLiveComponent(name: 'SettingsLeapDaysForm', template: 'components/settings/leapDays_form.html.twig')]
class LeapDaysForm extends AbstractController
{
    use DefaultActionTrait;
    use LiveCollectionTrait;

    /** @var MonthSettingsArray|null */
    #[LiveProp(fieldName: 'formData')]
    public array|null $month = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(
            LeapDaysType::class,
            $this->month,
        );
    }
}
