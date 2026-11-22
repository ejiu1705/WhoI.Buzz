<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateSkpiCategoryRequest extends StoreSkpiCategoryRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['code'] = [
            'required',
            'string',
            'max:32',
            Rule::unique('skpi_categories', 'code')->ignore($this->route('kurikulum_skpi') ?? $this->route('skpi_category')),
        ];

        return $rules;
    }
}
