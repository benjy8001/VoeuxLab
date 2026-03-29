<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class OfficiantDraft extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'couple_id', 'status', 'current_step', 'generated_text'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function couple(): BelongsTo
    {
        return $this->belongsTo(Couple::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(OfficiantAnswer::class)->orderBy('step_order');
    }
}
