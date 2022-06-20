<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user->id === auth()->id() || User::TYPE_SLUGS[auth()->user()->type] === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => ['required', 'string', 'min:3', 'max:255'],
            'last_name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email:rfc,dns', Rule::unique('users', 'email')->ignore(auth()->id())],
            'gender' => ['required', 'in:male,female'],
            'current_password' => ['required_with:new_password', 'current_password:api'],
            'new_password' => ['nullable', 'different:current_password', 'confirmed', 'string', 'min:3', 'max:255']
        ];
    }
}
