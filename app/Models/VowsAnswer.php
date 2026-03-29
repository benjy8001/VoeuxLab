<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VowsAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'vows_draft_id', 'question_key', 'answer_text', 'step_order',
    ];

    public function draft(): BelongsTo
    {
        return $this->belongsTo(VowsDraft::class, 'vows_draft_id');
    }
}
