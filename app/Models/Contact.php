<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $fillable = ['name', 'phone', 'company', 'status'];

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    public function lastCall()
    {
        return $this->callLogs()->latest()->first();
    }
}