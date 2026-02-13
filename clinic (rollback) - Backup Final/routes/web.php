<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Admin\LabTestController;
use App\Http\Controllers\Admin\ConsultationController;
use App\Http\Controllers\Admin\InventoryLogController;
use App\Http\Controllers\ChatbotController;

Route::get('/', fn () => redirect('login'));

// Chatbot routes - MOVED OUTSIDE auth middleware for public access
Route::get('/chatbot', function () {
    return view('chatbot.index');
})->name('chatbot.view');

Route::post('/chatbot/ask', [ChatbotController::class, 'ask'])->name('chatbot.ask');

// Additional chatbot endpoints from your controller
Route::post('/chatbot/send', [ChatbotController::class, 'sendMessage'])->name('chatbot.send');
Route::post('/chatbot/new-conversation', [ChatbotController::class, 'newConversation'])->name('chatbot.new');
Route::get('/chatbot/history', [ChatbotController::class, 'history'])->name('chatbot.history');
Route::get('/chatbot/conversation/{id}', [ChatbotController::class, 'getConversation'])->name('chatbot.conversation');
Route::get('/chatbot/follow-up-questions', [ChatbotController::class, 'getFollowUpQuestions'])->name('chatbot.followup');

// Wrap all auth-protected routes in `prevent-back-history`
Route::middleware(['auth', 'prevent-back-history'])->group(function () {

    // OTP routes (for users who haven't verified yet)
    Route::middleware('otp.not.verified')->group(function () {
        Route::get('/otp-verify', [OtpController::class, 'show'])->name('otp.verify');
        Route::post('/otp-verify', [OtpController::class, 'verify'])->name('otp.submit');
        Route::post('/otp-cancel', [OtpController::class, 'cancel'])->name('otp.cancel');
    });

    // Admin-only
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::prefix('admin/inventory')->name('admin.inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::post('/', [InventoryController::class, 'store'])->name('store');
            Route::put('/{inventory}', [InventoryController::class, 'update'])->name('update');
            Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->name('destroy');
            Route::patch('/{inventory}/archive', [InventoryController::class, 'archive'])->name('archive');
            Route::get('/export/csv', [InventoryController::class, 'exportCsv'])->name('export.csv');
        });

        Route::prefix('admin/consultation')->name('admin.consultation.')->group(function () {
            Route::get('/', [ConsultationController::class, 'index'])->name('index');
            Route::post('/', [ConsultationController::class, 'store'])->name('store');
            Route::get('/{consultation}', [ConsultationController::class, 'show'])->name('show');
            Route::delete('/{consultation}', [ConsultationController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('admin/labtests')->name('admin.labtests.')->group(function () {
            Route::get('/', [LabTestController::class, 'index'])->name('index');
            Route::post('/', [LabTestController::class, 'store'])->name('store');
            Route::get('/{test:test_code}', [LabTestController::class, 'show'])->name('show');
            Route::get('/{test:test_code}/edit', [LabTestController::class, 'edit'])->name('edit');
            Route::put('/{test:test_code}', [LabTestController::class, 'update'])->name('update');
            Route::delete('/{test:test_code}', [LabTestController::class, 'destroy'])->name('destroy');
        });

        Route::get('/admin/inventory/logs', [InventoryLogController::class, 'index'])->name('admin.inventory.logs');
    });

    // User-only
    Route::middleware('role:user')->group(function () {
        Route::get('/user/dashboard', fn () => view('user.dashboard'))->name('user.dashboard');
    });

}); 

require __DIR__.'/auth.php';