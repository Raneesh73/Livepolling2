<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PollController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [PollController::class, 'index'])->name('dashboard');
    Route::get('/poll/{id}', [PollController::class, 'show'])->name('poll.show');
    Route::post('/vote', [PollController::class, 'vote'])->name('poll.vote');
    Route::get('/poll/results/{id}', [PollController::class, 'results'])->name('poll.results');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin/polls', [AdminController::class, 'storePoll'])->name('admin.poll.store');
    Route::post('/admin/release-ip', [AdminController::class, 'releaseIp'])->name('admin.releaseIp');
    Route::get('/admin/history/{poll_id}/{ip_address}', [AdminController::class, 'history'])->name('admin.history');
});
