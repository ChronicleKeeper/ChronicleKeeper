<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Controller\CalendarOverview;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\EpochSettings as SettingsEpochSettings;
use ChronicleKeeper\Settings\Presentation\Controller\CalendarOverview\EpochSettings;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\EpochsType;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\EpochType;
use ChronicleKeeper\Settings\Presentation\Twig\EpochsForm;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(EpochSettings::class)]
#[CoversClass(CalendarSettings::class)]
#[CoversClass(EpochSettings::class)]
#[CoversClass(SettingsHandler::class)]
#[CoversClass(EpochsForm::class)]
#[CoversClass(EpochsType::class)]
#[CoversClass(EpochType::class)]
#[CoversClass(SettingsEpochSettings::class)]
#[Large]
final class EpochSettingsTest extends WebTestCase
{
    #[Test]
    public function itShowsTheEpochForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/epochs');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Einstellungen - Kalender - Epochen');
        self::assertSelectorExists('form[name="epochs"]');
        self::assertSelectorExists('#epochs_epochs_add');
    }

    #[Test]
    public function itShowsAFilledEpochForm(): void
    {
        $settings = (new SettingsBuilder())->withDefaultCalendarSettings()->build();

        $originSettings = $this->settingsHandler->get();
        $originSettings->setCalendarSettings($settings->getCalendarSettings());
        $this->settingsHandler->store();

        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/epochs');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Einstellungen - Kalender - Epochen');
        self::assertSelectorExists('form[name="epochs"]');
        self::assertSelectorExists('#epochs_epochs_add');

        self::assertFormValue('form[name="epochs"]', 'epochs[epochs][0][name]', 'First Age');
        self::assertFormValue('form[name="epochs"]', 'epochs[epochs][0][start_year]', '0');
        self::assertFormValue('form[name="epochs"]', 'epochs[epochs][0][end_year]', '');
    }

    #[Test]
    public function itIsAbleToStoreEpochs(): void
    {
        $this->client->request(Request::METHOD_POST, '/settings/calendar_overview/epochs', [
            'epochs' => [
                'epochs' => [
                    ['name' => 'First Age', 'start_year' => 0, 'end_year' => 999],
                    ['name' => 'Second Age', 'start_year' => 1000, 'end_year' => null],
                ],
            ],
        ]);

        self::assertResponseRedirects('/settings/calendar_overview');

        $settings         = $this->settingsHandler->get();
        $calendarSettings = $settings->getCalendarSettings();

        self::assertCount(2, $calendarSettings->getEpochs());

        self::assertEquals('First Age', $calendarSettings->getEpochs()[0]->getName());
        self::assertEquals(0, $calendarSettings->getEpochs()[0]->getStartYear());
        self::assertEquals(999, $calendarSettings->getEpochs()[0]->getEndYear());

        self::assertEquals('Second Age', $calendarSettings->getEpochs()[1]->getName());
        self::assertEquals(1000, $calendarSettings->getEpochs()[1]->getStartYear());
        self::assertNull($calendarSettings->getEpochs()[1]->getEndYear());
    }

    #[Test]
    public function itExecutesAValidationToTheSubmittedData(): void
    {
        $this->client->request(Request::METHOD_POST, '/settings/calendar_overview/epochs', [
            'epochs' => [
                'epochs' => [
                    ['name' => 'First Age', 'start_year' => 0, 'end_year' => 999],
                    ['name' => 'Second Age', 'start_year' => 1000, 'end_year' => null],
                    ['name' => '', 'start_year' => 2000, 'end_year' => null],
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert-danger', 'Only the last epoch can have an undefined end year.');
    }
}
