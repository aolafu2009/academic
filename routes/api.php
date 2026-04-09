<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::middleware('auth:api')->post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function () {
    Route::get('/courses', [App\Http\Controllers\Api\CourseController::class, 'index']);
    Route::post('/courses', [App\Http\Controllers\Api\CourseController::class, 'store']);
    Route::get('/students', [App\Http\Controllers\Api\StudentController::class, 'index']);

    Route::get('/bills', [App\Http\Controllers\Api\BillController::class, 'index']);
    Route::post('/bills', [App\Http\Controllers\Api\BillController::class, 'store']);
    Route::post('/bills/{billNo}/pay', [App\Http\Controllers\Api\BillController::class, 'pay']);
});
