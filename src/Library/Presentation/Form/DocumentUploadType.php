<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Form;

use ChronicleKeeper\Library\Application\Service\Document\Importer;
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
            'optimize',
            CheckboxType::class,
            [
                'label' => 'Automatisches optimieren des importieren Dokumentes',
                'translation_domain' => false,
                'required' => false,
                'empty_data' => true,
            ],
        );
    }
}
