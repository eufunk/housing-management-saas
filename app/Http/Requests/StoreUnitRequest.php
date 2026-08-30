<?php

namespace App\Http\Requests;

use App\Enums\UnitStatus;
use App\Enums\UnitType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreUnitRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'building_id' => [
                'required',
                Rule::exists('buildings', 'id')->where('organization_id', $this->user()->current_organization_id),
            ],
            'type' => ['required', new Enum(UnitType::class)],
            'unit_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'unit_number')->where('building_id', $this->building_id)->ignore($this->route('unit')),
            ],
            'floor' => ['nullable', 'integer', 'min:0', 'max:200'],
            'size_sqm' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'rooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'status' => ['required', new Enum(UnitStatus::class)],
        ];
    }
}
