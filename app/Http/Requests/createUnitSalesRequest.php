<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class createUnitSalesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'unit_id'        => ['required',Rule::exists('units','id')],
            'buyer_id'    => ['required', Rule::exists('customers','id')->where('type', 'buyer')],
            'marketer_id'    => ['required', Rule::exists('customers','id')->where('type','marketer')],
            'sale_date'      => ['required','date'],
            'payment_method' => ['required', 'string'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
        ];
    }
}
