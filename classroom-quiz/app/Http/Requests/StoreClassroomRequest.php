<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassroomRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isFaculty() ?? false; }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:100']];
    }
}
