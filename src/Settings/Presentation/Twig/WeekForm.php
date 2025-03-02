<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Twig;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\WeekSettings;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\WeekType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;

/** @phpstan-import-type WeekSettingsArray from WeekSettings */
#[AsLiveComponent(name: 'SettingsWeekForm', template: 'components/settings/week_form.html.twig')]
class WeekForm extends AbstractController
{
    use DefaultActionTrait;
    use LiveCollectionTrait;

    /** @var array{weekdays: array<WeekSettingsArray>}|null */
    #[LiveProp(fieldName: 'formData')]
    public array|null $weekdays = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(
            WeekType::class,
            $this->weekdays,
        );
    }
}
