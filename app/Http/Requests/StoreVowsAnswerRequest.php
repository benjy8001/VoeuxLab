<?php

namespace App\Http\Requests;

use App\Support\VowsQuestions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVowsAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->couple_id !== null
            && \App\Models\VowsDraft::where('user_id', $this->user()->id)
                ->where('couple_id', $this->user()->couple_id)
                ->exists();
    }

    public function rules(): array
    {
        return [
            'question_key' => ['required', 'string', Rule::in(VowsQuestions::validKeys())],
            'answer_text'  => ['required', 'string', 'max:5000'],
            'current_step' => ['required', 'integer', 'min:1', 'max:' . VowsQuestions::count()],
            'final'        => ['boolean'],
        ];
    }
}
