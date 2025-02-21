<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateTeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return  $this->user()->can('update', $this->teacher);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes','required', 'string', 'max:191'],
            'middle_name' => ['nullable', 'string', 'max:191'],
            'last_name' => ['sometimes','required', 'string', 'max:191'],
            'phone_number' => [
                'sometimes','required', 'string', 'max:191',
                Rule::unique('users', 'phone_number')
                ->ignore($this->teacher->user)
            ],
            'registration_no' => [
                'sometimes','required', 'string', 'max:191', 
                Rule::unique('teachers', 'registration_no')
                ->ignore($this->teacher)
            ],
        ];
    }
    
    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [

        ];
    }
}
