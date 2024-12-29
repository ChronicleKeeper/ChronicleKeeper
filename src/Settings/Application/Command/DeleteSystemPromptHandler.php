<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use RuntimeException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\SerializerInterface;

use function json_decode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

#[AsMessageHandler]
class DeleteSystemPromptHandler
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(DeleteSystemPrompt $command): void
    {
        // Load the content of all existing system prompts if the file already exists
        if (! $this->fileAccess->exists('storage', 'system_prompts.json')) {
            throw new RuntimeException('The system prompts file does not exist.');
        }

        $content = $this->fileAccess->read('storage', 'system_prompts.json');
        $prompts = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        // Remove the system prompt from the list
        unset($prompts[$command->systemPrompt->getId()]);

        // Serialize the payload to json with pretty print
        $promptsAsJson = $this->serializer->serialize($prompts, 'json', ['json_encode_options' => JSON_PRETTY_PRINT]);

        // Write the content back to the file
        $this->fileAccess->write('storage', 'system_prompts.json', $promptsAsJson);
    }
}
