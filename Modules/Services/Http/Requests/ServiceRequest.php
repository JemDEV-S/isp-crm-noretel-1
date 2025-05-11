<?php

namespace Modules\Services\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // La autorización se maneja con middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'service_type' => 'required|string|max:50',
            'technology' => 'required|string|max:50',
            'active' => 'boolean'
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
            'name.required' => 'El nombre del servicio es obligatorio.',
            'name.max' => 'El nombre del servicio no puede tener más de 100 caracteres.',
            'service_type.required' => 'El tipo de servicio es obligatorio.',
            'technology.required' => 'La tecnología es obligatoria.'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Convertir active a boolean
        if ($this->has('active')) {
            $this->merge([
                'active' => filter_var($this->active, FILTER_VALIDATE_BOOLEAN)
            ]);
        }
    }
}
