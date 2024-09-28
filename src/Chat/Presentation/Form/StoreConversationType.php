<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Form;

use ChronicleKeeper\Library\Presentation\Form\DirectoryChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class StoreConversationType extends AbstractType
{
    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class);
        $builder->add('directory', DirectoryChoiceType::class);
    }
}
