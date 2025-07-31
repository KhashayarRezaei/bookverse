<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|integer|exists:books,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:stripe,paypal',
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
            'items.required' => 'Order items are required.',
            'items.array' => 'Items must be an array.',
            'items.min' => 'At least one item is required.',
            'items.*.book_id.required' => 'Book ID is required for each item.',
            'items.*.book_id.integer' => 'Book ID must be a valid integer.',
            'items.*.book_id.exists' => 'The selected book does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.integer' => 'Quantity must be a valid integer.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Payment method must be either stripe or paypal.',
        ];
    }
}
