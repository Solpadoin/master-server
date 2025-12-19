<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Admin panel routes will be handled by Vue.js SPA
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Catch-all for Vue.js SPA routing
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
