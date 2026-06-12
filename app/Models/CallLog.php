<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallLog extends Model
{
    protected $fillable = [
        'contact_id', 'call_sid', 'result',
        'notes', 'transcript', 'duration', 'called_at'
    ];

    protected $casts = [
        'called_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function getResultLabelAttribute(): string
    {
        return match($this->result) {
            'answered'       => '✅ Répondu',
            'voicemail'      => '📭 Messagerie',
            'no_answer'      => '📵 Pas de réponse',
            'busy'           => '🔴 Occupé',
            'failed'         => '❌ Échec',
            'interested'     => '🌟 Intéressé',
            'not_interested' => '👎 Pas intéressé',
            default          => $this->result,
        };
    }
}