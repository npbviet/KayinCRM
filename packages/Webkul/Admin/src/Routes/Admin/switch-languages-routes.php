<?php

use Illuminate\Support\Facades\Route;

Route::post('/set-locale', function (\Illuminate\Http\Request $request) {
    $locale = $request->input('locale', 'en');
    session(['locale' => $locale]);
    app()->setLocale($locale);

    return back();
})->name('admin.set-locale');
