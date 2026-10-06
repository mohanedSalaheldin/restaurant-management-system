<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tableId = $this->route('table')?->id;

        return [
            'table_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tables', 'table_number')->ignore($tableId),
            ],
            'type'         => ['required', 'in:private,public'],
            'min_capacity' => ['required', 'integer', 'min:1'],
            'max_capacity' => ['required', 'integer', 'gte:min_capacity'],
            'location'     => ['nullable', 'string', 'max:100'],
            'status'       => ['required', 'in:available,occupied,reserved,maintenance'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ];
    }
}
