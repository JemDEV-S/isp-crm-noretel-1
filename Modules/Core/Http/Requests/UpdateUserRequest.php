<?php

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Entities\SecurityPolicy;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $userId = $this->route('id');
        
        return [
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'password' => 'nullable|string|min:8',
            'employee_id' => 'nullable|exists:employees,id',
            'status' => 'required|in:active,inactive,suspended',
            'requires_2fa' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'preferences' => 'nullable|array',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar política de contraseñas solo si se proporciona una nueva contraseña
            if ($this->filled('password')) {
                $passwordValidation = SecurityPolicy::validatePassword($this->password);
                
                if ($passwordValidation !== true) {
                    foreach ($passwordValidation as $error) {
                        $validator->errors()->add('password', $error);
                    }
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique' => 'Este nombre de usuario ya está en uso.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Por favor ingrese un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',
            'roles.*.exists' => 'Uno de los roles seleccionados no existe.',
            'employee_id.exists' => 'El empleado seleccionado no existe.',
        ];
    }
}