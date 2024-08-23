<?php

declare(strict_types=1);

use PhpLlm\LlmChainBundle\LlmChainBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;

return [
    FrameworkBundle::class => ['all' => true],
    MonologBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    LlmChainBundle::class => ['all' => true],

    DebugBundle::class => ['dev' => true],
    WebProfilerBundle::class => ['dev' => true],
];
