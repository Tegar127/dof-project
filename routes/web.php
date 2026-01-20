<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');

Route::get('/admin', function () {
    return view('admin.index');
})->name('admin');

Route::get('/editor/{id?}', function ($id = 'new') {
    return view('editor.index', ['documentId' => $id]);
})->name('editor');