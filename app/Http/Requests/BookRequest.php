<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
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
        $bookId = $this->route('book');
        $isbnRule = $bookId
            ? "required|string|max:255|unique:books,isbn,{$bookId}"
            : 'required|string|max:255|unique:books,isbn';

        return [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'published_year' => 'required|integer|min:1900|max:'.date('Y'),
            'isbn' => $isbnRule.'|regex:/^[0-9\-]+$/',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The book title is required.',
            'author.required' => 'The author name is required.',
            'description.required' => 'The book description is required.',
            'price.required' => 'The book price is required.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price must be at least 0.',
            'published_year.required' => 'The published year is required.',
            'published_year.integer' => 'The published year must be a valid year.',
            'published_year.min' => 'The published year must be at least 1900.',
            'published_year.max' => 'The published year cannot be in the future.',
            'isbn.required' => 'The ISBN is required.',
            'isbn.unique' => 'This ISBN is already registered.',
            'isbn.regex' => 'The ISBN format is invalid.',
        ];
    }
}
