<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web;

use DZunke\NovDoc\Domain\LLMExtension\Store\Filesystem\Store as FilesystemStore;
use PhpLlm\LlmChain\Store\StoreInterface;
use PhpLlm\LlmChain\Store\VectorStoreInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

use function dirname;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getProjectDir(): string
    {
        return dirname(
            __DIR__,
            2,
        );
    }

    protected function prepareContainer(ContainerBuilder $container): void
    {
        parent::prepareContainer($container);

        // Register the Filesystem Store as a Vector Store - the bundle is hardly bound to stuff being inside LlmChain
        $definition = new ChildDefinition(FilesystemStore::class);
        $definition->setArgument('$filepath', $this->getProjectDir() . '/var/embeddings.json');

        $container->setDefinition('llm_chain.store.filesystem', $definition);

        $container->setAlias(VectorStoreInterface::class, 'llm_chain.store.filesystem');
        $container->setAlias(StoreInterface::class, 'llm_chain.store.filesystem');
    }
}
