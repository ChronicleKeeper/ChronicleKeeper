<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Form;

use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use ChronicleKeeper\Settings\Presentation\Form\SystemPromptChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CreateConversationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data', ['title' => 'Neues Gespräch', 'utilize_prompt' => null]);
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'title',
            TextType::class,
            [
                'label' => 'Titel des Gesprächs',
                'empty_data' => 'Neues Gespräch',
                'translation_domain' => false,
            ],
        );

        $builder->add(
            'utilize_prompt',
            SystemPromptChoiceType::class,
            [
                'label' => 'System Prompt für das Gespräch',
                'for_purpose' => Purpose::CONVERSATION,
            ],
        );
    }
}
