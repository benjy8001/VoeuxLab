<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ceremony extends Model
{
    use HasFactory;

    // notes_officiant = notes globales de préparation de l'officiant (≠ notes par bloc stockées dans program[].notes_officiant)
    protected $fillable = ['couple_id', 'program', 'notes_officiant', 'status'];

    protected $casts = ['program' => 'array'];

    public function couple(): BelongsTo
    {
        return $this->belongsTo(Couple::class);
    }
}
