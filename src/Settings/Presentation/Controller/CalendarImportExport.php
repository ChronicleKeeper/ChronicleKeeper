<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Controller;

use ChronicleKeeper\Calendar\Application\Exception\CalendarConfigurationIncomplete;
use ChronicleKeeper\Settings\Application\Service\Version;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

use function assert;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;
use function json_validate;
use function sys_get_temp_dir;
use function tempnam;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final class CalendarImportExport extends AbstractController
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
        private readonly Version $version,
    ) {
    }

    #[Route('/settings/calendar/import_export', name: 'settings_calendar_import_export', methods: ['GET'])]
    public function overview(): Response
    {
        return $this->render('settings/import_export.html.twig');
    }

    #[Route('/settings/calendar/export', name: 'settings_calendar_export', methods: ['GET'])]
    public function export(): Response
    {
        try {
            $calendarSettings = $this->settingsHandler->get()->getCalendarSettings();

            $json     = json_encode(
                [
                    'version' => $this->version->getCurrentNumericVersion(),
                    'type' => 'settings_calendar',
                    'data' => $calendarSettings,
                ],
                JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
            );
            $tempFile = tempnam(sys_get_temp_dir(), 'calendar_settings_');
            file_put_contents($tempFile, $json);

            // Return as downloadable file
            $response = new BinaryFileResponse($tempFile);
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'calendar_settings.json',
            );
            $response->deleteFileAfterSend();

            return $response;
        } catch (CalendarConfigurationIncomplete $e) {
            $this->addFlash('danger', 'Kalendereinstellungen unvollständig. ' . $e->getMessage());

            return $this->redirectToRoute('settings_calendar_general');
        } catch (Throwable $e) {
            $this->addFlash('danger', 'Export fehlgeschlagen: ' . $e->getMessage());

            return $this->redirectToRoute('settings_calendar_general');
        }
    }

    #[Route('/settings/calendar/import', name: 'settings_calendar_import', methods: ['POST'])]
    public function import(Request $request): Response
    {
        try {
            $file = $request->files->get('calendar_settings_file');
            assert($file instanceof UploadedFile || $file === null);

            if (! $file instanceof UploadedFile) {
                throw new InvalidArgumentException('Keine Datei hochgeladen');
            }

            if ($file->getClientOriginalExtension() !== 'json') {
                throw new InvalidArgumentException('Ungültiges Dateiformat. Nur JSON-Dateien werden unterstützt.');
            }

            $content = file_get_contents($file->getPathname());
            if ($content === false || json_validate($content) === false) {
                throw new InvalidArgumentException('Die Datei ist kein gültiges JSON-Dokument');
            }

            $importedSettings = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            $calendarSettings = $importedSettings['data'] ?? $importedSettings;

            $currentSettings = $this->settingsHandler->get();
            $currentSettings->setCalendarSettings(CalendarSettings::fromArray($calendarSettings));

            $this->settingsHandler->store();

            $this->addFlash('success', 'Kalendereinstellungen wurden erfolgreich importiert');
        } catch (Throwable $e) {
            $this->addFlash('danger', 'Import fehlgeschlagen: ' . $e->getMessage());
        }

        return $this->redirectToRoute('settings_calendar_overview');
    }
}
