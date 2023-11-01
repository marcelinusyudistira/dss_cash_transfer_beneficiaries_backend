<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Analysis\Criteria\CriteriaComparisonController;
use App\Http\Controllers\Analysis\Criteria\CriteriaPriorityController;
use App\Http\Controllers\Analysis\Alternative\AlternativeComparisonController;
use App\Http\Controllers\Analysis\Alternative\AlternativePriorityController;
use App\Http\Controllers\Analysis\RecommendationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::middleware('api.is_admin')->group(function () {
    Route::resource('criteria', CriteriaController::class);
    Route::resource('alternative', AlternativeController::class);

    //route pengumuman
    //Route::resource('pengumuman', PengumumanController::class);
    Route::get('pengumuman', [PengumumanController::class, 'index']);
    
    Route::get('pengumuman/{id}', [PengumumanController::class, 'show']);
    Route::post('pengumuman', [PengumumanController::class, 'store']);
    Route::put('pengumuman/{id}', [PengumumanController::class, 'update']);
    Route::delete('pengumuman/{id}', [PengumumanController::class, 'destroy']);

    //route saran
    Route::get('saran', [SaranController::class, 'index']);
    Route::get('saran/{id}', [SaranController::class, 'show']);
    Route::put('saran/{id}', [SaranController::class, 'verifikasi']);
    Route::delete('saran/{id}', [SaranController::class, 'destroy']);

    //route analisa kriteria perbandingan dan prioritas
    Route::get('criteriaComparisons', [CriteriaComparisonController::class, 'index']);
    Route::post('criteriaComparisons', [CriteriaComparisonController::class, 'createComparisons']);
    Route::post('criteriaComparisonsReset', [CriteriaComparisonController::class, 'resetComparison']);
    Route::put('criteriaComparisons/{id}', [CriteriaComparisonController::class, 'store']);
    Route::get('criteriaPriority', [CriteriaPriorityController::class, 'index']);
    //Route::post('criteriaPriority', [CriteriaPriorityController::class, 'normalization']);
    Route::post('criteriaPriority', [CriteriaPriorityController::class, 'normalisasi']);
    Route::post('criteriaReset', [CriteriaComparisonController::class, 'resetKriteria']);

    //route analisa alternatif perbandingan dan prioritas
    Route::get('alternativeComparisons', [AlternativeComparisonController::class, 'index']);
    Route::post('alternativeComparisons', [AlternativeComparisonController::class, 'createComparisonsAl']);
    Route::post('alternativeComparisonsReset', [AlternativeComparisonController::class, 'resetComparison']);
    Route::put('alternativeComparisons/{id}', [AlternativeComparisonController::class, 'store']);
    Route::get('alternativePriority', [AlternativePriorityController::class, 'index']);
    //Route::post('alternativePriority', [AlternativePriorityController::class, 'normalization']);
    Route::post('alternativePriority', [AlternativePriorityController::class, 'hitungPrioritas']);
    Route::post('alternativeReset', [AlternativeComparisonController::class, 'resetAlternatif']);

    //route hasil rekomendasi
    Route::get('recommendation', [RecommendationController::class, 'index']);
    Route::post('recommendation', [RecommendationController::class, 'calculate']);

    Route::get('user', [UserController::class, 'index']);
    Route::post('addAdmin', [UserController::class, 'addAdmin']);

    Route::get('getDashboard', [UserController::class, 'getDashboard']);
});

Route::middleware('auth:api')->group(function () {
    //route pengumuman
    Route::get('pengumuman', [PengumumanController::class, 'index']);
    Route::get('pengumuman/{id}', [PengumumanController::class, 'show']);

    //route saran
    Route::get('saran', [SaranController::class, 'index']);
    Route::get('saran/{id}', [SaranController::class, 'show']);
    Route::post('saran/{id_user}', [SaranController::class, 'store']);
    Route::put('saranEdit/{id}', [SaranController::class, 'update']);
    Route::delete('saran/{id}', [SaranController::class, 'destroy']);
    Route::get('getAdmin/{id}', [UserController::class, 'show']);
    Route::get('getSaran', [SaranController::class, 'saranUser']);

    //route profile
    Route::get('user/{id}', [UserController::class, 'show']);
    Route::put('userEdit/{id}', [UserController::class, 'update']);
    Route::put('gantiPassword', [UserController::class, 'gantiPassword']);
});

Route::get('pengumumanUser', [PengumumanUserController::class, 'index']);
Route::get('pengumumanUser/{id}', [PengumumanUserController::class, 'show']);
Route::get('download/{id}', [PengumumanController::class, 'downloadFile']);


