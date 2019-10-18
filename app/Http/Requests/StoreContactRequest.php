<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
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
            'first_name' => 'required|min:3|max:30|alpha',
            'last_name'  => 'required|min:3|max:30|alpha',
            'mobile'     => 'required|numeric',
            'address'    => 'required|min:3|max:50|alpha_num'
        ];
    }
}
