<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
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
            'last_name' => ['required', 'string',  'min:3', 'max:255'],
            'email' => ['required', 'unique:users,email', 'email:rfc,dns'],
            'type' => ['required', Rule::in([User::TYPE_BUYER, User::TYPE_SELLER, User::TYPE_ADMIN])],
            'gender' => ['required', 'in:male,female'],
            'password' => ['required', 'confirmed', 'min:7', 'max:255']
        ];
    }
}
