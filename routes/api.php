<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MasterJobController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\ScReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WeeklyReportController;
use App\Http\Controllers\WorkPlanController;
use Illuminate\Support\Facades\Route;

// ─── Public ──────────────────────────────────────────────────
Route::post('/login',   [AuthController::class, 'login']);
Route::post('/scan-qr', [AuthController::class, 'scanQr']);

// ─── Authenticated ────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Locations
    Route::get('/location-types',              [LocationController::class, 'types']);
    Route::get('/locations',                   [LocationController::class, 'index']);
    Route::get('/locations/{locationName}',    [LocationController::class, 'show']);

    // Master Jobs
    Route::get('/master-jobs', [MasterJobController::class, 'index']);

    // Periods
    Route::get('/periods', [PeriodController::class, 'index']);

    // Checklist
    Route::get('/checklist',              [ChecklistController::class, 'index']);
    Route::post('/checklist/update',      [ChecklistController::class, 'update']);
    Route::post('/checklist/upload-doc',  [ChecklistController::class, 'uploadDoc']);
    Route::delete('/checklist/doc/{checklistDocumentation}',
        [ChecklistController::class, 'deleteDoc']);
    Route::get('/checklist/daily-summary',[ChecklistController::class, 'dailySummary']);

    // Issues
    Route::get('/issues',                         [IssueController::class, 'index']);
    Route::post('/issues',                        [IssueController::class, 'store']);
    Route::patch('/issues/{issue}/status',        [IssueController::class, 'updateStatus']);
    Route::post('/issues/{issue}/upload-doc',     [IssueController::class, 'uploadDoc']);
    Route::delete('/issues/doc/{issueDocumentation}',
        [IssueController::class, 'deleteDoc']);

    // SC Report
    Route::get('/sc-reports',                           [ScReportController::class, 'index']);
    Route::patch('/sc-reports/{scReport}',              [ScReportController::class, 'update']);
    Route::post('/sc-reports/{scReport}/upload-photo',  [ScReportController::class, 'uploadPhoto']);

    // Work Plans
    Route::get('/work-plans',              [WorkPlanController::class, 'index']);
    Route::post('/work-plans',             [WorkPlanController::class, 'store']);
    Route::patch('/work-plans/{workPlan}', [WorkPlanController::class, 'update']);
    Route::delete('/work-plans/{workPlan}',[WorkPlanController::class, 'destroy']);

    // Weekly Report
    Route::get('/weekly-report', [WeeklyReportController::class, 'index']);

    // ─── Admin only ───────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {

        // Location management
        Route::post('/location-types',                      [LocationController::class, 'storeType']);
        Route::patch('/location-types/{locationType}',      [LocationController::class, 'updateType']);
        Route::delete('/location-types/{locationType}',[LocationController::class, 'destroyType']);

        Route::post('/locations',                           [LocationController::class, 'store']);
        Route::patch('/locations/{locationName}',           [LocationController::class, 'update']);
        Route::delete('/locations/{locationName}',          [LocationController::class, 'destroy']);
    
        // Master Jobs management
        Route::post('/master-jobs',             [MasterJobController::class, 'store']);
        Route::patch('/master-jobs/{masterJob}',[MasterJobController::class, 'update']);
        Route::delete('/master-jobs/{masterJob}',[MasterJobController::class, 'destroy']);

        // Period management
        Route::post('/periods',            [PeriodController::class, 'store']);
        Route::patch('/periods/{period}',  [PeriodController::class, 'update']);
        Route::delete('/periods/{period}', [PeriodController::class, 'destroy']);

        // SC Report management
        Route::post('/sc-reports',              [ScReportController::class, 'store']);
        Route::delete('/sc-reports/{scReport}', [ScReportController::class, 'destroy']);

        // User management
        Route::get('/users',                   [UserController::class, 'index']);
        Route::post('/users',                  [UserController::class, 'store']);
        Route::patch('/users/{user}',          [UserController::class, 'update']);
        Route::post('/users/{user}/photo',     [UserController::class, 'updatePhoto']);
    });
});