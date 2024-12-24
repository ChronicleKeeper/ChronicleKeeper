<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Controller;

use ChronicleKeeper\Settings\Application\Service\Exporter;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function file_get_contents;
use function filesize;
use function unlink;

#[Route('/application/export', name: 'application_export')]
class Export extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Exporter $exporter,
    ) {
    }

    public function __invoke(): Response
    {
        $zipFilePath = $this->exporter->export();
        $zipFile     = file_get_contents($zipFilePath);
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
