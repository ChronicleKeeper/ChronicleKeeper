<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Form;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Presentation\Form\DirectoryChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class StoreConversationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Conversation::class);
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'title',
            TextType::class,
            [
                'label' => 'Temperatur',
                'translation_domain' => false,
                'required' => false,
                'constraints' => [new NotBlank()],
                'setter' => static fn (Conversation $conversation, string $title) => $conversation->rename($title),
            ],
        );

        $builder->add(
            'directory',
            DirectoryChoiceType::class,
            [
                'label' => 'Speichern in Verzeichnis ...',
                'setter' => static fn (Conversation $conversation, Directory $directory) => $conversation->moveToDirectory($directory),
            ],
        );
    }
}
