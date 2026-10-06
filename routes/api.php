<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn () => response()->json([
        'success' => true,
        'data' => ['service' => 'planning-service', 'status' => 'ok'],
    ]));
});
