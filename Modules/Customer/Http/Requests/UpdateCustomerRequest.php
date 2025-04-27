<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->canEditInModule('customers');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $customerId = $this->route('id');
        
        return [
            'customer_type' => 'sometimes|required|string|in:individual,business',
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'identity_document' => "nullable|string|max:50|unique:customers,identity_document,{$customerId}",
            'email' => "nullable|email|max:255|unique:customers,email,{$customerId}",
            'phone' => 'nullable|string|max:20',
            'credit_score' => 'nullable|integer|min:0|max:1000',
            'contact_preferences' => 'nullable|string|max:255',
            'segment' => 'nullable|string|max:50',
            'active' => 'boolean',
            
            // Addresses validation
            'addresses' => 'sometimes|required|array|min:1',
            'addresses.*.id' => 'nullable|integer|exists:addresses,id',
            'addresses.*.address_type' => 'required|string|in:main,billing,installation',
            'addresses.*.street' => 'required|string|max:255',
            'addresses.*.number' => 'nullable|string|max:20',
            'addresses.*.floor' => 'nullable|string|max:10',
            'addresses.*.apartment' => 'nullable|string|max:10',
            'addresses.*.city' => 'required|string|max:100',
            'addresses.*.state' => 'required|string|max:100',
            'addresses.*.postal_code' => 'nullable|string|max:20',
            'addresses.*.country' => 'required|string|max:100',
            'addresses.*.coordinates' => 'nullable|string|max:100',
            'addresses.*.is_primary' => 'boolean',
            
            // Emergency contacts validation
            'emergency_contacts' => 'nullable|array',
            'emergency_contacts.*.id' => 'nullable|integer|exists:emergency_contacts,id',
            'emergency_contacts.*.name' => 'required|string|max:255',
            'emergency_contacts.*.relationship' => 'required|string|max:100',
            'emergency_contacts.*.phone' => 'required|string|max:20',
            'emergency_contacts.*.email' => 'nullable|email|max:255',
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
            'customer_type.required' => 'El tipo de cliente es obligatorio.',
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Este email ya está registrado para otro cliente.',
            'identity_document.unique' => 'Este documento de identidad ya está registrado para otro cliente.',
            
            'addresses.required' => 'Se requiere al menos una dirección.',
            'addresses.min' => 'Se requiere al menos una dirección.',
            'addresses.*.address_type.required' => 'El tipo de dirección es obligatorio.',
            'addresses.*.street.required' => 'La calle es obligatoria.',
            'addresses.*.city.required' => 'La ciudad es obligatoria.',
            'addresses.*.state.required' => 'La provincia/estado es obligatoria.',
            'addresses.*.country.required' => 'El país es obligatorio.',
            
            'emergency_contacts.*.name.required' => 'El nombre del contacto de emergencia es obligatorio.',
            'emergency_contacts.*.relationship.required' => 'La relación con el contacto de emergencia es obligatoria.',
            'emergency_contacts.*.phone.required' => 'El teléfono del contacto de emergencia es obligatorio.',
            'emergency_contacts.*.email.email' => 'El email del contacto de emergencia debe tener un formato válido.',
        ];
    }
}