<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone');
            $table->string('company')->nullable();
            $table->enum('status', ['pending', 'calling', 'done', 'failed'])->default('pending');
            $table->timestamps();
        });

        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->string('call_sid')->nullable(); // Twilio Call SID
            $table->enum('result', [
                'answered',       // Décroché et conversation
                'voicemail',      // Messagerie vocale
                'no_answer',      // Pas de réponse
                'busy',           // Occupé
                'failed',         // Échec technique
                'interested',     // Intéressé (marqué en fin d'appel)
                'not_interested', // Pas intéressé
            ])->default('no_answer');
            $table->text('notes')->nullable();       // Notes de l'appel
            $table->text('transcript')->nullable();  // Transcription de la conversation
            $table->integer('duration')->default(0); // Durée en secondes
            $table->timestamp('called_at')->nullable();
            $table->timestamps();
        });

        Schema::create('call_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('call_sid')->unique();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->json('conversation_history')->nullable(); // Historique pour Claude
            $table->integer('turn_count')->default(0);
            $table->timestamps();
        });

        Schema::create('offer_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('system_prompt'); // Instructions pour Claude
            $table->text('opening_message'); // Premier message à dire
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_sessions');
        Schema::dropIfExists('call_logs');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('offer_prompts');
    }
};