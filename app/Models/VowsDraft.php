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
        'user_id', 'couple_id', 'status', 'tone', 'current_step', 'generated_text', 'shared_at',
    ];

    protected $casts = [
        'shared_at' => 'datetime',
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

    /**
     * Vérifie si les vœux du partenaire sont lisibles par l'utilisateur propriétaire de ce draft.
     * Conditions : les deux ont partagé, ou la date de cérémonie est passée.
     */
    public function isReadableByPartner(Couple $couple): bool
    {
        // Déverrouillage automatique après la date de cérémonie
        if ($couple->ceremony_date && $couple->ceremony_date->lte(now())) {
            return true;
        }

        // Partage mutuel requis
        if (!$couple->spouse_2_id) {
            return false;
        }

        $partnerId = $couple->spouse_1_id === $this->user_id
            ? $couple->spouse_2_id
            : $couple->spouse_1_id;

        $partnerDraft = self::where('user_id', $partnerId)
            ->where('couple_id', $couple->id)
            ->first();

        return $this->shared_at !== null && $partnerDraft?->shared_at !== null;
    }
}
