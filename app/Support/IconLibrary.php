<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

final class IconLibrary
{
    private const string FALLBACK = 'sparkles';

    public static function sanitize(string $name): string
    {
        if (! preg_match('/^[a-z0-9-]+$/', $name)) {
            return self::FALLBACK;
        }

        return is_file(self::path($name)) ? $name : self::FALLBACK;
    }

    /**
     * Every vendored Lucide icon inlined once as an SVG <symbol>, so the
     * rest of the app (Blade or Alpine, rendered server-side or live in the
     * browser) can reference any icon cheaply with <use href="#icon-name">.
     */
    public static function spriteSymbols(): string
    {
        $symbols = collect(File::files(resource_path('svg/icons')))
            ->map(function ($file) {
                $name = $file->getFilenameWithoutExtension();
                $inner = preg_replace('/^.*?<svg[^>]*>|<\/svg>\s*$/s', '', $file->getContents());

                return "<symbol id=\"icon-{$name}\" viewBox=\"0 0 24 24\">{$inner}</symbol>";
            })
            ->implode('');

        return $symbols;
    }

    private static function path(string $name): string
    {
        return resource_path("svg/icons/{$name}.svg");
    }
}
