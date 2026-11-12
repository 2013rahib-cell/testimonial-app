<?php

declare(strict_types=1);

use App\Livewire\Dashboard;
use App\Livewire\Embed\Generator as EmbedGenerator;
use App\Livewire\Public\SubmitTestimonial;
use App\Livewire\Public\WallOfLoveEmbed;
use App\Livewire\Spaces\Create as SpaceCreate;
use App\Livewire\Spaces\Edit as SpaceEdit;
use App\Livewire\Spaces\Index as SpaceIndex;
use App\Livewire\Spaces\Inbox as SpaceInbox;
use App\Livewire\Spaces\Success as SpaceSuccess;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Public collection page for customers
Route::get('s/{slug}', SubmitTestimonial::class)->name('spaces.submit');

// Public Wall of Love embed endpoint
Route::get('embed/wall-of-love/{slug}', WallOfLoveEmbed::class)->name('wall-of-love.embed');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('embeds', EmbedGenerator::class)->name('embed.generator');

    Route::prefix('spaces')->name('spaces.')->group(function () {
        Route::get('/', SpaceIndex::class)->name('index');
        Route::get('/create', SpaceCreate::class)->name('create');
        Route::get('/{space:slug}/edit', SpaceEdit::class)->name('edit');
        Route::get('/{space:slug}/success', SpaceSuccess::class)->name('success');
        Route::get('/{space:slug}/inbox', SpaceInbox::class)->name('inbox');
    });
});

require __DIR__.'/settings.php';
