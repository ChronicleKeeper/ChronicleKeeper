<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Twig;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\EpochSettings;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\EpochsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;

/** @phpstan-import-type EpochSettingsArray from EpochSettings */
#[AsLiveComponent(name: 'SettingsEpochsForm', template: 'components/settings/epochs_form.html.twig')]
class EpochsForm extends AbstractController
{
    use DefaultActionTrait;
    use LiveCollectionTrait;

    /** @var array{epochs: array<EpochSettingsArray>}|null */
    #[LiveProp(fieldName: 'formData')]
    public array|null $epochs = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(
            EpochsType::class,
            $this->epochs,
        );
    }
}
