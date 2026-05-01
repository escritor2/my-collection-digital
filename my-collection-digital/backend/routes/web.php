<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Bem-vindo à API do Meu Acervo Digital',
        'status' => 'online',
        'laravel_version' => Application::VERSION,
        'php_version' => PHP_VERSION,
    ]);
})->name('home');
