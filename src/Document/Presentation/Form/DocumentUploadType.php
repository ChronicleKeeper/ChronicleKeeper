<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Presentation\Form;

use ChronicleKeeper\Document\Application\Service\Importer;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use ChronicleKeeper\Settings\Presentation\Form\SystemPromptChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

class DocumentUploadType extends AbstractType
{
    public function __construct(private readonly Importer $importer)
    {
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'document',
            FileType::class,
            [
                'label' => 'Dokument',
                'translation_domain' => false,
                'constraints' => [new NotNull(), new File(mimeTypes: $this->importer->getSupportedMimeTypes())],
            ],
        );

        $builder->add(
            'utilize_prompt',
            SystemPromptChoiceType::class,
            [
                'label' => 'System Prompt',
                'translation_domain' => false,
                'for_purpose' => Purpose::DOCUMENT_OPTIMIZER,
            ],
        );

        $builder->add(
            'optimize',
            CheckboxType::class,
            [
                'label' => 'Automatisches optimieren des importieren Dokumentes',
                'translation_domain' => false,
                'required' => false,
                'data' => true,
            ],
        );
    }
}
