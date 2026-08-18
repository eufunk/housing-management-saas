<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | This project follows the modern Inertia/Vue convention of a lowercase
    | resources/js/pages directory (see docs/architecture.md), not the
    | package's own default resources/js/Pages. Both the root-level and the
    | testing-only page_paths need the override: the package's own comments
    | note that in a future release the root-level option will be used for
    | testing too, so both are kept in sync now rather than only patching
    | whichever one currently causes failures.
    |
    | On this project's case-INsensitive Windows/WSL dev filesystem the
    | mismatch is invisible; a case-sensitive Linux CI runner (correctly)
    | fails `assertInertia(...)->component(...)` without this override.
    |
    */

    'page_paths' => [
        resource_path('js/pages'),
    ],

    // Laravel's mergeConfigFrom() only merges top-level keys of this array —
    // 'testing' below fully replaces the package's default 'testing' array
    // rather than being deep-merged with it, so every key must be repeated
    // here even though only page_paths actually differs from the default.
    'testing' => [

        'ensure_pages_exist' => true,

        'page_paths' => [
            resource_path('js/pages'),
        ],

        'page_extensions' => [
            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',
        ],

    ],

];
