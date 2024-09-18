<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Application;

use DZunke\NovDoc\Infrastructure\Application\Exporter;
use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function file_get_contents;
use function filesize;
use function unlink;

#[Route('/application/export', name: 'application_export')]
class Export
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Exporter $exporter,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $zipFilePath = $this->exporter->export();

        $zipFile = file_get_contents($zipFilePath);
        if ($zipFile === false) {
            throw new RuntimeException('Error during ZIP Archive creation.');
        }

        $response = new Response($zipFile);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipFilePath . '"');
        $response->headers->set('Content-length', (string) filesize($zipFilePath));

        @unlink($zipFilePath);

        return $response;
    }
}
