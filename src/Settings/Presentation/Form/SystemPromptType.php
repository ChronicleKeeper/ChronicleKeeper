<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Form;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SystemPromptType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', SystemPrompt::class);
        $resolver->setDefault('empty_data', static fn (FormInterface $form): SystemPrompt => SystemPrompt::create(
            $form->get('purpose')->getData() ?? Purpose::CONVERSATION,
            $form->get('name')->getData() ?? '',
            $form->get('content')->getData() ?? '',
            $form->get('isDefault')->getData() ?? false,
        ));
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'Name',
                'setter' => static fn (SystemPrompt $prompt, string $name) => $prompt->rename($name),
            ],
        );

        $builder->add(
            'purpose',
            EnumType::class,
            [
                'label' => 'Zweck',
                'class' => Purpose::class,
                'choice_label' => static fn (Purpose $purpose) => $purpose->getLabel(),
                'setter' => static fn (SystemPrompt $prompt, Purpose $purpose) => $prompt->changePurpose($purpose),
            ],
        );

        $builder->add(
            'content',
            TextareaType::class,
            [
                'label' => 'Beschreibung',
                'setter' => static fn (SystemPrompt $prompt, string $content) => $prompt->changeContent($content),
            ],
        );

        $builder->add('isDefault', ChoiceType::class, [
            'label' => 'Standard (Nur Nutzer)',
            'choices' => [
                'Ja' => true,
                'Nein' => false,
            ],
            'setter' => static function (SystemPrompt $prompt, bool|null $isDefault): void {
                (bool) $isDefault ? $prompt->toDefault() : $prompt->toNotDefault();
            },
        ]);
    }
}
