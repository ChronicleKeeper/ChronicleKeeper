<?php

declare(strict_types=1);

use PhpLlm\LlmChainBundle\LlmChainBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\UX\Icons\UXIconsBundle;
use Symfony\UX\LiveComponent\LiveComponentBundle;
use Symfony\UX\StimulusBundle\StimulusBundle;
use Symfony\UX\Turbo\TurboBundle;
use Symfony\UX\TwigComponent\TwigComponentBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;

return [
    // Backend Packages
    FrameworkBundle::class => ['all' => true],
    MonologBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    LlmChainBundle::class => ['all' => true],
    TwigExtraBundle::class => ['all' => true],

    // Frontend Packages
    UXIconsBundle::class => ['all' => true],
    TwigComponentBundle::class => ['all' => true],
    LiveComponentBundle::class => ['all' => true],
    StimulusBundle::class => ['all' => true],
    TurboBundle::class => ['all' => true],

    // Development Packages
    DebugBundle::class => ['dev' => true],
    WebProfilerBundle::class => ['dev' => true, 'test' => true],
];
