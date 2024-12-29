<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Form;

use ChronicleKeeper\Settings\Application\Service\SystemPromptRegistry;
use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_filter;
use function array_values;
use function uasort;

final class SystemPromptChoiceType extends AbstractType
{
    public function __construct(
        private readonly SystemPromptRegistry $systemPromptRegistry,
    ) {
    }

    #[Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'label' => 'System Prompt',
                'translation_domain' => false,
                'required' => false,
                'choices' => $this->systemPromptRegistry->all(),
                'placeholder' => false,
                'data' => null,
                'choice_value' => static fn (SystemPrompt|null $systemPrompt): string|null => $systemPrompt?->getId(),
                'choice_label' => static fn (SystemPrompt|null $systemPrompt): string|null => $systemPrompt?->getName(),
            ],
        );

        $resolver->setDefault('for_purpose', Purpose::CONVERSATION);
        $resolver->setAllowedTypes('for_purpose', Purpose::class);

        $resolver->setNormalizer('choices', $this->filterChoicesByPurpose(...));
        $resolver->setNormalizer('data', $this->setEmptyDataFromChoices(...));
    }

    private function setEmptyDataFromChoices(Options $options, SystemPrompt|null $data): SystemPrompt
    {
        if ($data instanceof SystemPrompt) {
            return $data;
        }

        return $this->systemPromptRegistry->getDefaultForPurpose($options['for_purpose']);
    }

    /**
     * @param list<SystemPrompt> $choices
     *
     * @return list<SystemPrompt>
     */
    private function filterChoicesByPurpose(Options $options, array $choices): array
    {
        $choices = array_filter(
            $choices,
            static fn (SystemPrompt $systemPrompt) => $systemPrompt->getPurpose() === $options['for_purpose'],
        );

        uasort(
            $choices,
            static function (SystemPrompt $a, SystemPrompt $b) {
                $defaultComparison = $b->isDefault() <=> $a->isDefault();
                if ($defaultComparison !== 0) {
                    return $defaultComparison;
                }

                return $b->isSystem() <=> $a->isSystem();
            },
        );

        return array_values($choices);
    }
}
