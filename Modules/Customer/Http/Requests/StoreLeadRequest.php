<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->canCreateInModule('customers');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'source' => 'nullable|string|max:100',
            'capture_date' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'potential_value' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'contact.required' => 'La información de contacto es obligatoria.',
            'capture_date.date' => 'La fecha de captura debe ser una fecha válida.',
            'potential_value.numeric' => 'El valor potencial debe ser un número.',
            'potential_value.min' => 'El valor potencial no puede ser negativo.',
        ];
    }
}