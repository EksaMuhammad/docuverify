<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ResearchController;
use App\Livewire\ActivityLogs;
use App\Livewire\DocumentManagement;
use App\Livewire\RegisterDocument;
use App\Livewire\ResearchDashboard;
use App\Livewire\TransactionHistory;
use App\Livewire\VerificationHistory;
use App\Livewire\VerifyDocument;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/verify', VerifyDocument::class)->name('verify');

Route::get('/about', function () {
    return view('pages.about');
})->name('about');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Document Management & Registration
    Route::get('/documents/create', RegisterDocument::class)->name('documents.create');
    Route::get('/documents', DocumentManagement::class)->name('documents.index');
    Route::get('/documents/{id}', [DocumentController::class, 'show'])->name('documents.show');

    // Auditing & Logs
    Route::get('/transactions', TransactionHistory::class)->name('transactions.index');
    Route::get('/verification-logs', VerificationHistory::class)->name('verification-logs.index');
    Route::get('/activity-logs', ActivityLogs::class)->name('activity-logs.index');

    // Research & Skripsi Evaluation Panel
    Route::get('/research', ResearchDashboard::class)->name('research.index');
    Route::get('/research/export-verifications', [ResearchController::class, 'exportVerificationCsv'])->name('research.export-verifications');
    Route::get('/research/export-transactions', [ResearchController::class, 'exportTransactionsCsv'])->name('research.export-transactions');
});
