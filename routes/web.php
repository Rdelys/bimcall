<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallController;
use App\Http\Controllers\TwilioWebhookController;

// ── Interface principale ──────────────────────────────────────────
Route::get('/', [CallController::class, 'index'])->name('home');

// Appels
Route::post('/call', [CallController::class, 'call'])->name('call.single');
Route::post('/call/next', [CallController::class, 'callNext'])->name('call.next');
Route::post('/import', [CallController::class, 'import'])->name('import');
Route::get('/logs', [CallController::class, 'logs'])->name('calls.logs');
Route::get('/api/call-status', [CallController::class, 'callStatus'])->name('call.status.api');

// Contacts
Route::get('/contacts', [CallController::class, 'contacts'])->name('contacts.index');
Route::post('/contacts/{contact}/reset', [CallController::class, 'resetContact'])->name('contacts.reset');
Route::delete('/contacts/{contact}', [CallController::class, 'deleteContact'])->name('contacts.delete');

// Prompts
Route::get('/prompts', [CallController::class, 'prompts'])->name('prompts.index');
Route::post('/prompts', [CallController::class, 'savePrompt'])->name('prompts.save');
Route::post('/prompts/{prompt}/activate', [CallController::class, 'activatePrompt'])->name('prompts.activate');

// ── Webhooks Twilio (pas de CSRF) ─────────────────────────────────
Route::prefix('twilio')->name('twilio.voice.')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->group(function () {
    Route::post('/answer',  [TwilioWebhookController::class, 'answer'])->name('answer');
    Route::post('/respond', [TwilioWebhookController::class, 'respond'])->name('respond');
    Route::post('/status',  [TwilioWebhookController::class, 'status'])->name('status');
    Route::post('/amd',     [TwilioWebhookController::class, 'amd'])->name('amd');
});