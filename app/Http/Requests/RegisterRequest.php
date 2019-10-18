<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
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
            'name' => 'required|min:3|max:50|alpha',
            'email' => 'required|max:50|email|unique:users',
            'password' => 'required|min:5|max:50|alpha_num',
            'confirmPassword' => 'required|min:5|max:50|alpha_num'
        ];
    }
}
