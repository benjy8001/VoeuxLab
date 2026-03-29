<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinCoupleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->couple_id === null;
    }

    public function rules(): array
    {
        return [
            'invitation_code' => ['required', 'string', 'size:8', 'exists:couples,invitation_code'],
        ];
    }
}
