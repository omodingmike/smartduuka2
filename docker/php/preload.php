<?php

    /**
     * Laravel 12 + PHP 8.4 Optimized Preloader
     * Prevents "unlinked" and "already declared" errors.
     */

// 1. EXIT IF CLI: Preloading is for the web server (FPM), not Artisan commands.
    if ( PHP_SAPI === 'cli' ) {
        return;
    }

    require_once '/app/vendor/autoload.php';

// 2. FORCE LOAD THE FOUNDATION
// This solves "Unknown parent" or "Unknown interface" errors by forcing
// the autoloader to link them before we scan the rest of the files.
    $mustLink = [
        // Core Interfaces & Traits
        \Illuminate\Contracts\Support\Htmlable::class ,
        \Illuminate\Contracts\Support\MessageProvider::class ,
        \Illuminate\Contracts\Support\MessageBag::class ,
        \Illuminate\Contracts\Support\ValidatedData::class ,
        \Illuminate\Contracts\Database\Eloquent\SupportsPartialRelations::class ,
        \Illuminate\Database\Concerns\BuildsQueries::class ,
        \Illuminate\Support\Traits\ReflectsClosures::class ,
        \Illuminate\Support\HtmlString::class ,
        \Carbon\Carbon::class , // Fixes Carbon warnings

        // Core Parents
        \Illuminate\Database\Eloquent\Model::class ,
        \Illuminate\Database\Eloquent\Relations\Relation::class ,
        \Illuminate\Foundation\Auth\User::class ,
        \Filament\PanelProvider::class ,
    ];

    foreach ( $mustLink as $class ) {
        // Triggers the autoloader to link the class/interface correctly
        class_exists( $class ) || interface_exists( $class ) || trait_exists( $class );
    }

// 3. SCAN AND COMPILE
    $directories = [
        '/app/vendor/laravel/framework/src/Illuminate/Support' ,
        '/app/vendor/laravel/framework/src/Illuminate/Database/Eloquent' ,
        '/app/app/Models' ,
        '/app/app/Providers' ,
    ];

    foreach ( $directories as $directory ) {
        if ( ! is_dir( $directory ) ) continue;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $directory ) ,
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ( $iterator as $file ) {
            if ( $file->isFile() && $file->getExtension() === 'php' ) {
                $path = $file->getPathname();

                // Skip tests and console commands
                if ( str_contains( $path , 'Testing' ) || str_contains( $path , 'Console' ) ) continue;

                // Use @ to silence "already declared" warnings from files
                // the autoloader might have already touched.
                @opcache_compile_file( $path );
            }
        }
    }