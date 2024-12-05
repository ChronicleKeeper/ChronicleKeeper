<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Benchmark;

use ChronicleKeeper\Shared\Kernel;

trait UseSymfonyKernel
{
    private function getKernel(): Kernel
    {
        $kernel = new Kernel('bench', false);
        $kernel->boot();

        return $kernel;
    }
}
