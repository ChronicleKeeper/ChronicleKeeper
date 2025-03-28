<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.13',
    ],
    '@toast-ui/editor' => [
        'version' => '3.2.2',
    ],
    'prosemirror-model' => [
        'version' => '1.24.1',
    ],
    'prosemirror-view' => [
        'version' => '1.38.1',
    ],
    'prosemirror-transform' => [
        'version' => '1.10.3',
    ],
    'prosemirror-state' => [
        'version' => '1.4.3',
    ],
    'prosemirror-keymap' => [
        'version' => '1.2.2',
    ],
    'prosemirror-commands' => [
        'version' => '1.7.0',
    ],
    'prosemirror-inputrules' => [
        'version' => '1.4.0',
    ],
    'prosemirror-history' => [
        'version' => '1.4.1',
    ],
    'orderedmap' => [
        'version' => '2.1.1',
    ],
    'w3c-keyname' => [
        'version' => '2.2.8',
    ],
    'rope-sequence' => [
        'version' => '1.3.4',
    ],
    'prosemirror-view/style/prosemirror.min.css' => [
        'version' => '1.38.1',
        'type' => 'css',
    ],
    '@toast-ui/editor/dist/toastui-editor.css' => [
        'version' => '3.2.2',
        'type' => 'css',
    ],
    'tom-select' => [
        'version' => '2.4.3',
    ],
    '@orchidjs/sifter' => [
        'version' => '1.1.0',
    ],
    '@orchidjs/unicode-variants' => [
        'version' => '1.1.2',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.3',
        'type' => 'css',
    ],
    '@tabler/core/dist/css/tabler.min.css' => [
        'version' => '1.1.1',
        'type' => 'css',
    ],
    'bootstrap' => [
        'version' => '5.3.3',
    ],
    'marked' => [
        'version' => '15.0.7',
    ],
    'tom-select/dist/css/tom-select.bootstrap5.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
];
