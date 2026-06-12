<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferPrompt extends Model
{
    protected $fillable = ['name', 'system_prompt', 'opening_message', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getActive(): ?self
    {
        return self::where('is_active', true)->first();
    }
}