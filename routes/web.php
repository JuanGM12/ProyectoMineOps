<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/api/documentation');
Route::view('/api/documentation', 'swagger');
Route::get('/api/openapi.yaml', fn () => response()->file(base_path('docs/openapi.yaml'), [
    'Content-Type' => 'application/yaml',
]));
