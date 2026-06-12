<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallSession extends Model
{
    protected $fillable = ['call_sid', 'contact_id', 'conversation_history', 'turn_count'];

    protected $casts = [
        'conversation_history' => 'array',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}