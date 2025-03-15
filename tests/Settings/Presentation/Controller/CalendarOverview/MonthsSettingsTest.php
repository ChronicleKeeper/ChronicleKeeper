<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Controller\CalendarOverview;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Presentation\Constraint\ValidMonthCollection;
use ChronicleKeeper\Settings\Presentation\Controller\CalendarOverview\MonthsSettings;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\MonthsType;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\MonthType;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\LinearWithLeapDaysCalendarSettingsBuilder;
use ChronicleKeeper\Test\WebTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(MonthsSettings::class)]
#[CoversClass(MonthsType::class)]
#[CoversClass(ValidMonthCollection::class)]
#[CoversClass(MonthType::class)]
#[CoversClass(CalendarSettings::class)]
#[Large]
final class MonthsSettingsTest extends WebTestCase
{
    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $originSettings = $this->settingsHandler->get();
        $originSettings->setCalendarSettings((new LinearWithLeapDaysCalendarSettingsBuilder())->build());
        $this->settingsHandler->store();
    }

    #[Test]
    public function itShowsTheMonthsForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/months');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Einstellungen - Kalender - Monate');
        self::assertSelectorExists('form[name="months"]');
    }

    #[Test]
    public function itShowsAFilledLeapDaysForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/months');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Einstellungen - Kalender - Monate');
        self::assertSelectorExists('form[name="months"]');

        self::assertFormValue('form[name="months"]', 'months[months][0][index]', '1');
        self::assertFormValue('form[name="months"]', 'months[months][0][name]', 'Taranis');
        self::assertFormValue('form[name="months"]', 'months[months][0][days]', '30');

        self::assertFormValue('form[name="months"]', 'months[months][7][index]', '8');
        self::assertFormValue('form[name="months"]', 'months[months][7][name]', 'Telisias');
        self::assertFormValue('form[name="months"]', 'months[months][7][days]', '30');

        self::assertFormValue('form[name="months"]', 'months[months][11][index]', '12');
        self::assertFormValue('form[name="months"]', 'months[months][11][name]', 'Nox');
        self::assertFormValue('form[name="months"]', 'months[months][11][days]', '30');
    }

    #[Test]
    public function itIsAbleToStoreTheForm(): void
    {
        $this->client->request(Request::METHOD_POST, '/settings/calendar_overview/months', [
            'months' => [
                'months' => [
                    ['index' => 1, 'name' => 'My Edited Month', 'days' => 30],
                    ['index' => 2, 'name' => 'My Only Left Month', 'days' => 330],
                ],
            ],
        ]);

        self::assertResponseRedirects('/settings/calendar_overview');

        $settings         = $this->settingsHandler->get();
        $calendarSettings = $settings->getCalendarSettings();

        self::assertCount(2, $calendarSettings->getMonths());

        $firstMonth = $calendarSettings->getMonths()[0];
        self::assertSame(1, $firstMonth->getIndex());
        self::assertSame('My Edited Month', $firstMonth->getName());
        self::assertSame(30, $firstMonth->getDays());

        $secondMonth = $calendarSettings->getMonths()[1];
        self::assertSame(2, $secondMonth->getIndex());
        self::assertSame('My Only Left Month', $secondMonth->getName());
        self::assertSame(330, $secondMonth->getDays());
    }

    #[Test]
    public function itExecutesAValidationToTheSubmittedData(): void
    {
        $this->client->request(Request::METHOD_POST, '/settings/calendar_overview/months', [
            'months' => [
                'months' => [
                    ['index' => 1, 'name' => '', 'days' => 30],
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.invalid-feedback', 'This value should not be blank.');
    }
}
