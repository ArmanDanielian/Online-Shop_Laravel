<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UpdateShopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->shop->user_id === auth()->id() || User::TYPE_SLUGS[auth()->user()->type] === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $regex = '/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/';
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'min:10'],
            'address' => ['nullable'],
            'phone_number' => ['nullable', 'regex:' . $regex],
            'email' => ['required', 'email:rfc,dns', Rule::unique('shops')->ignore($this->shop)],
            'manager_name' => ['required', 'min:3', 'max:255']
        ];
    }

    public function failedAuthorization()
    {
        throw new HttpException(Response::HTTP_FORBIDDEN, 'You can update only your shops');
    }
}
