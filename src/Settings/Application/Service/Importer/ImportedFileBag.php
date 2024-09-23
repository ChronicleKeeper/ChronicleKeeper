<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Application\Service\Importer;

use ArrayObject;

use function array_values;

/** @template-extends ArrayObject<int, ImportedFile> */
final class ImportedFileBag extends ArrayObject
{
    public function __construct(ImportedFile ...$importedFile)
    {
        parent::__construct(array_values($importedFile));
    }

    public function extend(ImportedFile ...$importedFile): ImportedFileBag
    {
        return new self(...$this, ...$importedFile);
    }
}
