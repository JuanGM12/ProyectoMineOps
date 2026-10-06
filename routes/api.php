<?php

use App\Presentation\Http\Controllers\Api\V1\ActivityController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn () => response()->json([
        'success' => true,
        'data' => ['service' => 'planning-service', 'status' => 'ok'],
    ]));

    Route::prefix('activities')->group(function (): void {
        Route::get('/pending', [ActivityController::class, 'pending']);
        Route::get('/overdue', [ActivityController::class, 'overdue']);
        Route::get('/responsible/{responsibleId}', [ActivityController::class, 'responsible'])
            ->whereUuid('responsibleId');
        Route::post('/', [ActivityController::class, 'store']);
        Route::get('/', [ActivityController::class, 'index']);
        Route::get('/{id}', [ActivityController::class, 'show'])->whereUuid('id');
        Route::put('/{id}', [ActivityController::class, 'update'])->whereUuid('id');
        Route::patch('/{id}/start', [ActivityController::class, 'start'])->whereUuid('id');
        Route::patch('/{id}/complete', [ActivityController::class, 'complete'])->whereUuid('id');
        Route::patch('/{id}/cancel', [ActivityController::class, 'cancel'])->whereUuid('id');
    });
});
