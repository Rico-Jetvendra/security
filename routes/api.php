<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    ActivityLogsController
};

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('logger.auth')->group(function () {
    Route::post('/logs', [ActivityLogsController::class, 'store']);
});
