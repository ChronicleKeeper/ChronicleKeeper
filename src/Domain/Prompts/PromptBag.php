<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\Prompts;

use ArrayObject;

use function array_values;

/** @template-extends ArrayObject<int, Prompt> */
class PromptBag extends ArrayObject
{
    public function __construct(Prompt ...$prompts)
    {
        parent::__construct(array_values($prompts));
    }
}
