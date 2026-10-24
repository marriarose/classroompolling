<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Only ids are accepted. Score/is_correct/points from the browser are ignored. */
class AnswerRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isStudent() ?? false; }

    public function rules(): array
    {
        return [
            'question_id' => ['required', 'integer'],
            'option_id' => ['required', 'integer'],
        ];
    }
}
