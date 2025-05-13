<?php

namespace Modules\Billing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->canCreateInModule('invoices');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'contract_id' => 'required|exists:contracts,id',
            'invoice_number' => 'nullable|string|unique:invoices,invoice_number',
            'amount' => 'required|numeric|min:0',
            'taxes' => 'nullable|numeric|min:0',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'document_type' => 'nullable|string|max:50'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'contract_id' => 'contrato',
            'invoice_number' => 'número de factura',
            'amount' => 'monto',
            'taxes' => 'impuestos',
            'issue_date' => 'fecha de emisión',
            'due_date' => 'fecha de vencimiento',
            'document_type' => 'tipo de documento'
        ];
    }
}