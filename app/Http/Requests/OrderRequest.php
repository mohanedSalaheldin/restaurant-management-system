<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_type'                   => ['required', 'in:dine_in,takeaway'],
            'table_id'                     => ['required_if:order_type,dine_in', 'nullable', 'exists:tables,id'],
            'guest_count'                  => ['nullable', 'integer', 'min:1'],
            'notes'                        => ['nullable', 'string', 'max:500'],
            'status'                       => ['nullable', 'in:draft,sent_to_kitchen'],
            'items'                        => ['required', 'array', 'min:1'],
            'items.*.menu_item_id'         => ['required', 'exists:menu_items,id'],
            'items.*.quantity'             => ['required', 'integer', 'min:1'],
            'items.*.special_instructions' => ['nullable', 'string', 'max:255'],
        ];
    }
}
