<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Application;

use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use ZipArchive;

use function date;
use function file_get_contents;
use function filesize;
use function preg_match_all;
use function unlink;

#[Route('/application/export', name: 'application_export')]
class Export
{
    use HandleFlashMessages;

    public function __construct(
        private readonly string $dotEnvFile,
        private readonly string $settingsFilePath,
        private readonly string $directoryStoragePath,
        private readonly string $documentStoragePath,
        private readonly string $vectorDocumentsPath,
        private readonly string $changelogFile,
        private readonly string $libraryImageStoragePath,
        private readonly string $vectorImagesPath,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $zipName = 'NovDoc-Export-' . date('Y-m-d-H-i-s') . '.zip';

        $zip = new ZipArchive();
        $zip->open($zipName, ZipArchive::CREATE);
        $zip->addFromString('VERSION', $this->parseVersionFromChangelog());
        $zip->addFile($this->dotEnvFile, '.env');
        $zip->addFile($this->settingsFilePath, 'settings.json');

        $this->archiveLibraryDirectories($zip);
        $this->archiveLibraryDocuments($zip);
        $this->archiveLibraryImages($zip);
        $this->archiveVectorStorageDocuments($zip);
        $this->archiveVectorStorageImages($zip);

        $zip->close();

        $zipFile = file_get_contents($zipName);
        if ($zipFile === false) {
            throw new RuntimeException('Error during ZIP Archive creation.');
        }

        $response = new Response($zipFile);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipName . '"');
        $response->headers->set('Content-length', (string) filesize($zipName));

        @unlink($zipName);

        return $response;
    }

    private function parseVersionFromChangelog(): string
    {
        $changelog = file_get_contents($this->changelogFile);
        if ($changelog === false) {
            return 'latest'; // default version
        }

        preg_match_all('/\[(.*?)\]/', $changelog, $foundVersions);

        $foundVersions = $foundVersions[1];

        return $foundVersions[0];
    }

    private function archiveLibraryDirectories(ZipArchive $archive): void
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->directoryStoragePath)
            ->files();

        foreach ($finder as $directory) {
            $archive->addFile($directory->getRealPath(), 'library/directory/' . $directory->getFilename());
        }
    }

    private function archiveLibraryDocuments(ZipArchive $archive): void
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->documentStoragePath)
            ->files();

        foreach ($finder as $document) {
            $archive->addFile($document->getRealPath(), 'library/document/' . $document->getFilename());
        }
    }

    private function archiveLibraryImages(ZipArchive $archive): void
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->libraryImageStoragePath)
            ->files();

        foreach ($finder as $image) {
            $archive->addFile($image->getRealPath(), 'library/images/' . $image->getFilename());
        }
    }

    private function archiveVectorStorageDocuments(ZipArchive $archive): void
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->vectorDocumentsPath)
            ->files();

        foreach ($finder as $vectorDocument) {
            $archive->addFile($vectorDocument->getRealPath(), 'vector/document/' . $vectorDocument->getFilename());
        }
    }

    private function archiveVectorStorageImages(ZipArchive $archive): void
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->vectorImagesPath)
            ->files();

        foreach ($finder as $vectorImage) {
            $archive->addFile($vectorImage->getRealPath(), 'vector/image/' . $vectorImage->getFilename());
        }
    }
}
