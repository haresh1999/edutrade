<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SabpaisaRequest extends FormRequest
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
            'order_id' => ['required','string','max:255'],
            'amount' => ['required','numeric','min:1'],
            'payer_name' => ['required','string','max:255'],
            'payer_email' => ['required','email','max:255'],
            'payer_mobile' => ['required','digits_between:9,11'],
        ];
    }
}
