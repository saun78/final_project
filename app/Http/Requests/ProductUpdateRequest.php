<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
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
        $productId = $this->route('product')->id;

        return [
            'part_number' => ['nullable', 'string', 'max:50', Rule::unique('product', 'part_number')->ignore($productId)],
            'name' => ['required', 'string', 'max:255', Rule::unique('product', 'name')->ignore($productId)],
            'category_id' => 'required|exists:category,id',
            'brand_id' => 'required|exists:brand,id',
            'supplier_id' => 'required|exists:supplier,id',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'picture' => 'nullable|image|max:2048', // Max 2MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A product with this name already exists. Please choose a different name.',
            'part_number.unique' => 'A product with this part number already exists. Please choose a different part number.',
        ];
    }

}
