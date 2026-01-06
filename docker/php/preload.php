<?php

    require __DIR__ . '/vendor/autoload.php';

    /**
     * Directories to preload.
     * Only include class files, traits, jobs, events, models.
     * Avoid routes, views, bootstrap cache, tests, and code that executes on include.
     */
    $directories = [
        __DIR__ . '/app/Models',
        __DIR__ . '/app/Traits',
        __DIR__ . '/app/Jobs',
        __DIR__ . '/app/Events',
        __DIR__ . '/app/Listeners',
        __DIR__ . '/app/Services',
        __DIR__ . '/vendor/laravel/framework/src/Illuminate',
        __DIR__ . '/vendor/symfony',
        __DIR__ . '/vendor/psr',
    ];

    function preloadDirectory(string $dir)
    {
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

        foreach ($rii as $file) {
            if (!$file->isDir() && $file->getExtension() === 'php') {
                // Only require class/interface/trait files
                $content = file_get_contents($file->getPathname());
                if (preg_match('/^\s*(namespace\s+|abstract\s+|class\s+|interface\s+|trait\s+)/m', $content)) {
                    require_once $file->getPathname();
                }
            }
        }
    }

    foreach ($directories as $dir) {
        if (is_dir($dir)) {
            preloadDirectory($dir);
        }
    }
