<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Lazy;

use function array_filter;
use function array_key_first;
use function array_values;
use function assert;
use function count;

#[Lazy]
class SystemPromptRegistry
{
    /** @var array<string, SystemPrompt> */
    private readonly array $prompts;

    public function __construct(
        private readonly SystemPromptLoader $loader,
    ) {
        $this->prompts = $this->loader->load();
    }

    /** @return list<SystemPrompt> */
    public function all(): array
    {
        return array_values($this->prompts);
    }

    /** @return list<SystemPrompt> */
    public function findByPurpose(Purpose $purpose): array
    {
        $prompts = [];

        foreach ($this->prompts as $prompt) {
            if ($prompt->getPurpose() !== $purpose) {
                continue;
            }

            $prompts[] = $prompt;
        }

        return $prompts;
    }

    public function getDefaultForPurpose(Purpose $purpose): SystemPrompt
    {
        $promptsOfPurpose = $this->findByPurpose($purpose);

        // Search for a default entry and return it directly to stop execution of this method
        foreach ($promptsOfPurpose as $prompt) {
            if ($prompt->isDefault()) {
                return $prompt;
            }
        }

        // There is no user specified default, so the system default is returned
        $systemDefaultWithinPurpose = array_filter(
            $promptsOfPurpose,
            static fn (SystemPrompt $prompt) => $prompt->isSystem(),
        );
        assert(count($systemDefaultWithinPurpose) > 0); // Is ensured by the system

        return $systemDefaultWithinPurpose[array_key_first($systemDefaultWithinPurpose)];
    }

    public function getById(string $id): SystemPrompt
    {
        if (isset($this->prompts[$id])) {
            return $this->prompts[$id];
        }

        throw new InvalidArgumentException('Prompt not found');
    }
}
