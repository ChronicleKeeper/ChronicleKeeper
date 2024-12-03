<?php

/*
 * Quentic Platform
 * Copyright(c) Quentic GmbH
 * contact.de@quentic.com
 *
 * https://www.quentic.com/
 */

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Service\Exception;

use RuntimeException;

use function sprintf;

final class PDFHasEmptyContent extends RuntimeException
{
    public static function forFile(string $filename): PDFHasEmptyContent
    {
        return new self(sprintf(
            'The PDF "%s" does not contain any readable text. Please try a different PDF or reformat the PDF to another format.',
            $filename,
        ));
    }
}
