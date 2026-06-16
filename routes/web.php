<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallController;
use App\Http\Controllers\TelnyxWebhookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CallNoteController;
use App\Http\Middleware\EnsureAuthenticated;

// ── Authentification ──────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Interface principale (protégée) ────────────────────────────────
Route::middleware([EnsureAuthenticated::class])->group(function () {

    Route::get('/', [CallController::class, 'index'])->name('home');

    Route::post('/call', [CallController::class, 'call'])->name('call.single');
    Route::post('/call/next', [CallController::class, 'callNext'])->name('call.next');
    Route::post('/import', [CallController::class, 'import'])->name('import');
    Route::get('/logs', [CallController::class, 'logs'])->name('calls.logs');
    Route::get('/api/call-status', [CallController::class, 'callStatus'])->name('call.status.api');

    // Détail d'un appel + notes manuelles
    Route::get('/calls/{callLog}', [CallNoteController::class, 'show'])->name('calls.show');
    Route::put('/calls/{callLog}', [CallNoteController::class, 'update'])->name('calls.note.update');

    Route::get('/contacts', [CallController::class, 'contacts'])->name('contacts.index');
    Route::post('/contacts/{contact}/reset', [CallController::class, 'resetContact'])->name('contacts.reset');
    Route::delete('/contacts/{contact}', [CallController::class, 'deleteContact'])->name('contacts.delete');

    Route::get('/prompts', [CallController::class, 'prompts'])->name('prompts.index');
    Route::post('/prompts', [CallController::class, 'savePrompt'])->name('prompts.save');
    Route::post('/prompts/{prompt}/activate', [CallController::class, 'activatePrompt'])->name('prompts.activate');

});

// ── Webhook Telnyx (public, pas de CSRF) ───────────────────────────
Route::post('/telnyx/webhook', [TelnyxWebhookController::class, 'handle'])
    ->name('telnyx.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);