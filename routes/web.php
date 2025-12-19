<?php

use App\Services\Contracts\SetupServiceInterface;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Admin panel routes will be handled by Vue.js SPA
|
*/

// Home route - redirect to setup if not complete
Route::get('/', function (SetupServiceInterface $setupService) {
    if (! $setupService->isSetupComplete()) {
        return redirect('/setup');
    }
    return view('app');
});

// Setup wizard route - only accessible if setup is not complete
Route::get('/setup', function (SetupServiceInterface $setupService) {
    if ($setupService->isSetupComplete()) {
        return redirect('/');
    }
    return view('setup');
})->name('setup');

// Login route
Route::get('/login', function (SetupServiceInterface $setupService) {
    if (! $setupService->isSetupComplete()) {
        return redirect('/setup');
    }
    return view('app');
})->name('login');

// Catch-all for Vue.js SPA routing (protected routes)
Route::get('/{any}', function (SetupServiceInterface $setupService) {
    if (! $setupService->isSetupComplete()) {
        return redirect('/setup');
    }
    return view('app');
})->where('any', '^(?!api).*$');
