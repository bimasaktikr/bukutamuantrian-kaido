<?php

use App\Filament\Guest\Pages\PublicFeedback;
use Illuminate\Support\Facades\Route;
use Opcodes\LogViewer\Facades\LogViewer;


Route::get('/', function () {
    return redirect()->route('filament.guest.pages.public');
});


// Route::get('/f/{uuid PublicFeedback::class)->name('feedback.public');
