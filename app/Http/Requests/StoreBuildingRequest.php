<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBuildingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'property_id' => [
                'required',
                Rule::exists('properties', 'id')->where('organization_id', $this->user()->current_organization_id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'floors' => ['nullable', 'integer', 'min:1', 'max:200'],
        ];
    }
}
