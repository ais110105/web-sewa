<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can("edit-items");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "category_id" => ["required", "exists:categories,id"],
            "name" => ["required", "string", "max:255"],
            "description" => ["nullable", "string", "max:1000"],
            "photo" => [
                "nullable",
                "image",
                "mimes:jpeg,jpg,png,webp",
                "max:2048",
            ],
            "price_per_period" => ["required", "numeric", "min:0"],
            "stock" => ["required", "integer", "min:0"],
            "available_stock" => ["required", "integer", "min:0"],
            "status" => ["required", "in:available,rented,maintenance"],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            "category_id" => "Category",
            "name" => "Item Name",
            "description" => "Description",
            "photo" => "Photo",
            "price_per_period" => "Price per Period",
            "stock" => "Stock",
            "available_stock" => "Available Stock",
            "status" => "Status",
        ];
    }
}
