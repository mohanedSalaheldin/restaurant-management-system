<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:500'],
            'discount_type'    => ['required', 'in:percentage,fixed'],
            'discount_value'   => [
                'required',
                'numeric',
                'min:0.01',
                $this->input('discount_type') === 'percentage' ? 'max:100' : 'min:0.01',
            ],
            'start_date'       => ['required', 'date'],
            'end_date'         => ['required', 'date', 'after_or_equal:start_date'],
            'start_time'       => ['nullable', 'date_format:H:i'],
            'end_time'         => ['nullable', 'date_format:H:i', 'after:start_time'],
            'applicable_days'  => ['nullable', 'array'],
            'applicable_days.*'=> ['string', 'in:Mon,Tue,Wed,Thu,Fri,Sat,Sun'],
            'items'            => ['required', 'array', 'min:1'], // اختيار صنف واحد على الأقل
            'items.*'          => ['exists:menu_items,id'],
            'status'           => ['nullable', 'boolean'],
            'display_on_menu'  => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status'          => $this->boolean('status'),
            'display_on_menu' => $this->boolean('display_on_menu'),
        ]);
    }
}