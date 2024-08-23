<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\SearchIndex;

use ArrayObject;
use PhpLlm\LlmChain\Document\Document;

use function array_values;

/** @template-extends ArrayObject<int, Document> */
class DocumentBag extends ArrayObject
{
    public function __construct(Document ...$documents)
    {
        parent::__construct(array_values($documents));
    }
}
