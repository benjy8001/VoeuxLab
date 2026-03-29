<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Couple extends Model
{
    use HasFactory;

    protected $fillable = [
        'spouse_1_id', 'spouse_2_id', 'officiant_id',
        'invitation_code', 'ceremony_date', 'ceremony_location',
    ];

    protected $casts = [
        'ceremony_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Couple $couple) {
            $couple->invitation_code = strtoupper(Str::random(8));
        });
    }

    public function spouse1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'spouse_1_id');
    }

    public function spouse2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'spouse_2_id');
    }

    public function officiant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officiant_id');
    }

    public function vowsDrafts(): HasMany
    {
        return $this->hasMany(VowsDraft::class);
    }

    public function ceremony(): HasOne
    {
        return $this->hasOne(Ceremony::class);
    }

    public function isFull(): bool
    {
        return $this->spouse_2_id !== null;
    }
}
