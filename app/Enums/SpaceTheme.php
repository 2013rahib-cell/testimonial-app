<?php

declare(strict_types=1);

namespace App\Enums;

enum SpaceTheme: string
{
    case Light = 'light';
    case Dark = 'dark';
    case Indigo = 'indigo';
    case Emerald = 'emerald';
    case Sunset = 'sunset';

    public function label(): string
    {
        return match ($this) {
            self::Light => 'Classic Light',
            self::Dark => 'Modern Dark',
            self::Indigo => 'Indigo Bloom',
            self::Emerald => 'Emerald Fresh',
            self::Sunset => 'Sunset Glow',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Light => 'Clean, high-contrast crisp white background with zinc accents.',
            self::Dark => 'Sleek, focused dark slate layout that lets testimonials pop.',
            self::Indigo => 'Tech-forward vibrant indigo gradient with crisp modern accents.',
            self::Emerald => 'Fresh and trustworthy botanical green accents with vibrant energy.',
            self::Sunset => 'Warm, inspiring amber and rose gradient styling.',
        };
    }

    /**
     * Preview swatch CSS classes for the theme picker.
     */
    public function previewClasses(): string
    {
        return match ($this) {
            self::Light => 'bg-white border-zinc-200 text-zinc-900',
            self::Dark => 'bg-zinc-900 border-zinc-700 text-zinc-100',
            self::Indigo => 'bg-gradient-to-br from-indigo-900 via-slate-900 to-indigo-950 border-indigo-700 text-white',
            self::Emerald => 'bg-gradient-to-br from-emerald-950 via-zinc-900 to-teal-950 border-emerald-700 text-white',
            self::Sunset => 'bg-gradient-to-br from-orange-950 via-zinc-900 to-rose-950 border-orange-700 text-white',
        };
    }

    /**
     * Public page background classes.
     */
    public function pageBackground(): string
    {
        return match ($this) {
            self::Light => 'bg-zinc-50 text-zinc-900',
            self::Dark => 'bg-zinc-950 text-zinc-100',
            self::Indigo => 'bg-slate-950 text-slate-100',
            self::Emerald => 'bg-zinc-950 text-zinc-100',
            self::Sunset => 'bg-stone-950 text-stone-100',
        };
    }

    /**
     * Card container classes.
     */
    public function cardClasses(): string
    {
        return match ($this) {
            self::Light => 'bg-white border-zinc-200 shadow-xl text-zinc-900',
            self::Dark => 'bg-zinc-900/90 border-zinc-800 shadow-2xl text-zinc-100',
            self::Indigo => 'bg-slate-900/90 border-indigo-900/50 shadow-2xl text-slate-100',
            self::Emerald => 'bg-zinc-900/90 border-emerald-900/50 shadow-2xl text-zinc-100',
            self::Sunset => 'bg-stone-900/90 border-orange-900/50 shadow-2xl text-stone-100',
        };
    }

    /**
     * Accent button classes.
     */
    public function buttonClasses(): string
    {
        return match ($this) {
            self::Light => 'bg-zinc-900 hover:bg-zinc-800 text-white focus:ring-zinc-900/40',
            self::Dark => 'bg-white hover:bg-zinc-100 text-zinc-950 focus:ring-white/40',
            self::Indigo => 'bg-indigo-600 hover:bg-indigo-500 text-white focus:ring-indigo-500/40',
            self::Emerald => 'bg-emerald-600 hover:bg-emerald-500 text-white focus:ring-emerald-500/40',
            self::Sunset => 'bg-gradient-to-r from-orange-500 to-rose-500 hover:from-orange-600 hover:to-rose-600 text-white focus:ring-orange-500/40',
        };
    }
}
