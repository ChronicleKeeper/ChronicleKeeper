<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain;

use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\UserMessage;

class UserMessageBuilder
{
    private string $content;

    public function __construct()
    {
        $this->content = 'Default content';
    }

    public function withContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function build(): UserMessage
    {
        return new UserMessage(new Text($this->content));
    }
}
