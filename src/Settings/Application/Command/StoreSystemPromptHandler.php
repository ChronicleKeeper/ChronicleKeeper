<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\SerializerInterface;

use function json_decode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

#[AsMessageHandler]
class StoreSystemPromptHandler
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(StoreSystemPrompt $command): MessageEventResult
    {
        // Load the content of all existing system prompts if the file already exists
        $prompts = [];
        if ($this->fileAccess->exists('storage', 'system_prompts.json')) {
            $content = $this->fileAccess->read('storage', 'system_prompts.json');
            $prompts = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        }

        // Convert the system prompt to array and add it to the list, existing one will be overwritten if it already exists
        $prompts[$command->systemPrompt->getId()] = $command->systemPrompt->jsonSerialize();

        // Serialize the payload to json with pretty print
        $promptsAsJson = $this->serializer->serialize($prompts, 'json', ['json_encode_options' => JSON_PRETTY_PRINT]);

        // Write the content back to the file
        $this->fileAccess->write('storage', 'system_prompts.json', $promptsAsJson);

        return new MessageEventResult($command->systemPrompt->flushEvents());
    }
}
