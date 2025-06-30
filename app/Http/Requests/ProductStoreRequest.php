<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 允许所有经过身份验证的用户创建产品
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
            'part_number' => 'nullable|string|max:50|unique:product,part_number',
            'name' => 'required|string|max:255|unique:product,name',
            'category_id' => 'required|exists:category,id',
            'brand_id' => 'required|exists:brand,id',
            'supplier_id' => 'required|exists:supplier,id',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:purchase_price', // 售价不能低于成本价
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
            'selling_price.gte' => 'The selling price cannot be lower than the purchase price.',
        ];
    }
}
