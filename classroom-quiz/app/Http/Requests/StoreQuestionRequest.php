<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isFaculty() ?? false; }

    public function rules(): array
    {
        return [
            'question_text' => ['required', 'string', 'max:1000'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'options' => ['required', 'array'],
            'options.A' => ['required', 'string', 'max:255'],
            'options.B' => ['required', 'string', 'max:255'],
            'options.C' => ['nullable', 'string', 'max:255'],
            'options.D' => ['nullable', 'string', 'max:255'],
            'correct_option' => ['required', 'in:A,B,C,D'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $correct = $this->input('correct_option');
            if (in_array($correct, ['A', 'B', 'C', 'D'], true) && blank($this->input("options.$correct"))) {
                $v->errors()->add('correct_option', 'The correct answer must be one of the options you filled in.');
            }
        });
    }
}
