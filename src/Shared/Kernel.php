<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared;

use Override;
use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

use function dirname;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    #[Override]
    public function getProjectDir(): string
    {
        return dirname(
            __DIR__,
            2,
        );
    }

    #[Override]
    protected function prepareContainer(ContainerBuilder $container): void
    {
        parent::prepareContainer($container);

        $container->registerAttributeForAutoconfiguration(
            AsTool::class,
            static function (ChildDefinition $definition, AsTool $attribute): void {
                $definition->addTag('llm_chain.tool', [
                    'name' => $attribute->name,
                    'description' => $attribute->description,
                    'method' => $attribute->method,
                ]);
            },
        );
    }
}
