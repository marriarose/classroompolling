<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinClassroomRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isStudent() ?? false; }

    public function rules(): array
    {
        return [
            'join_code' => ['required', 'string', 'size:6', 'alpha_num'],
            'display_name' => ['required', 'string', 'max:60'],
        ];
    }
}
