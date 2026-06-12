<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallController;
use App\Http\Controllers\TwilioWebhookController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\EnsureAuthenticated;
use App\Http\Controllers\CallNoteController;

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

// ── Webhooks Twilio (publics) ──────────────────────────────────────
Route::prefix('twilio')->name('twilio.voice.')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->group(function () {
    Route::post('/answer',  [TwilioWebhookController::class, 'answer'])->name('answer');
    Route::post('/respond', [TwilioWebhookController::class, 'respond'])->name('respond');
    Route::post('/status',  [TwilioWebhookController::class, 'status'])->name('status');
    Route::post('/amd',     [TwilioWebhookController::class, 'amd'])->name('amd');
});