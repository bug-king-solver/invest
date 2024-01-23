<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentMethodRequest extends FormRequest
{
    public function rules()
    {
        return [
            'payment_method_id' => 'required|string'
        ];
    }

    public function authorize()
    {
        return true;
    }
}
