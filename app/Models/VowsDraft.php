<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VowsDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'couple_id', 'status', 'tone', 'current_step', 'generated_text',
    ];

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
        return $this->hasMany(VowsAnswer::class)->orderBy('step_order');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
