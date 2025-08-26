<?php

use App\Filament\Guest\Pages\PublicFeedback;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.guest.pages.public');
});

// Route::get('/f/{uuid PublicFeedback::class)->name('feedback.public');
