<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name'             => ['required', 'string', 'max:200'],
            'section_id'       => ['required', 'exists:sections,id'],
            'category_id'      => ['required', 'exists:categories,id'],
            'subcategory_id'   => ['required', 'exists:subcategories,id'],
            'description'      => ['nullable', 'string', 'max:500'],
            'price'            => ['required', 'numeric', 'min:0'],
            'preparation_time' => ['nullable', 'integer', 'min:1'],
            'availability'     => ['required', 'in:available,out_of_stock'],
            'image'            => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'special_tags'     => ['nullable', 'array'],
            'special_tags.*'   => ['string', 'in:Vegetarian,Vegan,Spicy,Chef\'s Special,Popular'],
        ];
    }
}
